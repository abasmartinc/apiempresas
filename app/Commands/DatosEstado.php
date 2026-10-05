<?php

namespace App\Commands;

use App\Libraries\EstadoBorme;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Corrige companies.estado a partir de los anuncios del BORME.
 *
 *   php spark datos:estado                     simulación (no escribe nada en la base)
 *   php spark datos:estado --cif A39000013     una sola empresa, con explicación
 *   php spark datos:estado --aplicar           escribe los cambios (guarda antes el valor anterior)
 *   php spark datos:estado --deshacer          devuelve los valores anteriores
 *
 * Trabaja por lotes de fichas con una pausa entre lotes. Se puede cortar y seguir con --desde.
 */
class DatosEstado extends BaseCommand
{
    protected $group       = 'Datos';
    protected $name        = 'datos:estado';
    protected $description = 'Corrige el estado de las empresas a partir del BORME (simula por defecto).';
    protected $usage       = 'datos:estado [--aplicar] [--deshacer] [--cif CIF] [--desde ID] [--hasta ID] [--lote 2000] [--pausa 200]';

    private const RESPALDO = '_correccion_estado';

    public function run(array $params)
    {
        $db      = \Config\Database::connect();
        $aplicar = CLI::getOption('aplicar') !== null;
        $lote    = max(100, (int) (CLI::getOption('lote') ?? 2000));
        $pausa   = max(0, (int) (CLI::getOption('pausa') ?? 200));
        $hoy     = date('Y-m-d');

        if (CLI::getOption('deshacer') !== null) {
            return $this->deshacer($db);
        }

        if ($cif = CLI::getOption('cif')) {
            return $this->unaEmpresa($db, strtoupper(trim((string) $cif)), $hoy);
        }

        $max   = (int) $db->query('SELECT MAX(id) m FROM companies')->getRow()->m;
        $desde = (int) (CLI::getOption('desde') ?? 1);
        $hasta = (int) (CLI::getOption('hasta') ?? $max);

        $dir = WRITEPATH . 'contexto/';
        if (! is_dir($dir)) { mkdir($dir, 0775, true); }
        $sello = date('Ymd_His');
        $csv   = fopen($dir . "estado_cambios_{$sello}.csv", 'w');
        $rev   = fopen($dir . "estado_revisar_{$sello}.csv", 'w');
        fwrite($csv, "\xEF\xBB\xBF"); fwrite($rev, "\xEF\xBB\xBF");
        $cab = ['id', 'cif', 'nombre', 'estado_antes', 'fecha_antes', 'estado_nuevo', 'fecha_nueva', 'motivo'];
        fputcsv($csv, $cab, ';'); fputcsv($rev, $cab, ';');

        if ($aplicar) {
            $db->query('CREATE TABLE IF NOT EXISTS ' . self::RESPALDO . ' (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                company_id INT UNSIGNED NOT NULL,
                estado_antes VARCHAR(50) NULL, fecha_antes DATE NULL,
                estado_nuevo VARCHAR(50) NULL, fecha_nueva DATE NULL,
                motivo VARCHAR(200) NULL, tanda VARCHAR(20) NOT NULL,
                creado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY (company_id), KEY (tanda)) DEFAULT CHARSET=utf8mb4');
        }

        CLI::write(($aplicar ? 'APLICANDO' : 'SIMULACIÓN') . " · fichas $desde a $hasta · lotes de $lote", $aplicar ? 'yellow' : 'green');

        $resumen = []; $nCambios = 0; $nRevisar = 0; $vistas = 0;

        for ($a = $desde; $a <= $hasta; $a += $lote) {
            $b = min($hasta, $a + $lote - 1);

            // 1) Fichas con estado distinto de activa.
            $fichas = [];
            $q = $db->query("SELECT id, cif, company_name, estado, estado_fecha FROM companies
                             WHERE id BETWEEN ? AND ? AND estado IS NOT NULL AND estado NOT IN ('ACTIVA','Activa')", [$a, $b]);
            foreach ($q->getResultArray() as $r) { $fichas[(int) $r['id']] = $r; }

            // 2) Fichas con algún anuncio de extinción, disolución o concurso.
            $q = $db->query("SELECT DISTINCT company_id FROM borme_posts
                             WHERE company_id BETWEEN ? AND ?
                               AND (description LIKE '%xtinci%' OR description LIKE '%isoluci%' OR description LIKE '%oncurs%')", [$a, $b]);
            $faltan = [];
            foreach ($q->getResultArray() as $r) {
                $id = (int) $r['company_id'];
                if (! isset($fichas[$id])) { $faltan[] = $id; }
            }
            if ($faltan) {
                $q = $db->query('SELECT id, cif, company_name, estado, estado_fecha FROM companies WHERE id IN (' . implode(',', $faltan) . ')');
                foreach ($q->getResultArray() as $r) { $fichas[(int) $r['id']] = $r; }
            }

            if ($fichas) {
                // 3) Sus anuncios, en orden.
                $actos = [];
                foreach (array_chunk(array_keys($fichas), 500) as $trozo) {
                    $q = $db->query('SELECT company_id, borme_date, company_name, description FROM borme_posts
                                     WHERE company_id IN (' . implode(',', $trozo) . ') ORDER BY company_id, borme_date, id');
                    foreach ($q->getResultArray() as $r) {
                        $actos[(int) $r['company_id']][] = [$r['borme_date'], (string) $r['company_name'], $this->limpio((string) $r['description'])];
                    }
                    $q->freeResult();
                }

                $escribir = [];
                foreach ($fichas as $id => $f) {
                    $vistas++;
                    $d = EstadoBorme::deducir((string) $f['company_name'], $actos[$id] ?? [], $hoy);
                    $c = EstadoBorme::decidir($f['estado'], $f['estado_fecha'], $d, $hoy);
                    if ($c === null) { continue; }

                    $fila    = [$id, $f['cif'], $f['company_name'], $f['estado'], $f['estado_fecha'], $c['estado'], $c['fecha'], $c['motivo']];
                    $revisar = str_starts_with($c['motivo'], 'REVISAR');
                    $clave   = EstadoBorme::grupo($f['estado']) . ' -> ' . ($revisar ? 'REVISAR' : $c['estado']);
                    $resumen[$clave] = ($resumen[$clave] ?? 0) + 1;

                    if ($revisar) { fputcsv($rev, $fila, ';'); $nRevisar++; continue; }
                    fputcsv($csv, $fila, ';'); $nCambios++;
                    if ($aplicar) { $escribir[] = [$id, $f, $c]; }
                }

                if ($escribir) {
                    $db->transStart();
                    foreach ($escribir as [$id, $f, $c]) {
                        $db->query('INSERT INTO ' . self::RESPALDO . ' (company_id, estado_antes, fecha_antes, estado_nuevo, fecha_nueva, motivo, tanda) VALUES (?,?,?,?,?,?,?)',
                            [$id, $f['estado'], $f['estado_fecha'], $c['estado'], $c['fecha'], mb_substr($c['motivo'], 0, 200), $sello]);
                        // Solo si la ficha sigue como la leímos.
                        $db->query('UPDATE companies SET estado = ?, estado_fecha = ? WHERE id = ? AND estado <=> ?',
                            [$c['estado'], $c['fecha'], $id, $f['estado']]);
                    }
                    $db->transComplete();
                }
            }

            if (($a - $desde) % ($lote * 25) === 0) {
                CLI::write(sprintf('  hasta la ficha %d (%.1f %%) · revisadas %d · cambios %d · a revisar %d',
                    $b, 100 * ($b - $desde + 1) / max(1, $hasta - $desde + 1), $vistas, $nCambios, $nRevisar));
            }
            if ($pausa) { usleep($pausa * 1000); }
        }

        fclose($csv); fclose($rev);
        arsort($resumen);
        CLI::newLine();
        CLI::write('Resumen (estado actual -> nuevo):', 'green');
        foreach ($resumen as $k => $n) { CLI::write(sprintf('  %7d  %s', $n, $k)); }
        CLI::newLine();
        CLI::write("Fichas revisadas: $vistas · cambios: $nCambios · para revisar a mano: $nRevisar");
        CLI::write('Listados en writable/contexto/estado_cambios_' . $sello . '.csv y estado_revisar_' . $sello . '.csv');
        if ($aplicar) {
            CLI::write('Cambios escritos. Tanda ' . $sello . '. Para deshacer: php spark datos:estado --deshacer', 'yellow');
        } else {
            CLI::write('No se ha escrito nada. Para aplicar: php spark datos:estado --aplicar', 'yellow');
        }
    }

    private function limpio(string $d): string
    {
        return trim(preg_replace('/\s+/u', ' ', $d) ?? $d);
    }

    private function unaEmpresa($db, string $cif, string $hoy)
    {
        $f = $db->query('SELECT id, cif, company_name, estado, estado_fecha FROM companies WHERE cif = ? LIMIT 1', [$cif])->getRowArray();
        if (! $f) { CLI::error('No existe ese CIF.'); return; }
        $actos = [];
        $q = $db->query('SELECT borme_date, company_name, description FROM borme_posts WHERE company_id = ? ORDER BY borme_date, id', [$f['id']]);
        foreach ($q->getResultArray() as $r) { $actos[] = [$r['borme_date'], (string) $r['company_name'], $this->limpio((string) $r['description'])]; }
        $d = EstadoBorme::deducir((string) $f['company_name'], $actos, $hoy);
        $c = EstadoBorme::decidir($f['estado'], $f['estado_fecha'], $d, $hoy);
        CLI::write($f['company_name'] . ' (' . $f['cif'] . ')');
        CLI::write('  Estado actual: ' . ($f['estado'] ?? 'NULL') . ' ' . ($f['estado_fecha'] ?? ''));
        CLI::write('  Anuncios: ' . count($actos) . ' · hojas registrales distintas: ' . $d['entidades']);
        CLI::write('  Según el BORME: ' . ($d['estado'] ?? 'sin estado adverso') . ' ' . ($d['fecha'] ?? '')
            . ' · último acto de empresa viva: ' . ($d['ultimo_vivo'] ?? '-'));
        CLI::write('  Decisión: ' . ($c ? ($c['estado'] . ' ' . ($c['fecha'] ?? '') . ' — ' . $c['motivo']) : 'no se toca'), 'green');
    }

    private function deshacer($db)
    {
        if (! $db->tableExists(self::RESPALDO)) { CLI::error('No hay nada que deshacer.'); return; }
        $tanda = CLI::getOption('tanda') ?: ($db->query('SELECT MAX(tanda) t FROM ' . self::RESPALDO)->getRow()->t ?? null);
        if (! $tanda) { CLI::error('No hay nada que deshacer.'); return; }
        $n = 0; $ultimo = 0;
        while (true) {
            $filas = $db->query('SELECT id, company_id, estado_antes, fecha_antes, estado_nuevo FROM ' . self::RESPALDO . ' WHERE tanda = ? AND id > ? ORDER BY id LIMIT 1000', [$tanda, $ultimo])->getResultArray();
            if (! $filas) { break; }
            $db->transStart();
            foreach ($filas as $r) {
                $db->query('UPDATE companies SET estado = ?, estado_fecha = ? WHERE id = ? AND estado <=> ?',
                    [$r['estado_antes'], $r['fecha_antes'], $r['company_id'], $r['estado_nuevo']]);
                $n += $db->affectedRows(); $ultimo = (int) $r['id'];
            }
            $db->transComplete();
            usleep(200000);
        }
        CLI::write("Tanda $tanda deshecha: $n fichas devueltas a su valor anterior.", 'green');
    }
}
