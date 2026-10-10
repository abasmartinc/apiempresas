<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Muestra de la base de datos para revisar la CALIDAD de los datos (solo lectura).
 *
 *   php spark db:muestra                 1 de cada 50 empresas (unas 85.000)
 *   php spark db:muestra --cada 25       1 de cada 25
 *   php spark db:muestra --pausa 300     milisegundos de pausa entre consultas (por defecto 150)
 *
 * `db:volcado` copia las ÚLTIMAS filas de cada tabla grande: sirve para ver el uso, pero
 * las últimas empresas son las recién creadas y no representan a la base. Este comando
 * saca una muestra repartida por toda la tabla y, además, las empresas que los clientes
 * de la API han pedido de verdad, con todo lo que cuelga de cada una (actos del BORME,
 * administradores, enriquecimiento, riesgo, contratos, subvenciones, grupo).
 *
 * Escribe writable/contexto/muestra.sql.gz.
 *
 * Cuidado con producción: no hace ninguna consulta sobre tablas enteras. Las empresas se
 * leen por tramos de clave primaria y lo relacionado por lotes de ids con índice, con una
 * pausa entre consultas. No modifica nada: solo SHOW y SELECT.
 */
class DbMuestraCalidad extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:muestra';
    protected $description = 'Muestra repartida de empresas y sus datos relacionados para revisar la calidad (writable/contexto/muestra.sql.gz)';
    protected $usage       = 'db:muestra [--cada 50] [--pausa 150]';

    /** Tablas pequeñas de referencia que se copian enteras (si existen) */
    private const REFERENCIA = ['cnae_classes', 'cnae_subclasses', 'cnae_2009_2025', 'municipalities', 'provinces', 'api_plans', 'aeat_debtors'];

    /** Tablas que cuelgan de la empresa por su id: tabla => columna */
    private const POR_ID = [
        'borme_posts'            => 'company_id',
        'company_administrators' => 'company_id',
        'company_enrichment'     => 'company_id',
        'company_radar_scores'   => 'company_id',
        'company_previous_names' => 'company_id',
        'company_lei'            => 'company_id',
    ];

    /** Tablas que cuelgan de la empresa por su CIF: tabla => columna */
    private const POR_CIF = [
        'company_risk_profiles' => 'cif',
        'company_contracts'     => 'company_cif',
        'company_subsidies'     => 'company_cif',
    ];

    private $db;
    private $gz;
    private int $pausa = 150;

    public function run(array $params)
    {
        $cada        = max(2, (int) (CLI::getOption('cada') ?? 50));
        $this->pausa = max(0, (int) (CLI::getOption('pausa') ?? 150));

        $this->db = \Config\Database::connect();
        $baseDir  = WRITEPATH . 'contexto';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }
        $ruta     = $baseDir . DIRECTORY_SEPARATOR . 'muestra.sql.gz';
        $this->gz = gzopen($ruta, 'wb6');
        if (!$this->gz) {
            CLI::error('No se puede escribir ' . $ruta);
            return;
        }

        $t0 = time();
        CLI::write('Base de datos: ' . $this->db->getDatabase() . ' en ' . $this->db->hostname, 'cyan');
        CLI::write("Muestra: 1 de cada {$cada} empresas, pausa de {$this->pausa} ms entre consultas.", 'cyan');
        gzwrite($this->gz, '-- Muestra de calidad de ' . $this->db->getDatabase() . ' (' . date('Y-m-d H:i:s') . "), 1 de cada {$cada}\n"
            . "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

        $existentes = array_map(static fn ($f) => (string) array_values($f)[0],
            $this->db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->getResultArray());
        $existe = static fn (string $t) => in_array($t, $existentes, true);

        // 1) Tamaños aproximados de las tablas (sin contar: estimación del motor)
        $this->totales($existentes);

        // 2) Tablas de referencia, enteras
        foreach (self::REFERENCIA as $tabla) {
            if ($existe($tabla)) {
                $this->crearTabla($tabla);
                $n = $this->volcar($tabla, 'SELECT * FROM `' . $tabla . '`');
                CLI::write("  {$tabla}: {$n} filas (entera)");
            }
        }

        // 3) Empresas: una de cada N, por tramos de clave primaria
        $this->crearTabla('companies');
        $lim   = $this->db->query('SELECT MIN(id) AS a, MAX(id) AS b FROM companies')->getRowArray();
        $minId = (int) ($lim['a'] ?? 0);
        $maxId = (int) ($lim['b'] ?? 0);
        $ids   = [];
        $cifs  = [];
        $tramo = 20000;
        CLI::write("Empresas: ids de {$minId} a {$maxId}...");
        for ($desde = $minId; $desde <= $maxId; $desde += $tramo) {
            $hasta = $desde + $tramo - 1;
            $filas = $this->db->query(
                "SELECT * FROM companies WHERE id BETWEEN {$desde} AND {$hasta} AND MOD(id, {$cada}) = 0"
            )->getResultArray();
            $this->insertar('companies', $filas);
            foreach ($filas as $f) {
                $ids[(int) $f['id']] = true;
                if (!empty($f['cif'])) {
                    $cifs[(string) $f['cif']] = true;
                }
            }
            $this->descanso();
        }
        CLI::write('  companies (muestra): ' . number_format(count($ids), 0, ',', '.') . ' empresas');

        // 4) Lo que piden los clientes de la API (últimos 90 días), con su resultado
        $pedidos = $this->cifsPedidos($existe);
        if (!empty($pedidos)) {
            $extra = 0;
            foreach (array_chunk(array_keys($pedidos), 300) as $lote) {
                $lista = implode(',', array_map(fn ($c) => $this->db->escape($c), $lote));
                $filas = $this->db->query("SELECT * FROM companies WHERE cif IN ({$lista})")->getResultArray();
                $nuevas = [];
                foreach ($filas as $f) {
                    if (!isset($ids[(int) $f['id']])) {
                        $nuevas[]            = $f;
                        $ids[(int) $f['id']] = true;
                        $extra++;
                    }
                    $cifs[(string) $f['cif']] = true;
                }
                $this->insertar('companies', $nuevas);
                $this->descanso();
            }
            CLI::write('  companies (pedidas por la API): ' . number_format($extra, 0, ',', '.') . ' más');
        }

        // 5) Lo que cuelga de cada empresa
        foreach (self::POR_ID as $tabla => $col) {
            if (!$existe($tabla)) {
                continue;
            }
            $this->crearTabla($tabla);
            $n = 0;
            foreach (array_chunk(array_keys($ids), 400) as $lote) {
                $n += $this->insertar($tabla, $this->db->query(
                    "SELECT * FROM `{$tabla}` WHERE `{$col}` IN (" . implode(',', $lote) . ')'
                )->getResultArray());
                $this->descanso();
            }
            CLI::write("  {$tabla}: " . number_format($n, 0, ',', '.') . ' filas');
        }
        foreach (self::POR_CIF as $tabla => $col) {
            if (!$existe($tabla)) {
                continue;
            }
            $this->crearTabla($tabla);
            $n = 0;
            foreach (array_chunk(array_keys($cifs), 300) as $lote) {
                $lista = implode(',', array_map(fn ($c) => $this->db->escape((string) $c), $lote));
                $n += $this->insertar($tabla, $this->db->query(
                    "SELECT * FROM `{$tabla}` WHERE `{$col}` IN ({$lista})"
                )->getResultArray());
                $this->descanso();
            }
            CLI::write("  {$tabla}: " . number_format($n, 0, ',', '.') . ' filas');
        }

        // 6) (Grupos de holdings: tablas borradas el 10-10-2026; el grupo es ahora company_lei, arriba.)

        gzwrite($this->gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($this->gz);

        CLI::write('Listo en ' . (time() - $t0) . ' s: ' . $ruta . ' (' . number_format(filesize($ruta) / 1048576, 1, ',', '.') . ' MB)', 'green');
    }

    /** Tabla auxiliar `_muestra_totales` con el tamaño aproximado de cada tabla. */
    private function totales(array $tablas): void
    {
        gzwrite($this->gz, "DROP TABLE IF EXISTS `_muestra_totales`;\n"
            . "CREATE TABLE `_muestra_totales` (`tabla` varchar(100) NOT NULL, `filas_aprox` bigint NOT NULL, PRIMARY KEY (`tabla`));\n");
        $filas = $this->db->query(
            'SELECT TABLE_NAME AS tabla, TABLE_ROWS AS filas_aprox FROM information_schema.TABLES WHERE TABLE_SCHEMA = ' . $this->db->escape($this->db->getDatabase())
        )->getResultArray();
        $this->insertar('_muestra_totales', array_map(static fn ($f) => [
            'tabla'       => (string) $f['tabla'],
            'filas_aprox' => (int) $f['filas_aprox'],
        ], $filas));
        gzwrite($this->gz, "\n");
    }

    /**
     * CIF que los clientes han pedido a la API en los últimos 90 días (sin el monitor),
     * con cuántas veces y cuántas dieron 404. Se guarda en `_muestra_cifs_api`.
     *
     * @return array<string, true> CIF en mayúsculas
     */
    private function cifsPedidos(callable $existe): array
    {
        if (!$existe('api_requests')) {
            return [];
        }
        $desde = date('Y-m-d H:i:s', strtotime('-90 days'));
        $acum  = [];
        // Por tramos de 7 días, con el índice de fecha: nunca la tabla entera de una vez
        for ($t = strtotime($desde); $t < time(); $t += 7 * 86400) {
            $a = date('Y-m-d H:i:s', $t);
            $b = date('Y-m-d H:i:s', min(time(), $t + 7 * 86400));
            $filas = $this->db->query(
                "SELECT UPPER(search_term) AS cif, COUNT(*) AS n, SUM(status_code = 404) AS no_encontrada
                   FROM api_requests
                  WHERE created_at >= '{$a}' AND created_at < '{$b}'
                    AND user_id <> " . (int) \App\Filters\ApiKeyFilter::MONITOR_USER_ID . "
                    AND status_code IN (200, 404)
                    AND search_term REGEXP '^([A-Za-z][0-9]{7}[A-Za-z0-9]|[0-9]{8}[A-Za-z])$'
                  GROUP BY UPPER(search_term)"
            )->getResultArray();
            foreach ($filas as $f) {
                $c = (string) $f['cif'];
                $acum[$c]['n']  = ($acum[$c]['n'] ?? 0) + (int) $f['n'];
                $acum[$c]['nf'] = ($acum[$c]['nf'] ?? 0) + (int) $f['no_encontrada'];
            }
            $this->descanso();
        }

        gzwrite($this->gz, "DROP TABLE IF EXISTS `_muestra_cifs_api`;\n"
            . "CREATE TABLE `_muestra_cifs_api` (`cif` varchar(20) NOT NULL, `peticiones` int NOT NULL, `no_encontrada` int NOT NULL, PRIMARY KEY (`cif`));\n");
        $salida = [];
        foreach ($acum as $c => $v) {
            $salida[] = ['cif' => $c, 'peticiones' => $v['n'], 'no_encontrada' => $v['nf']];
        }
        $this->insertar('_muestra_cifs_api', $salida);
        gzwrite($this->gz, "\n");
        CLI::write('  CIF pedidos por la API (90 días): ' . number_format(count($acum), 0, ',', '.'));

        return array_fill_keys(array_keys($acum), true);
    }

    private function crearTabla(string $tabla): void
    {
        $crear = $this->db->query('SHOW CREATE TABLE `' . $tabla . '`')->getRowArray();
        gzwrite($this->gz, "DROP TABLE IF EXISTS `{$tabla}`;\n" . ($crear['Create Table'] ?? '') . ";\n\n");
    }

    /** Copia el resultado de una consulta por lotes. Solo para tablas pequeñas. */
    private function volcar(string $tabla, string $sql): int
    {
        $n = 0;
        for ($desde = 0; ; $desde += 2000) {
            $filas = $this->db->query($sql . " LIMIT {$desde}, 2000")->getResultArray();
            if (empty($filas)) {
                break;
            }
            $n += $this->insertar($tabla, $filas);
            if (count($filas) < 2000) {
                break;
            }
        }
        gzwrite($this->gz, "\n");

        return $n;
    }

    /** Escribe las filas como INSERT, en bloques de 500. */
    private function insertar(string $tabla, array $filas): int
    {
        if (empty($filas)) {
            return 0;
        }
        $lista = '`' . implode('`, `', array_keys($filas[0])) . '`';
        foreach (array_chunk($filas, 500) as $bloque) {
            $valores = [];
            foreach ($bloque as $f) {
                $valores[] = '(' . implode(',', array_map(fn ($v) => $v === null ? 'NULL' : $this->db->escape($v), $f)) . ')';
            }
            gzwrite($this->gz, "INSERT INTO `{$tabla}` ({$lista}) VALUES\n" . implode(",\n", $valores) . ";\n");
        }

        return count($filas);
    }

    private function descanso(): void
    {
        if ($this->pausa > 0) {
            usleep($this->pausa * 1000);
        }
    }
}
