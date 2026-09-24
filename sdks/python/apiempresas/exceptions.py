class ApiError(Exception):
    """
    Excepción lanzada cuando la API de APIEmpresas devuelve un error.
    """
    def __init__(self, message: str, status: int = 500, error_code: str = None, raw_data: dict = None):
        super().__init__(message)
        self.status = status
        self.error_code = error_code
        self.raw_data = raw_data
        # Identificador estable del error (QUOTA_EXCEEDED, TOO_MANY_REQUESTS, API_KEY_INVALID...)
        self.code = raw_data.get('code') if isinstance(raw_data, dict) else None
