import { ApiEmpresas } from '../src/ApiEmpresas';
import { ApiError } from '../src/errors/ApiError';

const mockFetch = jest.fn();
global.fetch = mockFetch;

describe('ApiError y usage() (1.3.0)', () => {
  let api: ApiEmpresas;

  beforeEach(() => {
    mockFetch.mockReset();
    api = new ApiEmpresas({ apiKey: 'test_key' });
  });

  it('expone code, requestId, requiredPlan y checkoutUrl en un 403 de plan', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      status: 403,
      headers: new Headers({ 'content-type': 'application/json', 'x-request-id': 'req_abc' }),
      json: async () => ({
        success: false, error: 'PLAN_RESTRICTION', code: 'PLAN_RESTRICTION', message: 'Requiere Pro',
        required_plan: 'pro', instance: 'req_abc',
        upsell_opportunities: { checkout_url: 'https://apiempresas.es/billing?plan=pro' },
      }),
    });
    const e: ApiError = await api.companies.signals('A46103834').catch((x) => x);
    expect(e).toBeInstanceOf(ApiError);
    expect(e.code).toBe('PLAN_RESTRICTION');
    expect(e.requestId).toBe('req_abc');
    expect(e.requiredPlan).toBe('pro');
    expect(e.checkoutUrl).toBe('https://apiempresas.es/billing?plan=pro');
    expect(e.retryAfter).toBeUndefined();
  });

  it('expone retryAfter en un 429 por velocidad', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      status: 429,
      headers: new Headers({ 'content-type': 'application/json', 'retry-after': '1' }),
      json: async () => ({ success: false, error: 'TOO_MANY_REQUESTS', code: 'TOO_MANY_REQUESTS', message: 'Demasiadas' }),
    });
    const e: ApiError = await api.companies.get('A46103834').catch((x) => x);
    expect(e.status).toBe(429);
    expect(e.retryAfter).toBe(1);
  });

  it('usage() llama a /usage y devuelve data', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => ({ success: true, data: { stats: { monthly_queries: 3, quota_period: 'monthly' }, history: [] } }),
    });
    const u = await api.usage();
    expect(mockFetch.mock.calls[0][0]).toBe('https://apiempresas.es/api/v1/usage');
    expect(u.stats.monthly_queries).toBe(3);
  });
});
