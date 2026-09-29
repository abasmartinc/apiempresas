import { createHmac } from 'crypto';
import { ApiEmpresas } from '../../src/ApiEmpresas';
import { verifyWebhookSignature, constructWebhookEvent } from '../../src/webhooks/signature';

const mockFetch = jest.fn();
global.fetch = mockFetch;

const okJson = (body: any, status = 200) => ({
  ok: status < 400,
  status,
  headers: new Headers({ 'content-type': 'application/json' }),
  json: async () => body
});

describe('verify, watchlist y webhooks (1.2.0)', () => {
  let api: ApiEmpresas;

  beforeEach(() => {
    mockFetch.mockReset();
    api = new ApiEmpresas({ apiKey: 'k' });
  });

  it('companies.verify() arma la URL con name, person y vat', async () => {
    mockFetch.mockResolvedValueOnce(okJson({ success: true, data: { cif: 'A46103834', decision_hint: 'pass', flags: [] } }));
    const r = await api.companies.verify('A46103834', { name: 'Mercadona SA', person: 'Juan Roig', vat: true });
    expect(mockFetch.mock.calls[0][0]).toContain('/companies/verify?cif=A46103834&name=Mercadona+SA&person=Juan+Roig&vat=true');
    expect(r.decision_hint).toBe('pass');
  });

  it('companies.reconcile() envía items por POST y acepta textos', async () => {
    mockFetch.mockResolvedValueOnce(okJson({ success: true, data: [{ status: 'match', company: { cif: 'A46103834' } }], meta: { cost: 1 } }));
    const r = await api.companies.reconcile(['Mercadona', { name: 'Seur', province: 'Madrid' }]);
    expect(mockFetch.mock.calls[0][0]).toMatch(/\/companies\/reconcile$/);
    expect(mockFetch.mock.calls[0][1].method).toBe('POST');
    expect(JSON.parse(mockFetch.mock.calls[0][1].body)).toEqual({ items: [{ name: 'Mercadona' }, { name: 'Seur', province: 'Madrid' }] });
    expect(r.meta.cost).toBe(1);
    expect(r.data[0].company!.cif).toBe('A46103834');
  });

  it('companies.count() y filter() arman la URL de /companies/filter', async () => {
    mockFetch
      .mockResolvedValueOnce(okJson({ success: true, data: { total: 3412 }, meta: { cost: 0 } }))
      .mockResolvedValueOnce(okJson({ success: true, data: [{ cif: 'B1', has_phone: true }], meta: { total: 3412, next_cursor: 'abc', cost: 5 } }));
    const n = await api.companies.count({ cnae: ['62', '4711'], province: 'MADRID', has_phone: true });
    const r = await api.companies.filter({ cnae: '62', size_band: ['GT_1M'], limit: 1, cursor: 'x' });
    expect(n).toBe(3412);
    expect(mockFetch.mock.calls[0][0]).toContain('/companies/filter?cnae=62%2C4711&province=MADRID&has_phone=true&count_only=true');
    expect(mockFetch.mock.calls[1][0]).toContain('/companies/filter?cnae=62&size_band=GT_1M&limit=1&cursor=x');
    expect(r.meta.next_cursor).toBe('abc');
    expect(r.data[0].has_phone).toBe(true);
  });

  it('radar() pasa los filtros nuevos', async () => {
    mockFetch.mockResolvedValueOnce(okJson({ success: true, data: [] }));
    await api.companies.radar({ cnae: '62', min_score: 70, has_phone: true });
    expect(mockFetch.mock.calls[0][0]).toContain('/companies/radar?cnae=62&min_score=70&has_phone=true');
  });

  it('watchlist.add() envía {"cifs": [...]} por POST y acepta un CIF suelto', async () => {
    mockFetch.mockResolvedValueOnce(okJson({ success: true, data: { added: ['A1'] }, meta: { total: 1, watch_limit: 100 } }));
    const r = await api.watchlist.add('A1');
    expect(mockFetch.mock.calls[0][0]).toMatch(/\/watchlist$/);
    expect(mockFetch.mock.calls[0][1].method).toBe('POST');
    expect(JSON.parse(mockFetch.mock.calls[0][1].body)).toEqual({ cifs: ['A1'] });
    expect(r.meta.watch_limit).toBe(100);
  });

  it('watchlist.list(), remove() y events()', async () => {
    mockFetch
      .mockResolvedValueOnce(okJson({ success: true, data: [], meta: { total: 0 } }))
      .mockResolvedValueOnce(okJson({ success: true, data: { cif: 'A1', removed: true } }))
      .mockResolvedValueOnce(okJson({ success: true, data: [{ type: 'borme_act', cif: 'A1', date: '2026-09-01', data: {} }], meta: { total: 1 } }));
    await api.watchlist.list({ page: 2 });
    const rm = await api.watchlist.remove('A1');
    const ev = await api.watchlist.events({ since: '2026-09-01', types: ['borme_act', 'status_change'] });
    expect(mockFetch.mock.calls[0][0]).toContain('/watchlist?page=2');
    expect(mockFetch.mock.calls[1][0]).toContain('/watchlist/A1');
    expect(mockFetch.mock.calls[1][1].method).toBe('DELETE');
    expect(rm.removed).toBe(true);
    expect(mockFetch.mock.calls[2][0]).toContain('/watchlist/events?since=2026-09-01&types=borme_act%2Cstatus_change');
    expect(ev.data[0].type).toBe('borme_act');
  });

  it('webhooks.create(), list(), remove()', async () => {
    mockFetch
      .mockResolvedValueOnce(okJson({ success: true, id: 7, event: 'watchlist.*', secret: 's3' }, 201))
      .mockResolvedValueOnce(okJson({ success: true, data: [{ id: 7 }] }))
      .mockResolvedValueOnce(okJson({ success: true, message: 'Webhook eliminado' }));
    const c = await api.webhooks.create({ url: 'https://x.example/h', event: 'watchlist' });
    expect(c).toMatchObject({ id: 7, secret: 's3', event: 'watchlist.*' });
    expect(JSON.parse(mockFetch.mock.calls[0][1].body)).toEqual({ url: 'https://x.example/h', event: 'watchlist' });
    expect(await api.webhooks.list()).toHaveLength(1);
    expect(await api.webhooks.remove(7)).toBe(true);
    expect(mockFetch.mock.calls[2][0]).toContain('/webhooks/7');
  });

  it('webhooks.test() devuelve el resultado también cuando la API responde 502', async () => {
    const data = { delivered: false, http_status: 500, duration_ms: 12, error: null, delivery_id: 'd1' };
    mockFetch.mockResolvedValueOnce(okJson({ success: false, data }, 502));
    const r = await api.webhooks.test(7);
    expect(mockFetch.mock.calls[0][0]).toContain('/webhooks/7/test');
    expect(r.delivered).toBe(false);
    expect(r.http_status).toBe(500);
  });
});

describe('verifyWebhookSignature', () => {
  const secret = 'whsec_test';
  const body = '{"id":"u1","event":"company.borme_act","created_at":"2026-09-28T10:00:00+02:00","data":{"cif":"A1"}}';
  const t = 1790000000;
  const sig = `t=${t},v1=${createHmac('sha256', secret).update(`${t}.${body}`).digest('hex')}`;

  it('acepta una firma correcta dentro de la tolerancia', () => {
    expect(verifyWebhookSignature(body, sig, secret, 300, t + 10)).toBe(true);
    expect(verifyWebhookSignature(Buffer.from(body), sig, secret, 300, t)).toBe(true);
    expect(ApiEmpresas.verifyWebhookSignature(body, sig, secret, 300, t)).toBe(true);
  });

  it('rechaza cuerpo alterado, secreto erróneo, firma caducada o cabecera rota', () => {
    expect(verifyWebhookSignature(body + ' ', sig, secret, 300, t)).toBe(false);
    expect(verifyWebhookSignature(body, sig, 'otro', 300, t)).toBe(false);
    expect(verifyWebhookSignature(body, sig, secret, 300, t + 301)).toBe(false);
    expect(verifyWebhookSignature(body, sig, secret, 0, t + 99999)).toBe(true);
    expect(verifyWebhookSignature(body, 'basura', secret)).toBe(false);
    expect(verifyWebhookSignature(body, undefined, secret)).toBe(false);
  });

  it('constructWebhookEvent() devuelve el evento o lanza', () => {
    const now = Math.floor(Date.now() / 1000);
    const s = `t=${now},v1=${createHmac('sha256', secret).update(`${now}.${body}`).digest('hex')}`;
    expect(constructWebhookEvent(body, s, secret).event).toBe('company.borme_act');
    expect(() => constructWebhookEvent(body, sig, secret)).toThrow();
  });
});
