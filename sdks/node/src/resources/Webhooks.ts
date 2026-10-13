import { ApiEmpresas } from '../ApiEmpresas';
import { ApiError } from '../errors/ApiError';
import { WebhookCreateOptions, WebhookCreated, WebhookTestResult, WebhookDeliveriesOptions, WebhookDeliveries } from '../types';

/** (Business) Webhooks: la API te avisa por POST firmado cuando cambia algo que vigilas. */
export class Webhooks {
  constructor(private client: ApiEmpresas) {}

  public async list(): Promise<any[]> {
    const r = await this.client.request<{ success: boolean; data: any[] }>('/webhooks');
    return r.data;
  }

  /** Guarda el secret que devuelve: con él se comprueba la firma (verifyWebhookSignature). */
  public async create(options: WebhookCreateOptions): Promise<WebhookCreated> {
    const r = await this.client.request<any>('/webhooks', {
      method: 'POST',
      body: JSON.stringify(options)
    });
    return { id: r.id, event: r.event, secret: r.secret, message: r.message };
  }

  public async remove(id: number): Promise<boolean> {
    const r = await this.client.request<any>(`/webhooks/${encodeURIComponent(String(id))}`, { method: 'DELETE' });
    return !!(r && r.success !== false);
  }

  /**
   * Envía un test.ping a la URL del webhook y devuelve lo que respondió. Si tu servidor
   * falla, la API responde 502: aquí se devuelve igualmente el resultado (delivered: false).
   */
  public async test(id: number): Promise<WebhookTestResult> {
    try {
      const r = await this.client.request<any>(`/webhooks/${encodeURIComponent(String(id))}/test`, { method: 'POST' });
      return r.data;
    } catch (e) {
      if (e instanceof ApiError && e.status === 502 && e.rawData && e.rawData.data) {
        return e.rawData.data as WebhookTestResult;
      }
      throw e;
    }
  }

  /**
   * Historial de entregas del webhook, de la más reciente a la más antigua: estado, intentos,
   * código HTTP de tu servidor, duración y error. Pagina con page/limit mientras meta.has_more.
   */
  public async deliveries(id: number, options: WebhookDeliveriesOptions = {}): Promise<WebhookDeliveries> {
    const params = new URLSearchParams();
    if (options.status) params.set('status', options.status);
    if (options.page !== undefined) params.set('page', String(options.page));
    if (options.limit !== undefined) params.set('limit', String(options.limit));
    const qs = params.toString();
    const r = await this.client.request<any>(`/webhooks/${encodeURIComponent(String(id))}/deliveries${qs ? '?' + qs : ''}`);
    return { data: r.data, meta: r.meta };
  }
}
