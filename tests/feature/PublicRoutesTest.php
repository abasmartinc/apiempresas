<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * PublicRoutesTest (Black-Box Integration)
 * 
 * Esta suite comprueba que el sitio web esta "vivo" desde fuera,
 * como lo veria un usuario real o un bot de Google.
 * 
 * Ventajas:
 * - Valida toda la pila (Apache/Nginx, PHP, MySQL).
 * - No interfiere con el codigo interno del framework.
 * - Detecta errores 404, 500 y cuelgues de base de datos reales.
 */
class PublicRoutesTest extends TestCase
{
    private Client $client;
    private string $baseUrl;

    protected function setUp(): void
    {
        parent::setUp();
        // Usamos la URL local del proyecto en Laragon
        $this->baseUrl = 'http://localhost/apiempresas/';
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 10.0,
            'http_errors' => false, // No lanzar excepcion en 404/500 para poder asertar
            'verify' => false,      // Ignorar errores de SSL local
        ]);
    }

    private function assertRouteWorks(string $route, string $expectedText = ''): void
    {
        try {
            $response = $this->client->request('GET', ltrim($route, '/'));
            $status = $response->getStatusCode();
            
            $this->assertEquals(200, $status, "La ruta '{$route}' fallo con estado {$status}");
            
            if ($expectedText) {
                $body = (string) $response->getBody();
                $this->assertStringContainsString($expectedText, $body, "La ruta '{$route}' no contiene el texto esperado: '{$expectedText}'");
            }
        } catch (GuzzleException $e) {
            $this->fail("Error de conexion al intentar acceder a '{$route}': " . $e->getMessage());
        }
    }

    /**
     * Menu Superior y Core
     */
    public function testHeaderNavigation()
    {
        $this->assertRouteWorks('/', 'Validar CIF');
        $this->assertRouteWorks('api-empresas', 'Infraestructura API');
        $this->assertRouteWorks('leads-empresas-nuevas', 'Radar');
        $this->assertRouteWorks('blog', 'Blog');
        $this->assertRouteWorks('documentation', 'Documentación');
        $this->assertRouteWorks('enter', 'Acceso al panel');
        $this->assertRouteWorks('register', 'Crea tu cuenta');
    }

    /**
     * Footer Radar
     */
    public function testFooterRadarRoutes()
    {
        $this->assertRouteWorks('empresas-nuevas-hoy', 'hoy');
        $this->assertRouteWorks('empresas-nuevas-semana', 'semana');
        $this->assertRouteWorks('empresas-nuevas-mes', 'últimos 30 días');
        $this->assertRouteWorks('empresas-nuevas', 'Radar');
        $this->assertRouteWorks('directorio/ultimas-empresas-registradas');
    }

    /**
     * Herramientas y Buscador
     */
    public function testUtilitiesRoutes()
    {
        $this->assertRouteWorks('search_company', 'Buscador');
        $this->assertRouteWorks('autocompletado-cif-empresas', 'Autocompletado');
        $this->assertRouteWorks('contact', 'Contacto');
    }

    /**
     * Provincias
     */
    public function testProvinceRoutes()
    {
        $this->assertRouteWorks('empresas-nuevas/madrid', 'Madrid');
        $this->assertRouteWorks('empresas-nuevas/barcelona', 'Barcelona');
        $this->assertRouteWorks('directorio', 'Provincias');
    }

    /**
     * Fichas de Empresa (Muestreo real de Base de Datos)
     */
    public function testBusinessProfiles()
    {
        $samples = [
            'B75514570-karola-producciones-eventos-y-espectaculos-sl',
            'B29620630-electromuebles-ruiz-e-hijos-sll',
            'B57499436-somtres-saume-sl',
            'B63667380-estal-zone-sl'
        ];

        foreach ($samples as $slug) {
            // Verificamos que cargue el estado 200 y contenga el CIF que es el identificador unico
            $cif = explode('-', $slug)[0];
            $this->assertRouteWorks($slug, $cif);
        }
    }

    /**
     * Rutas Dinamicas SEO
     */
    public function testDynamicSeoRoutes()
    {
        // Informe de mercado SEO
        $this->assertRouteWorks('informes/empresas-nuevas-de-hosteleria-en-madrid', 'Hostelería');
    }
}
