<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class GeocodeCompanies extends BaseCommand
{
    protected $group       = 'Geocoding';
    protected $name        = 'geocode:run';
    protected $description = 'Completa las coordenadas de las empresas usando Nominatim (OpenStreetMap).';
    protected $usage       = 'geocode:run [limit]';
    protected $arguments   = [
        'limit' => 'Número máximo de empresas a procesar (default: 50)'
    ];

    public function run(array $params)
    {
        $limit = $params[0] ?? 50;
        $db = Database::connect();
        
        CLI::write("Buscando empresas sin coordenadas (máximo $limit)...", 'cyan');

        $companies = $db->table('companies')
            ->select('id, address, company_name')
            ->groupStart()
                ->where('lat_num', 0)
                ->orWhere('lat_num', null)
                ->orWhere('lng_num', 0)
                ->orWhere('lng_num', null)
            ->groupEnd()
            ->where('address !=', '')
            ->where('address IS NOT NULL', null, false)
            ->limit($limit)
            ->get()->getResultArray();

        if (empty($companies)) {
            CLI::write('No hay empresas pendientes de geocodificación.', 'yellow');
            return;
        }

        CLI::write('Procesando ' . count($companies) . ' empresas...', 'cyan');

        foreach ($companies as $company) {
            $address = $company['address'];
            CLI::write("--------------------------------------------------");
            CLI::write("Empresa: " . $company['company_name']);
            CLI::write("Dirección original: " . $address);

            $coords = $this->getCoordinates($address);

            if ($coords) {
                // RECORDATORIO: lat_num = Longitud, lng_num = Latitud (Invertido en este proyecto)
                $db->table('companies')
                    ->where('id', $company['id'])
                    ->update([
                        'lat_num' => $coords['lat'], // Latitude
                        'lng_num' => $coords['lng'], // Longitude
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                CLI::write("✅ OK: Lat=" . $coords['lat'] . " Lng=" . $coords['lng'], 'green');
            } else {
                CLI::write("❌ No se encontraron coordenadas.", 'red');
            }

            // Respetar límite de Nominatim (1 req/sec)
            sleep(1);
        }

        CLI::write("--------------------------------------------------");
        CLI::write('Proceso finalizado.', 'cyan');
    }

    private function getCoordinates($address)
    {
        // Limpieza básica de la dirección para mejorar el match
        // Ejemplo: "JOSE ABASCAL, 44 , 4 D. 28003 MADRID. (MADRID)." -> "JOSE ABASCAL, 44 , 28003 MADRID"
        $cleanAddress = preg_replace('/\s+/', ' ', $address);
        $cleanAddress = str_replace([' (MADRID).', ' (MADRID)', ' MADRID.'], ' MADRID', $cleanAddress);
        
        // Quitar partes que suelen confundir al geocoder (piso, puerta...) si vienen después de coma
        // "CALLE FALSA 123, 2-B, 28001 MADRID" -> "CALLE FALSA 123, 28001 MADRID"
        // Este regex es conservador
        $cleanAddress = preg_replace('/, [0-9]+[\s]*[-][\s]*[A-Z0-9]+,/i', ',', $cleanAddress);

        $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" . urlencode($cleanAddress);
        
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: APIEmpresasGeocoder/1.0 (ariel@apiempresas.es)\r\n"
            ]
        ];
        
        $context = stream_context_create($opts);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            CLI::error("Error en la petición a Nominatim.");
            return null;
        }

        $data = json_decode($response, true);

        if (!empty($data) && isset($data[0])) {
            return [
                'lat' => $data[0]['lat'],
                'lng' => $data[0]['lon']
            ];
        }

        return null;
    }
}
