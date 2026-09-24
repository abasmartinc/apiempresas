<?php

namespace ApiEmpresas\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected int $statusCode;
    protected ?string $errorCode;
    protected ?array $rawData;

    public function __construct(string $message, int $statusCode = 500, ?string $errorCode = null, ?array $rawData = null)
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->rawData = $rawData;
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
