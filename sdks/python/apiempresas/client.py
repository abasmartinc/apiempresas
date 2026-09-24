import requests
from .exceptions import ApiError
from .resources.companies import Companies

SDK_VERSION = '1.1.0'


def _extract_error_message(data):
    """Según el error, la API pone el texto en message, detail, error o messages."""
    if not isinstance(data, dict):
        return None
    for key in ('message', 'detail', 'error'):
        value = data.get(key)
        if isinstance(value, str) and value:
            return value
    messages = data.get('messages')
    if isinstance(messages, dict) and messages:
        first = next(iter(messages.values()))
        if isinstance(first, str) and first:
            return first
    return None

class ApiEmpresas:
    """
    Cliente principal para interactuar con la API de APIEmpresas.es
    """
    def __init__(self, api_key: str, base_url: str = 'https://apiempresas.es/api/v1', timeout: int = 30):
        if not api_key or not api_key.strip():
            raise ApiError("La propiedad 'api_key' es obligatoria para inicializar ApiEmpresas.", status=401)
        
        self.api_key = api_key
        self.base_url = base_url.rstrip('/')
        self.timeout = timeout
        
        # Iniciar sesión de requests para reutilizar conexiones
        self.session = requests.Session()
        self.session.headers.update({
            'X-API-KEY': self.api_key,
            'Accept': 'application/json',
            'User-Agent': f'apiempresas-python/{SDK_VERSION}',
        })
        
        # Inicializar recursos
        self.companies = Companies(self)

    def request(self, method: str, endpoint: str, params: dict = None, json_data: dict = None) -> dict:
        """
        Método interno genérico para lanzar las peticiones HTTP.
        """
        url = f"{self.base_url}{endpoint}"
        
        try:
            response = self.session.request(
                method=method,
                url=url,
                params=params,
                json=json_data,
                timeout=self.timeout
            )
        except requests.exceptions.RequestException as e:
            raise ApiError(f"Error de red al conectar con APIEmpresas: {str(e)}", status=500)
            
        try:
            decoded_data = response.json()
        except ValueError:
            decoded_data = None
            
        if not response.ok:
            status = response.status_code
            message = f"{status} Error desconocido en la API"
            error_code = None
            
            if decoded_data and isinstance(decoded_data, dict):
                message = _extract_error_message(decoded_data) or message
                error_code = decoded_data.get('error')
            else:
                message = response.text
                
            raise ApiError(message, status=status, error_code=error_code, raw_data=decoded_data)
            
        if decoded_data and 'data' in decoded_data:
            # Para el endpoint /batch, se suele devolver meta y data
            if 'meta' in decoded_data:
                return decoded_data
            return decoded_data['data']
            
        return decoded_data

