import unittest
from apiempresas import ApiEmpresas, ApiError

class TestApiEmpresas(unittest.TestCase):
    def test_missing_api_key(self):
        with self.assertRaises(ApiError) as context:
            ApiEmpresas('')
        self.assertTrue('obligatoria' in str(context.exception))

    def test_init_success(self):
        client = ApiEmpresas('fake_key')
        self.assertEqual(client.api_key, 'fake_key')
        self.assertEqual(client.session.headers['X-API-KEY'], 'fake_key')

if __name__ == '__main__':
    unittest.main()
