import { ApiEmpresasOptions } from './types';
import { ApiError } from './errors/ApiError';
import { Companies } from './resources/Companies';

/** Versión del SDK, enviada en el User-Agent. */
export const SDK_VERSION = '1.1.0';

/**
 * Mensaje legible del cuerpo de error. Según el error, la API lo pone en
 * message, detail, error (texto) o messages (errores de validación).
 */
function extractErrorMessage(data: any): string | undefined {
  if (!data || typeof data !== 'object') return undefined;
  for (const key of ['message', 'detail']) {
    if (typeof data[key] === 'string' && data[key] !== '') return data[key];
  }
  if (typeof data.error === 'string' && data.error !== '') return data.error;
  if (data.messages && typeof data.messages === 'object') {
    const first = Object.values(data.messages)[0];
    if (typeof first === 'string' && first !== '') return first;
  }
  return undefined;
}

export class ApiEmpresas {
  private apiKey: string;
  private baseURL: string;
  private timeout: number;

  public companies: Companies;

  constructor(options: ApiEmpresasOptions) {
    if (!options || !options.apiKey) {
      throw new Error("La propiedad 'apiKey' es obligatoria para inicializar ApiEmpresas.");
    }

    this.apiKey = options.apiKey;
    this.baseURL = (options.baseURL || 'https://apiempresas.es/api/v1').replace(/\/$/, '');
    this.timeout = options.timeout || 30000;

    this.companies = new Companies(this);
  }

  public async request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
    const url = `${this.baseURL}${endpoint}`;
    
    const headers = new Headers(options.headers || {});
    headers.set('X-API-KEY', this.apiKey);
    headers.set('Accept', 'application/json');
    headers.set('User-Agent', `apiempresas-node/${SDK_VERSION}`);
    if (!headers.has('Content-Type') && options.method && options.method.toUpperCase() !== 'GET') {
      headers.set('Content-Type', 'application/json');
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.timeout);

    try {
      const response = await fetch(url, {
        ...options,
        headers,
        signal: controller.signal as any
      });

      clearTimeout(timeoutId);

      const isJson = response.headers.get('content-type')?.includes('application/json');
      const data = isJson ? await response.json() : await response.text();

      if (!response.ok) {
        throw new ApiError(
          response.status,
          extractErrorMessage(data) || response.statusText || 'Error desconocido en la API',
          data?.error || undefined,
          data
        );
      }

      return data as T;
    } catch (error: any) {
      clearTimeout(timeoutId);
      if (error.name === 'AbortError') {
        throw new ApiError(408, `La petición superó el tiempo límite de ${this.timeout}ms`);
      }
      if (error instanceof ApiError) {
        throw error;
      }
      throw new ApiError(500, error.message || 'Error de red o conexión fallida');
    }
  }
}
