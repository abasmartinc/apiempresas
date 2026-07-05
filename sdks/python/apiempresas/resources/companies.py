class Companies:
    def __init__(self, client):
        self._client = client

    def get(self, cif: str) -> dict:
        """Obtiene los datos básicos de una empresa por su CIF."""
        return self._client.request('GET', '/companies', params={'cif': cif})

    def search(self, q: str) -> dict:
        """Busca empresas por nombre o razón social."""
        return self._client.request('GET', '/companies/search', params={'q': q})

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

    def radar(self, cif: str) -> dict:
        """(Business) Obtiene la información del Radar de empresas."""
        return self._client.request('GET', '/companies/radar', params={'cif': cif})

    def match(self, cif: str) -> dict:
        """(Business) Realiza un match avanzado con los datos de una empresa."""
        return self._client.request('GET', '/companies/match', params={'cif': cif})

    def network(self, cif: str) -> dict:
        """(Business) Obtiene la red o entramado societario (Network) de una empresa."""
        return self._client.request('GET', '/companies/network', params={'cif': cif})
