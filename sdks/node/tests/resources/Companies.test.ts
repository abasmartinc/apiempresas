import { ApiEmpresas } from '../../src/ApiEmpresas';

// Mocking global fetch
const mockFetch = jest.fn();
global.fetch = mockFetch;

describe('Companies Resource', () => {
  let api: ApiEmpresas;

  beforeEach(() => {
    mockFetch.mockClear();
    api = new ApiEmpresas({ apiKey: 'test_key' });
  });

  it('calls get() with correct URL and parses the response', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => ({ success: true, data: { cif: 'B123', name: 'Test SL' } })
    });

    const company = await api.companies.get('B123');
    
    expect(mockFetch).toHaveBeenCalledTimes(1);
    expect(mockFetch.mock.calls[0][0]).toContain('/companies?cif=B123');
    expect(mockFetch.mock.calls[0][1].headers.get('X-API-KEY')).toBe('test_key');
    expect(company.name).toBe('Test SL');
  });

  it('throws ApiError when the API returns an error', async () => {
    mockFetch.mockResolvedValue({
      ok: false,
      status: 403,
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => ({ success: false, error: 'UPGRADE_REQUIRED', message: 'Plan insuficiente' })
    });

    await expect(api.companies.get('B123')).rejects.toThrow('Plan insuficiente');
    
    try {
      await api.companies.get('B123');
    } catch (err: any) {
      expect(err.status).toBe(403);
      expect(err.errorCode).toBe('UPGRADE_REQUIRED');
    }
  });

  it('calls batch() using POST correctly', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: true,
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => ({
        success: true,
        meta: { cost: 2 },
        data: [{ cif: 'A1', name: 'Corp1' }]
      })
    });

    const response = await api.companies.batch({ cifs: ['A1'] });

    expect(mockFetch.mock.calls[0][0]).toContain('/companies/batch');
    expect(mockFetch.mock.calls[0][1].method).toBe('POST');
    const body = JSON.parse(mockFetch.mock.calls[0][1].body);
    expect(body.cifs).toEqual(['A1']);
    
    expect(response.data[0].cif).toBe('A1');
    expect(response.meta.cost).toBe(2);
  });

  const okJson = (body: any) => ({
    ok: true,
    headers: new Headers({ 'content-type': 'application/json' }),
    json: async () => body
  });

  it('get() with admin option adds admin=true', async () => {
    mockFetch.mockResolvedValueOnce(okJson({ success: true, data: { cif: 'A1', name: 'X' } }));
    await api.companies.get('A1', { admin: true });
    expect(mockFetch.mock.calls[0][0]).toContain('/companies?cif=A1&admin=true');
  });

  it('match() sends seller_sector when given', async () => {
    mockFetch.mockResolvedValueOnce(okJson({ success: true, data: {} }));
    await api.companies.match('A1', 'software');
    expect(mockFetch.mock.calls[0][0]).toContain('/companies/match?cif=A1&seller_sector=software');
  });

  it('radar() accepts filters and still accepts a CIF string', async () => {
    mockFetch.mockResolvedValue(okJson({ success: true, data: [] }));
    await api.companies.radar({ province: 'Madrid', range: 'semana' });
    await api.companies.radar('A1');
    expect(mockFetch.mock.calls[0][0]).toContain('/companies/radar?province=Madrid&range=semana');
    expect(mockFetch.mock.calls[1][0]).toContain('/companies/radar?cif=A1');
  });

  it('searchMultiple() returns data and meta', async () => {
    mockFetch.mockResolvedValueOnce(okJson({ success: true, data: [{ cif: 'A1', name: 'X' }], meta: { next_cursor: 'abc' } }));
    const r = await api.companies.searchMultiple('soft', { limit: 5, cursor: 'c1' });
    expect(mockFetch.mock.calls[0][0]).toContain('/companies/search?q=soft&multiple=true&limit=5&cursor=c1');
    expect(r.data).toHaveLength(1);
    expect(r.meta.next_cursor).toBe('abc');
  });

  it('ApiError takes the message from error when there is no message, and exposes code', async () => {
    mockFetch.mockResolvedValueOnce({
      ok: false,
      status: 401,
      statusText: 'Unauthorized',
      headers: new Headers({ 'content-type': 'application/json' }),
      json: async () => ({ error: 'API key inválida', success: false, code: 'API_KEY_INVALID' })
    });
    await expect(api.companies.get('A1')).rejects.toMatchObject({
      status: 401,
      message: 'API key inválida',
      errorCode: 'API key inválida',
      code: 'API_KEY_INVALID'
    });
  });
});
