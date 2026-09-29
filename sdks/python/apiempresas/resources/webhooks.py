import hashlib
import hmac
import json
import time

from ..exceptions import ApiError

DEFAULT_TOLERANCE = 300


def verify_webhook_signature(raw_body, signature_header, secret: str, tolerance: int = DEFAULT_TOLERANCE, now: int = None) -> bool:
    """
    Comprueba la cabecera X-ApiEmpresas-Signature (t=<timestamp>,v1=<hex>).
    La firma es HMAC-SHA256(secret, "t.cuerpo") sobre el cuerpo en bruto
    (request.body en Django, request.get_data() en Flask), no el JSON ya parseado.
    tolerance: segundos de antigüedad aceptados (0 = sin límite).
    """
    if not signature_header or not secret:
        return False
    t = None
    v1 = []
    for part in str(signature_header).split(','):
        if '=' not in part:
            continue
        k, v = part.split('=', 1)
        k, v = k.strip(), v.strip()
        if k == 't':
            t = v
        elif k == 'v1':
            v1.append(v)
    if t is None or not t.isdigit() or not v1:
        return False
    if now is None:
        now = int(time.time())
    if tolerance and tolerance > 0 and abs(now - int(t)) > tolerance:
        return False
    body = raw_body if isinstance(raw_body, (bytes, bytearray)) else str(raw_body).encode('utf-8')
    expected = hmac.new(secret.encode('utf-8'), t.encode('ascii') + b'.' + bytes(body), hashlib.sha256).hexdigest()
    return any(hmac.compare_digest(expected, sig) for sig in v1)


def construct_webhook_event(raw_body, signature_header, secret: str, tolerance: int = DEFAULT_TOLERANCE) -> dict:
    """Comprueba la firma y devuelve el evento (id, event, created_at, data). Lanza ApiError si no es válida."""
    if not verify_webhook_signature(raw_body, signature_header, secret, tolerance):
        raise ApiError('Firma del webhook no válida o caducada.', status=400, error_code='INVALID_SIGNATURE')
    body = raw_body.decode('utf-8') if isinstance(raw_body, (bytes, bytearray)) else raw_body
    try:
        return json.loads(body)
    except ValueError:
        raise ApiError('El cuerpo del webhook no es JSON válido.', status=400, error_code='INVALID_PAYLOAD')


class Webhooks:
    """(Business) Webhooks: la API te avisa por POST firmado cuando cambia algo que vigilas."""

    verify_signature = staticmethod(verify_webhook_signature)
    construct_event = staticmethod(construct_webhook_event)

    def __init__(self, client):
        self._client = client

    def list(self) -> list:
        return self._client.request('GET', '/webhooks')

    def create(self, url: str, event: str, secret: str = None) -> dict:
        """Crea un webhook. Devuelve id, event y secret: guarda el secret para comprobar la firma."""
        body = {'url': url, 'event': event}
        if secret:
            body['secret'] = secret
        r = self._client.request('POST', '/webhooks', json_data=body)
        return {k: r.get(k) for k in ('id', 'event', 'secret', 'message')} if isinstance(r, dict) else r

    def remove(self, webhook_id: int) -> bool:
        r = self._client.request('DELETE', f'/webhooks/{int(webhook_id)}')
        return not (isinstance(r, dict) and r.get('success') is False)

    def test(self, webhook_id: int) -> dict:
        """Envía un test.ping y devuelve delivered, http_status, duration_ms, error, delivery_id.
        Si tu servidor falla, la API responde 502: aquí se devuelve igualmente el resultado."""
        try:
            return self._client.request('POST', f'/webhooks/{int(webhook_id)}/test')
        except ApiError as e:
            if e.status == 502 and isinstance(e.raw_data, dict) and isinstance(e.raw_data.get('data'), dict):
                return e.raw_data['data']
            raise
