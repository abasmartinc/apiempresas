<?php

namespace App\Commands;

use App\Libraries\IndiceDirectorio;
use App\Libraries\Sectores;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Recalcula la caché de /listado-de-empresas (y de /base-de-datos-de-empresas, que usa
 * las mismas provincias) para que ningún visitante espere los recuentos (8,8 s).
 *
 *   php spark directorio:calentar
 *
 * Programarlo cada noche en el SERVIDOR (en local llena la caché local), después de la
 * carga del BORME, por ejemplo:
 *   15 5 * * * cd /ruta/a/apiempresas && php spark directorio:calentar >/dev/null 2>&1
 */
class CalentarDirectorio extends BaseCommand
{
    protected $group       = 'Background Tasks';
    protected $name        = 'directorio:calentar';
    protected $description = 'Recalcula la caché del índice de empresas por provincia y sector.';
    protected $usage       = 'directorio:calentar';

    public function run(array $params)
    {
        $t = microtime(true);

        // Nombres de sector primero: el índice los usa
        \Config\Services::cache()->delete('sectores_nombres_v1');
        $nombres = count(Sectores::nombres());

        $datos = IndiceDirectorio::datos(true);
        IndiceDirectorio::ultimas(true);

        // Lista de provincias que usa la redirección de slugs (Directory::provinciaDesdeSlug)
        \Config\Services::cache()->save('dir_provincias_nombres_v2', array_column($datos['provinces'], 'name'), 1296000);

        CLI::write(sprintf(
            'Índice listo: %d provincias, %d sectores (%d nombres CNAE), %.1f s',
            count($datos['provinces']),
            count($datos['cnaes']),
            $nombres,
            microtime(true) - $t
        ), 'green');
    }
}
