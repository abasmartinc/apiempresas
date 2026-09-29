import { ApiEmpresas } from '../ApiEmpresas';
import {
  WatchlistAddResult,
  WatchlistEvent,
  WatchlistEventsOptions,
  WatchlistMeta
} from '../types';

/**
 * (Pro / Business) Vigilancia de empresas: 100 en Pro, 1.000 en Business.
 * Ninguna de estas llamadas consume consultas.
 */
export class Watchlist {
  constructor(private client: ApiEmpresas) {}

  /** Empresas vigiladas. */
  public async list(options: { page?: number; limit?: number } = {}): Promise<{ data: any[]; meta: WatchlistMeta }> {
    const params = new URLSearchParams();
    if (options.page !== undefined) params.set('page', String(options.page));
    if (options.limit !== undefined) params.set('limit', String(options.limit));
    const qs = params.toString();
    const r = await this.client.request<{ success: boolean; data: any[]; meta: WatchlistMeta }>(`/watchlist${qs ? '?' + qs : ''}`);
    return { data: r.data, meta: r.meta };
  }

  /** Añade uno o varios CIF. Devuelve qué se añadió, qué ya estaba y qué no se pudo. */
  public async add(cifs: string | string[]): Promise<{ data: WatchlistAddResult; meta: WatchlistMeta; message?: string; upgrade_url?: string }> {
    const r = await this.client.request<any>('/watchlist', {
      method: 'POST',
      body: JSON.stringify({ cifs: Array.isArray(cifs) ? cifs : [cifs] })
    });
    const out: any = { data: r.data, meta: r.meta };
    if (r.message) out.message = r.message;
    if (r.upgrade_url) out.upgrade_url = r.upgrade_url;
    return out;
  }

  /** Quita una empresa de la vigilancia. */
  public async remove(cif: string): Promise<{ cif: string; removed: boolean }> {
    const r = await this.client.request<any>(`/watchlist/${encodeURIComponent(cif)}`, { method: 'DELETE' });
    return r.data;
  }

  /**
   * Cambios en las empresas vigiladas desde una fecha (borme_act, status_change,
   * risk_level_change), de más antiguo a más reciente.
   */
  public async events(options: WatchlistEventsOptions = {}): Promise<{ data: WatchlistEvent[]; meta: WatchlistMeta }> {
    const params = new URLSearchParams();
    if (options.since) params.set('since', options.since);
    if (options.types) params.set('types', Array.isArray(options.types) ? options.types.join(',') : options.types);
    if (options.cif) params.set('cif', options.cif);
    if (options.page !== undefined) params.set('page', String(options.page));
    if (options.limit !== undefined) params.set('limit', String(options.limit));
    const qs = params.toString();
    const r = await this.client.request<{ success: boolean; data: WatchlistEvent[]; meta: WatchlistMeta }>(`/watchlist/events${qs ? '?' + qs : ''}`);
    return { data: r.data, meta: r.meta };
  }
}
