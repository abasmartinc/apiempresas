<?php

namespace ApiEmpresas\Tests;

use ApiEmpresas\ApiEmpresas;
use ApiEmpresas\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

class ApiEmpresasTest extends TestCase
{
    public function testThrowsExceptionIfApiKeyIsEmpty()
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage("La propiedad 'apiKey' es obligatoria para inicializar ApiEmpresas.");
        
        new ApiEmpresas('');
    }

    public function testCanInstantiateWithApiKey()
    {
        $api = new ApiEmpresas('fake_api_key');
        $this->assertInstanceOf(ApiEmpresas::class, $api);
    }
}
