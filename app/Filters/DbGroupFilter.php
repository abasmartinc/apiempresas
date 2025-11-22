<?php


namespace App\Filters;

use App\Libraries\Tenant;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class DbGroupFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $cfg = config('Tenants');
        $headerName = $cfg->headerName;
        $queryParam = $cfg->queryParam;

        // 1) Orden de preferencia: Header -> query param -> default
        $dbGroup = $request->getHeaderLine($headerName);
        if (!$dbGroup) {
            $dbGroup = $request->getGet($queryParam);
        }
        if (!$dbGroup) {
            $dbGroup = $cfg->defaultDbGroup; // podría ser null
        }

        // 2) Validar presencia
        if (empty($dbGroup)) {
            return service('response')
                ->setStatusCode(400)
                ->setJSON([
                    'status' => 'error',
                    'message' => "Missing required DB group. Send header '{$headerName}' or query '{$queryParam}'.",
                ]);
        }

        // 3) Validar que esté en la lista blanca
        if (!in_array($dbGroup, $cfg->allowedDbGroups, true)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => "Invalid DB group '{$dbGroup}'.",
                ]);
        }

        // 4) Fijar en el contexto Tenant
        Tenant::set($dbGroup);

        // Nota: no retornamos nada para continuar la cadena.
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Limpieza opcional
        Tenant::set(null);
    }
}
