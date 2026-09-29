class Watchlist:
    """
    (Pro / Business) Vigilancia de empresas: 100 en Pro, 1.000 en Business.
    Ninguna de estas llamadas consume consultas.
    """
    def __init__(self, client):
        self._client = client

    def list(self, page: int = None, limit: int = None) -> dict:
        """Empresas vigiladas. Devuelve {'success', 'data', 'meta'}."""
        params = {k: v for k, v in (('page', page), ('limit', limit)) if v is not None}
        return self._client.request('GET', '/watchlist', params=params or None)

    def add(self, cifs) -> dict:
        """Añade uno o varios CIF (str o lista). data: added, already_watching, not_found,
        invalid, rejected_over_limit. Si llegas al límite trae también message (y upgrade_url en Pro)."""
        if isinstance(cifs, str):
            cifs = [cifs]
        return self._client.request('POST', '/watchlist', json_data={'cifs': list(cifs)})

    def remove(self, cif: str) -> dict:
        """Quita una empresa de la vigilancia. Devuelve {'cif', 'removed'}."""
        from urllib.parse import quote
        r = self._client.request('DELETE', '/watchlist/' + quote(cif, safe=''))
        return r.get('data', r) if isinstance(r, dict) and 'meta' in r else r

    def events(self, since: str = None, types=None, cif: str = None, page: int = None, limit: int = None) -> dict:
        """Cambios en las empresas vigiladas (borme_act, status_change, risk_level_change),
        de más antiguo a más reciente. since: 'YYYY-MM-DD' (por defecto, hace 7 días; máximo 90).
        types: lista o texto separado por comas. Devuelve {'success', 'data', 'meta'}."""
        params = {}
        if since:
            params['since'] = since
        if types:
            params['types'] = types if isinstance(types, str) else ','.join(types)
        if cif:
            params['cif'] = cif
        if page is not None:
            params['page'] = page
        if limit is not None:
            params['limit'] = limit
        return self._client.request('GET', '/watchlist/events', params=params or None)
