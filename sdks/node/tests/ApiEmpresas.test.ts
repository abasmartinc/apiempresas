import { ApiEmpresas } from '../src/ApiEmpresas';

describe('ApiEmpresas Client', () => {
  it('throws an error if no API key is provided', () => {
    expect(() => new ApiEmpresas({} as any)).toThrow(/apiKey/);
    expect(() => new ApiEmpresas({ apiKey: '' })).toThrow(/apiKey/);
  });

  it('initializes correctly with an API key', () => {
    const api = new ApiEmpresas({ apiKey: 'test_key' });
    expect(api).toBeInstanceOf(ApiEmpresas);
    expect(api.companies).toBeDefined();
  });

  it('sets a custom baseURL correctly', () => {
    const api = new ApiEmpresas({ apiKey: 'test_key', baseURL: 'https://test.com/api/' });
    // It should strip the trailing slash
    expect((api as any).baseURL).toBe('https://test.com/api');
  });
});
