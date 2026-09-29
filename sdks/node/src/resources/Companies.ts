import { ApiEmpresas } from '../ApiEmpresas';
import { 
  BaseResponse, 
  Company, 
  BatchRequest, 
  BatchResponse, 
  ScoreData, 
  BormeData, 
  SignalsData,
  VerifyOptions,
  VerifyResult,
  FilterOptions,
  SegmentRow,
  SegmentMeta,
  ReconcileItem,
  ReconcileResult,
  ReconcileMeta
} from '../types';

/** Opciones de get(): admin=true añade administradores y cargos (Pro/Business). */
export interface GetOptions {
  admin?: boolean;
}

/** Opciones de searchMultiple(): paginación por página o por cursor (meta.next_cursor). */
export interface SearchMultipleOptions {
  limit?: number;
  page?: number;
  cursor?: string;
}

/** Filtros del Radar de empresas nuevas. */
export interface RadarOptions {
  province?: string;
  priority?: string;
  range?: string;
  /** Código CNAE (o su prefijo). */
  cnae?: string;
  /** Puntuación mínima (0-100). */
  min_score?: number;
  /** Tipo de acto principal (constitución, ampliación de capital...). */
  main_act_type?: string;
  /** true: solo empresas con teléfono. */
  has_phone?: boolean;
}

function filterParams(o: FilterOptions): URLSearchParams {
  const p = new URLSearchParams();
  for (const [k, v] of Object.entries(o || {})) {
    if (v === undefined || v === null || v === '') continue;
    if (Array.isArray(v)) p.set(k, v.join(','));
    else if (typeof v === 'boolean') p.set(k, v ? 'true' : 'false');
    else p.set(k, String(v));
  }
  return p;
}

export class Companies {
  constructor(private client: ApiEmpresas) {}

  /**
   * Obtiene los datos básicos de una empresa por su CIF.
   */
  public async get(cif: string, options: GetOptions = {}): Promise<Company> {
    const params = new URLSearchParams({ cif });
    if (options.admin) params.set('admin', 'true');
    const response = await this.client.request<BaseResponse<Company>>(`/companies?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Pro) Verificación KYB en una llamada: estado, nombre, administrador, VIES y
   * alertas, con decision_hint pass / review / fail. Coste: 2 consultas.
   */
  public async verify(cif: string, options: VerifyOptions = {}): Promise<VerifyResult> {
    const params = new URLSearchParams({ cif });
    if (options.name) params.set('name', options.name);
    if (options.person) params.set('person', options.person);
    if (options.vat) params.set('vat', 'true');
    const response = await this.client.request<BaseResponse<VerifyResult>>(`/companies/verify?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Pro) Nombre a CIF, hasta 100 por petición. 1 consulta por cada "match";
   * ambiguous y no_match no se cobran.
   */
  public async reconcile(items: Array<string | ReconcileItem>): Promise<{ data: ReconcileResult[]; meta: ReconcileMeta }> {
    const body = { items: items.map((i) => (typeof i === 'string' ? { name: i } : i)) };
    const response = await this.client.request<{ success: boolean; data: ReconcileResult[]; meta: ReconcileMeta }>('/companies/reconcile', {
      method: 'POST',
      body: JSON.stringify(body)
    });
    return { data: response.data, meta: response.meta };
  }

  /**
   * Cuántas empresas encajan con los filtros. Gratis en todos los planes.
   */
  public async count(options: FilterOptions): Promise<number> {
    const params = filterParams(options);
    params.set('count_only', 'true');
    const response = await this.client.request<BaseResponse<{ total: number }>>(`/companies/filter?${params.toString()}`);
    return response.data!.total;
  }

  /**
   * (Business) Empresas de un segmento. 5 consultas por fila devuelta; para la página
   * siguiente, pasa meta.next_cursor como cursor con los mismos filtros.
   */
  public async filter(options: FilterOptions): Promise<{ data: SegmentRow[]; meta: SegmentMeta }> {
    const params = filterParams(options);
    const response = await this.client.request<{ success: boolean; data: SegmentRow[]; meta: SegmentMeta }>(`/companies/filter?${params.toString()}`);
    return { data: response.data, meta: response.meta };
  }

  /**
   * Busca empresas por nombre o razón social.
   */
  public async search(q: string): Promise<Company> {
    const params = new URLSearchParams({ q });
    const response = await this.client.request<BaseResponse<Company>>(`/companies/search?${params.toString()}`);
    return response.data!;
  }

  /**
   * Búsqueda con varios resultados (multiple=true). Devuelve data y meta;
   * para la página siguiente, pasa meta.next_cursor como cursor.
   */
  public async searchMultiple(q: string, options: SearchMultipleOptions = {}): Promise<{ data: Company[], meta: any }> {
    const params = new URLSearchParams({ q, multiple: 'true' });
    if (options.limit !== undefined) params.set('limit', String(options.limit));
    if (options.page !== undefined) params.set('page', String(options.page));
    if (options.cursor !== undefined) params.set('cursor', options.cursor);
    const response = await this.client.request<{ success: boolean, data: Company[], meta: any }>(`/companies/search?${params.toString()}`);
    return { data: response.data, meta: response.meta };
  }

  /**
   * Consulta múltiple de CIFs en una sola petición.
   */
  public async batch(request: BatchRequest): Promise<{ meta: BatchResponse, data: Company[] }> {
    const response = await this.client.request<{ success: boolean, meta: BatchResponse, data: Company[] }>('/companies/batch', {
      method: 'POST',
      body: JSON.stringify(request)
    });
    return { meta: response.meta, data: response.data };
  }

  /**
   * (Pro) Obtiene el Scoring Comercial de una empresa.
   */
  public async score(cif: string): Promise<ScoreData> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<ScoreData>>(`/companies/score?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Pro) Obtiene el historial de actos del BORME de una empresa.
   */
  public async borme(cif: string): Promise<BormeData> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<BormeData>>(`/companies/borme?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Pro) Obtiene las señales societarias recientes de una empresa.
   */
  public async signals(cif: string): Promise<SignalsData> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<SignalsData>>(`/companies/signals?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Obtiene Insights IA de una empresa.
   */
  public async insights(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/insights?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Pro) Obtiene datos de contacto y preparación de la empresa.
   */
  public async contactPrep(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/contact-prep?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Obtiene la información del Radar de empresas.
   */
  public async radar(cifOrOptions: string | RadarOptions = {}): Promise<any> {
    // Compatibilidad: antes se pasaba un CIF, que el Radar no usa. Ahora se pasan
    // los filtros (province, priority, range, cnae, min_score, main_act_type, has_phone).
    const params = new URLSearchParams();
    if (typeof cifOrOptions === 'string') {
      params.set('cif', cifOrOptions);
    } else {
      for (const [k, v] of Object.entries(cifOrOptions)) {
        if (v !== undefined && v !== null) params.set(k, String(v));
      }
    }
    const response = await this.client.request<BaseResponse<any>>(`/companies/radar?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Realiza un match avanzado con los datos de una empresa.
   */
  public async match(cif: string, sellerSector?: string): Promise<any> {
    // seller_sector es obligatorio en la API: sin él responde 400.
    const params = new URLSearchParams({ cif });
    if (sellerSector) params.set('seller_sector', sellerSector);
    const response = await this.client.request<BaseResponse<any>>(`/companies/match?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Obtiene la red o entramado societario (Network) de una empresa.
   */
  public async network(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/network?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Obtiene los contratos públicos y licitaciones adjudicadas a una empresa.
   */
  public async contracts(cif: string, page: number = 1, limit: number = 20): Promise<any> {
    const params = new URLSearchParams({ cif, page: page.toString(), limit: limit.toString() });
    const response = await this.client.request<BaseResponse<any>>(`/companies/contracts?${params.toString()}`);
    return response.data!;
  }

  /**
   * (Business) Obtiene el perfil de riesgo corporativo y solvencia de una empresa.
   */
  public async riskProfile(cif: string): Promise<any> {
    const params = new URLSearchParams({ cif });
    const response = await this.client.request<BaseResponse<any>>(`/companies/risk-profile?${params.toString()}`);
    return response.data!;
  }
}

