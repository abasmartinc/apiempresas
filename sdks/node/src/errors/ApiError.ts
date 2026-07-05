export class ApiError extends Error {
  public status: number;
  public errorCode?: string;
  public rawData?: any;

  constructor(status: number, message: string, errorCode?: string, rawData?: any) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errorCode = errorCode;
    this.rawData = rawData;

    // Fix prototype chain for built-in classes in TS
    Object.setPrototypeOf(this, ApiError.prototype);
  }
}
