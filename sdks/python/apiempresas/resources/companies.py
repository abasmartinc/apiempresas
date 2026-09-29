class Companies:
    def __init__(self, client):
        self._client = client

    def get(self, cif: str, admin: bool = False) -> dict:
        """Obtiene los datos básicos de una empresa por su CIF. admin=True añade administradores (Pro/Business)."""
        params = {'cif': cif}
        if admin:
            params['admin'] = 'true'
        return self._client.request('GET', '/companies', params=params)

    def verify(self, cif: str, name: str = None, person: str = None, vat: bool = False) -> dict:
        """(Pro) Verificación KYB en una llamada: estado, nombre, administrador, VIES y alertas,
        con decision_hint pass / review / fail. Coste: 2 consultas."""
        params = {'cif': cif}
        if name:
            params['name'] = name
        if person:
            params['person'] = person
        if vat:
            params['vat'] = 'true'
        return self._client.request('GET', '/companies/verify', params=params)

    @staticmethod
    def _filter_params(filters: dict) -> dict:
        params = {}
        for k, v in filters.items():
            if v is None or v == '':
                continue
            if isinstance(v, bool):
                v = 'true' if v else 'false'
            elif isinstance(v, (list, tuple)):
                v = ','.join(str(x) for x in v)
            params[k] = v
        return params

    def reconcile(self, items) -> dict:
        """(Pro) Nombre a CIF, hasta 100 por petición. Cada elemento puede ser un texto o
        {'name': ..., 'province': ...}. Devuelve {'success', 'data', 'meta'}.
        1 consulta por cada "match"; ambiguous y no_match no se cobran."""
        norm = [{'name': i} if isinstance(i, str) else i for i in items]
        return self._client.request('POST', '/companies/reconcile', json_data={'items': norm})

    def count(self, **filters) -> int:
        """Cuántas empresas encajan con los filtros (cnae, province, municipality, status,
        founded_from, founded_to, has_phone, size_band, min_accounts_year). Gratis en todos los planes."""
        params = self._filter_params(filters)
        params['count_only'] = 'true'
        r = self._client.request('GET', '/companies/filter', params=params)
        return int(r.get('data', r).get('total', 0)) if isinstance(r, dict) else 0

    def filter(self, **filters) -> dict:
        """(Business) Empresas de un segmento. Devuelve {'success', 'data', 'meta'}.
        5 consultas por fila devuelta; para la página siguiente, pasa meta['next_cursor'] como cursor."""
        return self._client.request('GET', '/companies/filter', params=self._filter_params(filters))

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

    def radar(self, cif: str = None, province: str = None, priority: str = None, range: str = None,
              cnae: str = None, min_score: int = None, main_act_type: str = None, has_phone: bool = None) -> dict:
        """(Business) Radar de empresas nuevas. Filtros: province, priority, range, cnae, min_score,
        main_act_type, has_phone (cif se mantiene por compatibilidad; el Radar no lo usa)."""
        pares = (('cif', cif), ('province', province), ('priority', priority), ('range', range),
                 ('cnae', cnae), ('min_score', min_score), ('main_act_type', main_act_type), ('has_phone', has_phone))
        params = {}
        for k, v in pares:
            if v is None:
                continue
            params[k] = ('true' if v else 'false') if isinstance(v, bool) else v
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

