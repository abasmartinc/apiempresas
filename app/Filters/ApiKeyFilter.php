<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiKeyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1) Leer API key (header recomendado)
        $apiKey = trim((string) $request->getHeaderLine('X-API-KEY'));

        // 2) Alternativa: permitir también "Authorization: Bearer <APIKEY>" si quieres
        if ($apiKey === '') {
            $auth = trim((string) $request->getHeaderLine('Authorization'));
            if (stripos($auth, 'Bearer ') === 0) {
                $apiKey = trim(substr($auth, 7));
            }
        }

        if ($apiKey === '') {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Falta la API key (X-API-KEY).']);
        }

        // 3) Validar contra DB
        $db = \Config\Database::connect('default');

        $row = $db->table('api_keys')
            ->select('api_keys.id, api_keys.user_id, api_keys.is_active, users.is_active as user_active')
            ->join('users', 'users.id = api_keys.user_id', 'inner')
            ->where('api_keys.api_key', $apiKey)
            ->get()
            ->getRow();

        if (!$row) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'API key inválida']);
        }

        if ((int)$row->is_active !== 1 || (int)$row->user_active !== 1) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON(['error' => 'API key inactiva o usuario inactivo']);
        }

        // 4) Registrar uso (opcional)
        $db->table('api_keys')
            ->where('id', (int)$row->id)
            ->update(['last_used_at' => date('Y-m-d H:i:s')]);

        // 5) (Opcional) Exponer el user_id al resto de la request
        // CI4 no tiene “attributes” estándar, pero puedes “inyectar” en globals del request:
        // Esto te permite hacer: $this->request->getVar('__auth_user_id') en controladores
        $request->setGlobal('get', array_merge($request->getGet(), [
            '__auth_user_id' => (int)$row->user_id,
            '__auth_api_key_id' => (int)$row->id,
        ]));

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nada
    }
}
