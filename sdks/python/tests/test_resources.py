import hashlib
import hmac
import time
import unittest
from unittest import mock

from apiempresas import ApiEmpresas, ApiError, verify_webhook_signature, construct_webhook_event, __version__


class FakeResponse:
    def __init__(self, body, status=200):
        self._body = body
        self.status_code = status
        self.ok = status < 400
        self.text = str(body)

    def json(self):
        return self._body


class TestResources(unittest.TestCase):
    def setUp(self):
        self.api = ApiEmpresas('k')
        self.req = mock.patch.object(self.api.session, 'request').start()
        self.addCleanup(mock.patch.stopall)

    def call(self, i=0):
        return self.req.call_args_list[i].kwargs

    def test_version(self):
        self.assertEqual(__version__, '1.2.0')
        self.assertIn('1.2.0', self.api.session.headers['User-Agent'])

    def test_verify(self):
        self.req.return_value = FakeResponse({'success': True, 'data': {'decision_hint': 'pass'}})
        r = self.api.companies.verify('A46103834', name='Mercadona SA', person='Juan Roig', vat=True)
        c = self.call()
        self.assertTrue(c['url'].endswith('/companies/verify'))
        self.assertEqual(c['params'], {'cif': 'A46103834', 'name': 'Mercadona SA', 'person': 'Juan Roig', 'vat': 'true'})
        self.assertEqual(r['decision_hint'], 'pass')

    def test_reconcile(self):
        self.req.return_value = FakeResponse({'success': True, 'data': [{'status': 'match'}], 'meta': {'cost': 1}})
        r = self.api.companies.reconcile(['Mercadona', {'name': 'Seur', 'province': 'Madrid'}])
        self.assertEqual(self.call()['method'], 'POST')
        self.assertEqual(self.call()['json'], {'items': [{'name': 'Mercadona'}, {'name': 'Seur', 'province': 'Madrid'}]})
        self.assertEqual(r['meta']['cost'], 1)

    def test_filter_and_count(self):
        self.req.side_effect = [
            FakeResponse({'success': True, 'data': {'total': 3412}, 'meta': {'cost': 0}}),
            FakeResponse({'success': True, 'data': [{'cif': 'B1'}], 'meta': {'next_cursor': 'abc'}}),
        ]
        n = self.api.companies.count(cnae=['62', '4711'], province='MADRID', has_phone=True)
        r = self.api.companies.filter(cnae='62', size_band=['GT_1M'], limit=1)
        self.assertEqual(n, 3412)
        self.assertEqual(self.call(0)['params'], {'cnae': '62,4711', 'province': 'MADRID', 'has_phone': 'true', 'count_only': 'true'})
        self.assertTrue(self.call(1)['url'].endswith('/companies/filter'))
        self.assertEqual(r['meta']['next_cursor'], 'abc')

    def test_radar_new_filters(self):
        self.req.return_value = FakeResponse({'success': True, 'data': []})
        self.api.companies.radar(cnae='62', min_score=70, has_phone=True)
        self.assertEqual(self.call()['params'], {'cnae': '62', 'min_score': 70, 'has_phone': 'true'})

    def test_watchlist(self):
        self.req.side_effect = [
            FakeResponse({'success': True, 'data': {'added': ['A1']}, 'meta': {'watch_limit': 100}}),
            FakeResponse({'success': True, 'data': [], 'meta': {'total': 1}}),
            FakeResponse({'success': True, 'data': {'cif': 'A1', 'removed': True}, 'meta': {'total': 0}}),
            FakeResponse({'success': True, 'data': [{'type': 'borme_act'}], 'meta': {'total': 1}}),
        ]
        add = self.api.watchlist.add('A1')
        self.api.watchlist.list(page=2)
        rm = self.api.watchlist.remove('A1')
        ev = self.api.watchlist.events(since='2026-09-01', types=['borme_act', 'status_change'])
        self.assertEqual(self.call(0)['method'], 'POST')
        self.assertEqual(self.call(0)['json'], {'cifs': ['A1']})
        self.assertEqual(add['meta']['watch_limit'], 100)
        self.assertEqual(self.call(1)['params'], {'page': 2})
        self.assertEqual(self.call(2)['method'], 'DELETE')
        self.assertTrue(self.call(2)['url'].endswith('/watchlist/A1'))
        self.assertTrue(rm['removed'])
        self.assertEqual(self.call(3)['params'], {'since': '2026-09-01', 'types': 'borme_act,status_change'})
        self.assertEqual(ev['data'][0]['type'], 'borme_act')

    def test_webhooks(self):
        self.req.side_effect = [
            FakeResponse({'success': True, 'id': 7, 'event': 'watchlist.*', 'secret': 's3', 'message': 'ok'}, 201),
            FakeResponse({'success': True, 'data': [{'id': 7}]}),
            FakeResponse({'success': True, 'message': 'Webhook eliminado'}),
            FakeResponse({'success': False, 'data': {'delivered': False, 'http_status': 500}}, 502),
        ]
        c = self.api.webhooks.create('https://x.example/h', 'watchlist')
        self.assertEqual((c['id'], c['secret']), (7, 's3'))
        self.assertEqual(self.call(0)['json'], {'url': 'https://x.example/h', 'event': 'watchlist'})
        self.assertEqual(len(self.api.webhooks.list()), 1)
        self.assertTrue(self.api.webhooks.remove(7))
        t = self.api.webhooks.test(7)
        self.assertTrue(self.call(3)['url'].endswith('/webhooks/7/test'))
        self.assertFalse(t['delivered'])


class TestSignature(unittest.TestCase):
    secret = 'whsec_test'
    body = '{"id":"u1","event":"company.borme_act","data":{"cif":"A1"}}'

    def sig(self, t, body=None):
        b = (body or self.body).encode()
        return f't={t},v1=' + hmac.new(self.secret.encode(), f'{t}.'.encode() + b, hashlib.sha256).hexdigest()

    def test_verify(self):
        t = 1790000000
        s = self.sig(t)
        self.assertTrue(verify_webhook_signature(self.body, s, self.secret, 300, now=t + 10))
        self.assertTrue(verify_webhook_signature(self.body.encode(), s, self.secret, 300, now=t))
        self.assertTrue(ApiEmpresas('k').webhooks.verify_signature(self.body, s, self.secret, 300, now=t))
        self.assertFalse(verify_webhook_signature(self.body + ' ', s, self.secret, 300, now=t))
        self.assertFalse(verify_webhook_signature(self.body, s, 'otro', 300, now=t))
        self.assertFalse(verify_webhook_signature(self.body, s, self.secret, 300, now=t + 301))
        self.assertTrue(verify_webhook_signature(self.body, s, self.secret, 0, now=t + 99999))
        self.assertFalse(verify_webhook_signature(self.body, 'basura', self.secret))
        self.assertFalse(verify_webhook_signature(self.body, None, self.secret))

    def test_construct(self):
        ok = self.sig(int(time.time()))
        self.assertEqual(construct_webhook_event(self.body, ok, self.secret)['event'], 'company.borme_act')
        with self.assertRaises(ApiError):
            construct_webhook_event(self.body, self.sig(1790000000), self.secret)


if __name__ == '__main__':
    unittest.main()
