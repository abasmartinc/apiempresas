<?php

namespace ApiEmpresas\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected int $statusCode;
    protected ?string $errorCode;
    protected ?array $rawData;
    /** @var array<string,string> Cabeceras de la respuesta, en minúsculas */
    protected array $headers;

    public function __construct(string $message, int $statusCode = 500, ?string $errorCode = null, ?array $rawData = null, array $headers = [])
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->rawData = $rawData;
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    /** Identificador de la petición (X-Request-Id o instance). Dáselo a soporte. */
    public function getRequestId(): ?string
    {
        if (!empty($this->headers['x-request-id'])) {
            return $this->headers['x-request-id'];
        }
        return isset($this->rawData['instance']) && is_string($this->rawData['instance']) ? $this->rawData['instance'] : null;
    }

    /** Segundos a esperar antes de reintentar (Retry-After). Solo en los 429 que se arreglan esperando. */
    public function getRetryAfter(): ?int
    {
        $v = $this->headers['retry-after'] ?? null;
        return ($v !== null && ctype_digit($v)) ? (int) $v : null;
    }

    /** Plan necesario para la ruta ('pro' o 'business'), en los 403 de plan. */
    public function getRequiredPlan(): ?string
    {
        return isset($this->rawData['required_plan']) && is_string($this->rawData['required_plan']) ? $this->rawData['required_plan'] : null;
    }

    /** Enlace para cambiar de plan o comprar créditos, si la API lo incluye. */
    public function getCheckoutUrl(): ?string
    {
        $up = isset($this->rawData['upsell_opportunities']) && is_array($this->rawData['upsell_opportunities']) ? $this->rawData['upsell_opportunities'] : [];
        foreach ([$this->rawData['checkout_url'] ?? null, $up['checkout_url'] ?? null, $this->rawData['upgrade_url'] ?? null, $up['upgrade_url'] ?? null] as $u) {
            if (is_string($u) && $u !== '') {
                return $u;
            }
        }
        return null;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    /**
     * Identificador estable del error (getCode() es el código HTTP) (QUOTA_EXCEEDED, TOO_MANY_REQUESTS, API_KEY_INVALID...).
     */
    public function getApiCode(): ?string
    {
        return isset($this->rawData['code']) && is_string($this->rawData['code']) ? $this->rawData['code'] : null;
    }
}
