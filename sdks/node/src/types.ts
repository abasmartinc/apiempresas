export interface ApiEmpresasOptions {
  /**
   * Tu API Key de APIEmpresas.es
   */
  apiKey: string;
  /**
   * Base URL de la API. Por defecto es https://apiempresas.es/api/v1
   * Puedes sobreescribirla para apuntar al sandbox (gratis, datos simulados, misma API Key): https://apiempresas.es/api/sandbox/v1
   */
  baseURL?: string;
  /**
   * Timeout opcional para las peticiones en milisegundos.
   */
  timeout?: number;
}

export interface BaseResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
}

export interface Company {
  id?: number;
  cif: string;
  name: string;
  cnae?: string;
  cnae_label?: string;
  founded?: string;
  province?: string;
  municipality?: string;
  address?: string;
  status?: string;
  score?: number;
  cnae_2025?: string;
  cnae_2025_label?: string;
  corporate_purpose?: string;
  capital_social_raw?: string;
  lat?: number;
  lng?: number;
  /**
   * Estado normalizado (desde 1.2.0): ACTIVE, PRESUMED_ACTIVE, INSOLVENCY, IN_LIQUIDATION,
   * DISSOLVED, REGISTRY_CLOSED, MERGED, INACTIVE, EXTINCT o UNKNOWN.
   */
  status_code?: StatusCode;
  /** De dónde sale el estado (Registro, BORME...). */
  status_source?: string | null;
  /** Fecha del hecho que fija el estado (YYYY-MM-DD). */
  status_date?: string | null;
  /** Pro/Business: tramo de tamaño y año de las últimas cuentas vistas. */
  financials?: CompanyFinancials | null;
  /** Solo con get(cif, { admin: true }) en Pro/Business. since: fecha del nombramiento. */
  administrators?: Array<{ name: string; position: string; since?: string | null; [key: string]: any }>;
  /** Solo en el plan Free: indica que la respuesta viene recortada. */
  upsell_opportunities?: { campos_ocultos?: string[]; mensaje?: string; [key: string]: any };
}

export type StatusCode =
  | 'ACTIVE' | 'PRESUMED_ACTIVE' | 'INSOLVENCY' | 'IN_LIQUIDATION' | 'DISSOLVED'
  | 'REGISTRY_CLOSED' | 'MERGED' | 'INACTIVE' | 'EXTINCT' | 'UNKNOWN';

export interface CompanyFinancials {
  size_band?: string | null;
  size_band_label?: string | null;
  last_accounts_year?: number | null;
}

// ---------------------------------------------------------------------------
// Verificación KYB (GET /companies/verify)
// ---------------------------------------------------------------------------

export interface VerifyOptions {
  /** Razón social que te han dado, para compararla con la oficial. */
  name?: string;
  /** Nombre y apellidos de quien firma, para comprobar que es administrador vigente. */
  person?: string;
  /** true: comprueba el NIF-IVA intracomunitario en VIES. */
  vat?: boolean;
}

export interface VerifyFlag {
  code: string;
  severity: 'low' | 'medium' | 'high' | 'critical';
  message: string;
}

export interface VerifyResult {
  cif: string;
  exists: boolean;
  name: string | null;
  status: string | null;
  status_code: StatusCode | null;
  status_source: string | null;
  status_date: string | null;
  checks: {
    name?: { provided: string; score: number; match: boolean };
    person?: { provided: string; is_current_admin: boolean; matched_name: string | null; position: string | null; since: string | null };
    vat?: { vat_number: string; checked: boolean; valid: boolean | null; source: string; error: string | null };
    accounts?: { last_accounts_year: number | null };
  };
  /** Solo en Business. */
  risk?: { risk_level: string; risk_score: number; updated_at: string } | null;
  flags: VerifyFlag[];
  decision_hint: 'pass' | 'review' | 'fail';
  checked_at: string;
}

// ---------------------------------------------------------------------------
// Nombre a CIF (POST /companies/reconcile)
// ---------------------------------------------------------------------------

export interface ReconcileItem {
  name: string;
  province?: string;
}

export interface ReconcileCandidate {
  cif: string;
  name: string;
  province: string | null;
  status: string | null;
  status_code: StatusCode | null;
  score: number;
}

export interface ReconcileResult {
  input: { name: string; province: string | null };
  status: 'match' | 'ambiguous' | 'no_match' | 'invalid' | 'skipped_quota';
  score?: number;
  company?: ReconcileCandidate;
  candidates?: ReconcileCandidate[];
  message?: string;
}

export interface ReconcileMeta {
  requested: number;
  matched: number;
  ambiguous: number;
  no_match: number;
  invalid: number;
  skipped_quota: number;
  cost: number;
  thresholds: { match: number; ambiguous: number };
}

// ---------------------------------------------------------------------------
// Segmentos (GET /companies/filter)
// ---------------------------------------------------------------------------

export interface FilterOptions {
  /** Prefijos CNAE (ej: ['62'] o '4711,4719'). Hace falta cnae, province o municipality. */
  cnae?: string | string[];
  province?: string;
  municipality?: string;
  /** active (por defecto), active_or_unknown o any. */
  status?: 'active' | 'active_or_unknown' | 'any';
  founded_from?: string;
  founded_to?: string;
  has_phone?: boolean;
  size_band?: Array<'NO_REVENUE' | 'LT_500K' | '500K_1M' | 'GT_1M'> | string;
  min_accounts_year?: number;
  /** Filas por página (1-1000). */
  limit?: number;
  cursor?: string;
}

export interface SegmentRow {
  cif: string;
  name: string;
  cnae: string | null;
  cnae_label: string | null;
  province: string | null;
  municipality: string | null;
  founded: string | null;
  status: string | null;
  status_code: StatusCode | null;
  status_source: string | null;
  financials: CompanyFinancials | null;
  has_phone: boolean;
}

export interface SegmentMeta {
  total: number;
  returned: number;
  limit: number;
  has_more: boolean;
  next_cursor: string | null;
  cost: number;
  cost_per_row: number;
  truncated: boolean;
  filters: Record<string, any>;
}

// ---------------------------------------------------------------------------
// Vigilancia (watchlist)
// ---------------------------------------------------------------------------

export type WatchlistEventType = 'borme_act' | 'status_change' | 'risk_level_change';

export interface WatchlistEvent {
  type: WatchlistEventType;
  cif: string;
  company_name?: string | null;
  date: string;
  /** borme_act: act_types, description, url_pdf. status_change: status, status_code.
   *  risk_level_change: from, to, model_change. */
  data: Record<string, any>;
}

export interface WatchlistAddResult {
  added: string[];
  already_watching: string[];
  not_found: string[];
  invalid: string[];
  rejected_over_limit: string[];
}

export interface WatchlistMeta {
  total?: number;
  watch_limit?: number;
  page?: number;
  limit?: number;
  [key: string]: any;
}

export interface WatchlistEventsOptions {
  /** YYYY-MM-DD (incluida). Por defecto, hace 7 días; máximo 90. */
  since?: string;
  types?: WatchlistEventType[] | string;
  cif?: string;
  page?: number;
  limit?: number;
}

// ---------------------------------------------------------------------------
// Webhooks
// ---------------------------------------------------------------------------

export type WebhookEvent =
  | 'company.borme_act' | 'company.status_changed' | 'company.risk_level_changed'
  | 'watchlist.*' | 'company.created' | 'export.completed' | string;

export interface WebhookCreateOptions {
  url: string;
  event: WebhookEvent;
  /** Si no lo pasas, la API genera uno y lo devuelve. */
  secret?: string;
}

export interface WebhookCreated {
  id: number;
  event: string;
  secret: string;
  message?: string;
}

export interface WebhookTestResult {
  delivered: boolean;
  http_status: number | null;
  duration_ms: number;
  error: string | null;
  delivery_id: string;
}

/** Cuerpo que recibe tu servidor. */
export interface WebhookPayload<T = any> {
  id: string;
  event: string;
  created_at: string;
  data: T;
}

export interface BatchRequest {
  cifs: string[];
  admin?: boolean;
}

export interface BatchResponse {
  requested: number;
  found: number;
  cost: number;
  truncated: boolean;
}

export interface ScoreData {
  cif: string;
  score: number;
  priority: string;
  reasons: string[];
  last_signal?: {
    type: string;
    date: string;
  };
}

export interface BormeEvent {
  date: string;
  act_types: string;
  description: string;
  url_pdf: string;
}

export interface BormeData {
  cif: string;
  company_name: string;
  events: BormeEvent[];
}

export interface SignalEvent {
  type: string;
  label: string;
  date: string;
  probability: string;
}

export interface SignalsData {
  cif: string;
  signals: SignalEvent[];
}
