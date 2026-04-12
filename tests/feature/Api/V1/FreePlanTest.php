<?php

namespace Tests\Feature\Api\V1;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FreePlanTest extends TestCase
{
    private Client $client;
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->baseUrl = 'http://localhost/apiempresas/';
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 5.0,
            'http_errors' => false,
            'verify' => false,
        ]);
    }

    /**
     * Test que valida el formato JSON para el plan Free
     * segun los requisitos del usuario.
     */
    public function testFreePlanMasking()
    {
        $apiKey = 'fb8f4c010f8a5ddd75840dd733c456cb1af46d99b25443f4c41771665c81c89d';
        $cif    = 'B24920787';

        try {
            $response = $this->client->request('GET', "api/v1/companies?cif={$cif}", [
                'headers' => [
                    'X-API-KEY' => $apiKey
                ]
            ]);

            $this->assertEquals(200, $response->getStatusCode());
            
            $json = json_decode((string)$response->getBody(), true);

            $this->assertTrue($json['success']);
            $data = $json['data'];

            // 1. Validar que el CIF NO esté enmascarado
            $this->assertEquals($cif, $data['cif'], "El CIF no debe estar enmascarado en el plan Free");

            // 2. Validar que el Objeto Social esté truncado con el mensaje Pro
            $this->assertStringContainsString('[ACTUALIZA A PRO PARA VER EL DETALLE COMPLETO]', $data['corporate_purpose']);

            // 3. Validar el formato de la dirección
            $this->assertStringContainsString('(', $data['address']);
            $this->assertStringContainsString('Alicante', $data['address']);
            $this->assertStringStartsWith('*', $data['address']);
            
            // 4. Otros campos
            $this->assertEquals('THE GRILL IN LOVE MADRID SL', $data['name']);
            $this->assertEquals('5611', $data['cnae']);

        } catch (GuzzleException $e) {
            $this->fail("Error de conexión a la API: " . $e->getMessage());
        }
    }
}
