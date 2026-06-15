<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;

class BaseApiController extends ResourceController
{
    /**
     * Sobrescribimos el método respond de ResourceController para inyectar
     * propiedades del estándar RFC 7807 en caso de errores (success = false),
     * manteniendo los campos legacy (success, error, message) intactos.
     */
    public function respond($data = null, int $statusCode = null, string $message = '')
    {
        if (is_array($data)) {
            // Detectar si la respuesta es un error
            $isError = false;
            if (isset($data['success']) && $data['success'] === false) {
                $isError = true;
            } elseif (isset($data['error']) && !isset($data['success'])) {
                $isError = true;
            }

            if ($isError) {
                // Determinar el código HTTP final
                $finalStatusCode = $statusCode ?? $this->response->getStatusCode();
                if ($finalStatusCode === 200) {
                    $finalStatusCode = 400; // Fail-safe si olvidan el código de error HTTP
                }

                $errorCodeStr = (is_string($data['error'] ?? null)) ? $data['error'] : 'UNKNOWN_ERROR';
                $errorMessage = (is_string($data['message'] ?? null)) ? $data['message'] : 'Se ha producido un error no identificado.';

                // Inyectar campos RFC 7807 solo si no existen previamente para evitar sobrescrituras accidentales
                if (!isset($data['type'])) {
                    $data['type'] = 'https://apiempresas.com/docs/errors/' . strtolower($errorCodeStr);
                }
                if (!isset($data['title'])) {
                    $data['title'] = $errorCodeStr;
                }
                if (!isset($data['status'])) {
                    $data['status'] = $finalStatusCode;
                }
                if (!isset($data['detail'])) {
                    $data['detail'] = $errorMessage;
                }
                if (!isset($data['instance'])) {
                    $reqId = \App\Filters\ApiKeyFilter::$apiRequestId ?? 'req_' . bin2hex(random_bytes(4));
                    if ($reqId === '') $reqId = 'req_' . bin2hex(random_bytes(4));
                    $data['instance'] = $reqId;
                }
            }
        }

        return parent::respond($data, $statusCode, $message);
    }
}
