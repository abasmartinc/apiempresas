class Companies:
    def __init__(self, client):
        self._client = client

    def get(self, cif: str, admin: bool = False) -> dict:
        """Obtiene los datos básicos de una empresa por su CIF. admin=True añade administradores (Pro/Business)."""
        params = {'cif': cif}
        if admin:
            params['admin'] = 'true'
        return self._client.request('GET', '/companies', params=params)

    def search(self, q: str) -> dict:
        """Busca empresas por nombre o razón social."""
        return self._client.request('GET', '/companies/search', params={'q': q})

    def search_multiple(self, q: str, limit: int = None, page: int = None, cursor: str = None) -> dict:
        """Búsqueda con varios resultados (multiple=true). Para la página siguiente, pasa meta['next_cursor'] como cursor."""
        params = {'q': q, 'multiple': 'true'}
        for k, v in (('limit', limit), ('page', page), ('cursor', cursor)):
            if v is not None:
                params[k] = v
        return self._client.request('GET', '/companies/search', params=params)

    def batch(self, cifs: list) -> dict:
        """Consulta múltiple de CIFs en una sola petición."""
        return self._client.request('POST', '/companies/batch', json_data={'cifs': cifs})

    def score(self, cif: str) -> dict:
        """(Pro) Obtiene el Scoring Comercial de una empresa."""
        return self._client.request('GET', '/companies/score', params={'cif': cif})

    def borme(self, cif: str) -> dict:
        """(Pro) Obtiene el historial de actos del BORME de una empresa."""
        return self._client.request('GET', '/companies/borme', params={'cif': cif})

    def signals(self, cif: str) -> dict:
        """(Pro) Obtiene las señales societarias recientes de una empresa."""
        return self._client.request('GET', '/companies/signals', params={'cif': cif})

    def insights(self, cif: str) -> dict:
        """(Business) Obtiene Insights IA de una empresa."""
        return self._client.request('GET', '/companies/insights', params={'cif': cif})

    def contact_prep(self, cif: str) -> dict:
        """(Pro) Obtiene datos de contacto y preparación de la empresa."""
        return self._client.request('GET', '/companies/contact-prep', params={'cif': cif})

    def radar(self, cif: str = None, province: str = None, priority: str = None, range: str = None) -> dict:
        """(Business) Radar de empresas nuevas. Filtros: province, priority, range (cif se mantiene por compatibilidad; el Radar no lo usa)."""
        params = {k: v for k, v in (('cif', cif), ('province', province), ('priority', priority), ('range', range)) if v is not None}
        return self._client.request('GET', '/companies/radar', params=params)

    def match(self, cif: str, seller_sector: str = None) -> dict:
        """(Business) Match avanzado. seller_sector es obligatorio en la API (sin él responde 400)."""
        params = {'cif': cif}
        if seller_sector:
            params['seller_sector'] = seller_sector
        return self._client.request('GET', '/companies/match', params=params)

    def network(self, cif: str) -> dict:
        """(Business) Obtiene la red o entramado societario (Network) de una empresa."""
        return self._client.request('GET', '/companies/network', params={'cif': cif})

    def contracts(self, cif: str, page: int = 1, limit: int = 20) -> dict:
        """(Business) Obtiene los contratos públicos y licitaciones adjudicadas a una empresa."""
        return self._client.request('GET', '/companies/contracts', params={'cif': cif, 'page': page, 'limit': limit})

    def risk_profile(self, cif: str) -> dict:
        """(Business) Obtiene el perfil de riesgo corporativo y solvencia de una empresa."""
        return self._client.request('GET', '/companies/risk-profile', params={'cif': cif})

