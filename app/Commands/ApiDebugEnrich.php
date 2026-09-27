<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;


/**
 * Diagnóstico de los campos añadidos de la API para un CIF (27-09-2026).
 * Uso: php spark api:debug-enrich A46103834
 * Solo lee. Se puede borrar cuando ya no haga falta.
 */
class ApiDebugEnrich extends BaseCommand
{
    protected $group       = 'API';
    protected $name        = 'api:debug-enrich';
    protected $description = 'Muestra paso a paso de dónde salen status_code y financials para un CIF.';
    protected $usage       = 'api:debug-enrich <CIF>';

    public function run(array $params)
    {
        helper(['api', 'company']);
        $cif = strtoupper(trim($params[0] ?? 'A46103834'));
        $db = \Config\Database::connect();

        \CodeIgniter\CLI\CLI::write('1) Fila en companies (por CIF):', 'yellow');
        $fila = $db->table('companies')->select('id, cif, ventas_raw, ult_cuentas_anio, estado, estado_fecha')->where('cif', $cif)->get()->getRowArray();
        print_r($fila);

        $cacheKey = 'company_by_cif_' . md5(mb_strtolower($cif, 'UTF-8'));
        $cached = cache($cacheKey);
        \CodeIgniter\CLI\CLI::write("\n2) Ficha en caché ({$cacheKey}):", 'yellow');
        if (is_array($cached)) {
            \CodeIgniter\CLI\CLI::write('   id en caché: ' . ($cached['id'] ?? '(sin id)') . ' | id en BD: ' . ($fila['id'] ?? '(no está)'));
        } else {
            \CodeIgniter\CLI\CLI::write('   (no hay ficha en caché)');
        }

        $ref = new \ReflectionClass(\App\Services\ApiCompanyEnricher::class);
        \CodeIgniter\CLI\CLI::write("\n3) Archivo del servicio: " . $ref->getFileName() . ' (' . date('Y-m-d H:i:s', filemtime($ref->getFileName())) . ')', 'yellow');
        $src = file_get_contents($ref->getFileName());
        \CodeIgniter\CLI\CLI::write('   Versión que busca por CIF: ' . (strpos($src, "whereIn('cif', \$cifs)") !== false ? 'SÍ' : 'NO (versión antigua)'));

        $base = is_array($cached) ? $cached : $this->fichaApi($cif);
        \CodeIgniter\CLI\CLI::write("\n4) Resultado de enrich() con acceso completo:", 'yellow');
        $out = \App\Services\ApiCompanyEnricher::enrich([$base], true)[0];
        print_r(['id_usado' => $out['id'] ?? null, 'status_code' => $out['status_code'] ?? null,
                 'status_source' => $out['status_source'] ?? null, 'financials' => $out['financials'] ?? null]);

        \CodeIgniter\CLI\CLI::write("\n5) OPcache en PHP de consola: " . (function_exists('opcache_get_status') && @opcache_get_status(false) ? 'activo' : 'inactivo')
            . ' (el de la web puede ser distinto: mira opcache.enable y opcache.validate_timestamps en el php.ini de Laragon)', 'yellow');
    }

    private function fichaApi(string $cif): array
    {
        return (new \App\Models\CompanyModel())->getByCif($cif, true) ?? ['cif' => $cif];
    }
}
