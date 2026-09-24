export class ApiError extends Error {
  public status: number;
  public errorCode?: string;
  /** Identificador estable del error (QUOTA_EXCEEDED, TOO_MANY_REQUESTS, API_KEY_INVALID...). */
  public code?: string;
  public rawData?: any;

  constructor(status: number, message: string, errorCode?: string, rawData?: any) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errorCode = errorCode;
    this.rawData = rawData;
    this.code = rawData && typeof rawData.code === 'string' ? rawData.code : undefined;

    // Fix prototype chain for built-in classes in TS
    Object.setPrototypeOf(this, ApiError.prototype);
  }
}
