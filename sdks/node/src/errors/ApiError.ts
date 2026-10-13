export class ApiError extends Error {
  public status: number;
  public errorCode?: string;
  /** Identificador estable del error (QUOTA_EXCEEDED, TOO_MANY_REQUESTS, API_KEY_INVALID...). */
  public code?: string;
  public rawData?: any;
  /** Identificador de la petición (cabecera X-Request-Id o campo instance). Dáselo a soporte. */
  public requestId?: string;
  /** Segundos a esperar antes de reintentar (cabecera Retry-After). Solo en 429 que se arreglan esperando. */
  public retryAfter?: number;
  /** Plan necesario para la ruta ('pro' o 'business'), en los 403 de plan. */
  public requiredPlan?: string;
  /** Enlace para cambiar de plan o comprar créditos, si la API lo incluye. */
  public checkoutUrl?: string;

  constructor(status: number, message: string, errorCode?: string, rawData?: any, headers?: Headers) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errorCode = errorCode;
    this.rawData = rawData;
    const d = rawData && typeof rawData === 'object' ? rawData : {};
    this.code = typeof d.code === 'string' ? d.code : undefined;
    this.requestId = headers?.get('x-request-id') || (typeof d.instance === 'string' ? d.instance : undefined);
    const ra = headers?.get('retry-after');
    this.retryAfter = ra && /^\d+$/.test(ra) ? parseInt(ra, 10) : undefined;
    this.requiredPlan = typeof d.required_plan === 'string' ? d.required_plan : undefined;
    const up = d.upsell_opportunities && typeof d.upsell_opportunities === 'object' ? d.upsell_opportunities : {};
    this.checkoutUrl = [d.checkout_url, up.checkout_url, d.upgrade_url, up.upgrade_url].find((u) => typeof u === 'string' && u !== '');

    // Fix prototype chain for built-in classes in TS
    Object.setPrototypeOf(this, ApiError.prototype);
  }
}
