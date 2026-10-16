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
  /** Todos los planes (desde 1.3.0): código LEI de GLEIF. */
  identifiers?: CompanyIdentifiers;
  /** Todos los planes (desde 1.3.0): forma jurídica con su código ISO 20275 (ELF). */
  legal_form?: CompanyLegalForm;
  /** Pro/Business (desde 1.3.0): matriz directa y última declaradas a GLEIF. null si no tiene LEI. */
  group?: CompanyGroup | null;
  /** Pro/Business (desde 1.3.0): socio único según el BORME. */
  ownership?: CompanyOwnership;
  /** Solo con get(cif, { admin: true }) en Pro/Business. since: fecha del nombramiento. */
  administrators?: Array<{ name: string; position: string; since?: string | null; [key: string]: any }>;
  /** Solo en el plan Free: indica que la respuesta viene recortada. */
  upsell_opportunities?: { campos_ocultos?: string[]; mensaje?: string; [key: string]: any };
}

export type StatusCode =
  | 'ACTIVE' | 'PRESUMED_ACTIVE' | 'INSOLVENCY' | 'IN_LIQUIDATION' | 'DISSOLVED'
  | 'REGISTRY_CLOSED' | 'MERGED' | 'INACTIVE' | 'EXTINCT' | 'UNKNOWN';

export interface CompanyIdentifiers {
  lei: string | null;
  /** Estado del LEI en GLEIF: ISSUED, LAPSED, RETIRED... */
  lei_status: string | null;
  lei_next_renewal_date: string | null;
  lei_source: 'gleif' | null;
  lei_as_of: string | null;
}

export interface CompanyLegalForm {
  /** SL, SA, SLP, SLL, SLNE, SAL, SAD, SCOOP, AIE, UTE, FUNDACION... */
  code: string | null;
  label: string | null;
  /** Código ELF de la ISO 20275 (DP3Q = SL, 5RDO = SA). null si la forma no tiene código propio. */
  iso20275: string | null;
  /** name: leída de la razón social; cif: deducida de la letra del CIF. */
  source: 'name' | 'cif' | null;
}

export interface CompanyParent {
  lei: string;
  name: string | null;
  country: string | null;
  /** CIF de la matriz si es una sociedad española con ficha. */
  cif: string | null;
}

export interface CompanyGroup {
  direct_parent: CompanyParent | null;
  ultimate_parent: CompanyParent | null;
  source: 'gleif';
  as_of: string | null;
}

export interface CompanyOwnership {
  /** true, false o null (no consta: el accionariado con varios socios no es público). */
  unipersonal: boolean | null;
  sole_shareholder: {
    name: string;
    type: 'person' | 'company';
    since: string | null;
    url_pdf: string | null;
  } | null;
  /** borme: último acto de unipersonalidad; name: solo el nombre dice SLU o SAU. */
  source: 'borme' | 'name' | null;
  as_of: string | null;
}

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
  identifiers?: CompanyIdentifiers;
  legal_form?: CompanyLegalForm;
  group?: CompanyGroup | null;
  ownership?: CompanyOwnership;
  checks: {
    name?: { provided: string; score: number; match: boolean };
    person?: { provided: string; is_current_admin: boolean; matched_name: string | null; position: string | null; since: string | null; is_sole_shareholder?: boolean };
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
  /** Estable: úsalo para no procesar dos veces el mismo evento. */
  id: string;
  type: WatchlistEventType;
  cif: string;
  company_name?: string | null;
  date: string;
  /** borme_act: act_types, description, url_pdf. status_change: status, status_code.
   *  risk_level_change: from, to, model_change (true/false, o null si no consta la versión del modelo). */
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
  | 'watchlist.*' | 'test.ping' | string;

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

/** Estado de una entrega de webhook. */
export type WebhookDeliveryStatus = 'pending' | 'processing' | 'retry' | 'delivered' | 'dead';

export interface WebhookDeliveriesOptions {
  /** Solo las entregas en ese estado. */
  status?: WebhookDeliveryStatus;
  /** Página (desde 1). */
  page?: number;
  /** Entregas por página (1-100). Por defecto, 50. */
  limit?: number;
}

export interface WebhookDelivery {
  delivery_id: string;
  event: string;
  /** id del evento de la vigilancia (el mismo de /watchlist/events), si lo hay. */
  event_id: string | null;
  status: WebhookDeliveryStatus;
  attempts: number;
  /** Código HTTP con el que respondió tu servidor. */
  http_status: number | null;
  duration_ms: number | null;
  error: string | null;
  created_at: string;
  last_attempt_at: string | null;
  next_attempt_at: string | null;
  completed_at: string | null;
}

export interface WebhookDeliveries {
  data: WebhookDelivery[];
  meta: { webhook_id: number; total: number; page: number; limit: number; has_more: boolean };
}

/** Valores válidos de los filtros (GET /catalog). No gasta consultas. */
export interface CatalogData {
  province: string[];
  cnae: { format: string; count: string };
  size_band: { value: string; label: string }[];
  status: string[];
  radar: { range: string[]; range_note: string; priority: string[]; main_act_type: string[] };
  score_priority: string[];
  webhook_events: string[];
  watchlist_event_types: string[];
  [key: string]: any;
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

export type Priority = 'muy_alta' | 'alta' | 'media' | 'baja' | 'muy_baja';

/** En el plan Free, priority, reasons y last_signal llegan como texto bloqueado ("🔒 Disponible en Pro"). */
export interface ScoreData {
  cif: string;
  score: number;
  priority: Priority | string;
  reasons: string[] | string;
  last_signal?: {
    type: string;
    date: string | null;
  } | string;
  source?: string;
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

/** Como mucho un elemento: el último acto del BORME que cuenta para la puntuación. */
export interface SignalEvent {
  type: 'borme_event' | string;
  label: string;
  date: string | null;
  /** Prioridad comercial de la empresa (la misma que priority en score), no la fiabilidad del evento. */
  probability: Priority | string;
}

export interface SignalsData {
  cif: string;
  signals: SignalEvent[];
}

// ---------------------------------------------------------------------------
// Consumo (/usage)
// ---------------------------------------------------------------------------

export interface UsageData {
  stats: {
    monthly_queries: number;
    total_queries: number;
    monthly_quota: number;
    remaining_calls: number;
    wallet_balance: number;
    plan_name: string;
    plan_slug: string;
    /** monthly (se renueva cada mes) o lifetime (Free: 100 en total). */
    quota_period: 'monthly' | 'lifetime';
    quota_resets_at: string | null;
    checkout_url?: string | null;
    recharge_url?: string | null;
  };
  /** Las 20 últimas empresas consultadas (respuesta 200 en producción). */
  history: Array<Record<string, any> & { cif: string; query_count: number; last_query: string; found: boolean }>;
}
