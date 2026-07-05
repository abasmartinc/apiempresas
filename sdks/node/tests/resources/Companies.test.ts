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
});
