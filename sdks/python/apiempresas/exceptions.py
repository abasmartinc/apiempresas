class ApiError(Exception):
    """
    Excepción lanzada cuando la API de APIEmpresas devuelve un error.
    """
    def __init__(self, message: str, status: int = 500, error_code: str = None, raw_data: dict = None, headers=None):
        super().__init__(message)
        self.status = status
        self.error_code = error_code
        self.raw_data = raw_data
        d = raw_data if isinstance(raw_data, dict) else {}
        h = headers or {}
        # Identificador estable del error (QUOTA_EXCEEDED, TOO_MANY_REQUESTS, API_KEY_INVALID...)
        self.code = d.get('code') if isinstance(d.get('code'), str) else None
        # Identificador de la petición (X-Request-Id o instance). Dáselo a soporte.
        self.request_id = h.get('X-Request-Id') or (d.get('instance') if isinstance(d.get('instance'), str) else None)
        # Segundos a esperar antes de reintentar (Retry-After). Solo en los 429 que se arreglan esperando.
        ra = h.get('Retry-After')
        self.retry_after = int(ra) if isinstance(ra, str) and ra.isdigit() else None
        # Plan necesario ('pro' o 'business') en los 403 de plan
        self.required_plan = d.get('required_plan') if isinstance(d.get('required_plan'), str) else None
        # Enlace para cambiar de plan o comprar créditos, si la API lo incluye
        up = d.get('upsell_opportunities') if isinstance(d.get('upsell_opportunities'), dict) else {}
        self.checkout_url = next((u for u in (d.get('checkout_url'), up.get('checkout_url'), d.get('upgrade_url'), up.get('upgrade_url'))
                                  if isinstance(u, str) and u), None)
