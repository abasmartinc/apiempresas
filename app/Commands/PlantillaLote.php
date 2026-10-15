<?php

namespace App\Commands;

use App\Models\BormePostsModel;
use App\Models\CompanyModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Vacía la cola seo_generation_queue con la plantilla en código, sin IA (15-10-2026).
 * Por defecto NO escribe: cuenta lo que haría. Con --guardar guarda en company_enrichment
 * (seo_plantilla_guardar) y saca la ficha de la cola.
 *
 *   php spark seo:plantilla-lote                       prueba con 1.000 (no escribe)
 *   php spark seo:plantilla-lote --limite 20000 --guardar
 *   php spark seo:plantilla-lote --mitad --guardar     solo crc32(CIF, o 'ID'+id sin CIF) par; la otra mitad sigue
 *                                                      para la IA (prueba A/B en Search Console)
 *
 * No toca las filas con regenerar = 1 (fichas que ya tienen texto de IA): esas siguen en la cola
 * de IA mientras dura la prueba de regeneración.
 * Puede ir a la vez que seo:process-queue: solo coge filas 'pending' y se las queda borrándolas
 * de la cola antes de generar.
 */
class PlantillaLote extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:plantilla-lote';
    protected $description = 'Genera con la plantilla en código (sin IA) el texto de las fichas de la cola SEO.';
    protected $usage       = 'seo:plantilla-lote [--limite N] [--guardar] [--mitad] [--rehacer]';

    public function run(array $params)
    {
        $limite  = (int) (CLI::getOption('limite') ?: 1000);
        $guardar = CLI::getOption('guardar') !== null || in_array('--guardar', $params, true);
        $mitad   = CLI::getOption('mitad') !== null || in_array('--mitad', $params, true);

        $lock = @fopen(WRITEPATH . 'seo_plantilla_lote.lock', 'c+');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            CLI::write('Ya hay otro seo:plantilla-lote en marcha.', 'yellow');
            return;
        }

        helper(['seo_dynamic_helper', 'seo_plantilla', 'company', 'ficha_hechos']);
        $db = \Config\Database::connect();
        $model = new CompanyModel();
        $borme = new BormePostsModel();

        // --rehacer (15-10-2026): vuelve a generar los textos que ya hizo la plantilla
        // (ai_origen = 'plantilla') con la versión actual del helper. Solo esos: nunca toca textos de IA.
        if (CLI::getOption('rehacer') !== null || in_array('--rehacer', $params, true)) {
            $this->rehacer($db, $model, $borme, $limite, $guardar);
            flock($lock, LOCK_UN);
            fclose($lock);
            return;
        }

        CLI::write(($guardar ? 'GUARDANDO' : 'PRUEBA (no escribe; --guardar para guardar)') . " · límite {$limite}" . ($mitad ? ' · solo la mitad A/B' : ''), $guardar ? 'yellow' : 'cyan');

        $c = ['generadas' => 0, 'ya_tenia' => 0, 'omitidas' => 0, 'otra_mitad' => 0, 'errores' => 0, 'cogidas_por_otro' => 0];
        $t0 = microtime(true);
        $ultimo = 0;
        $vistos = 0;

        while ($c['generadas'] < $limite) {
            $filas = $db->query(
                "SELECT company_id FROM seo_generation_queue
                  WHERE status = 'pending' AND attempts < 3 AND (regenerar = 0 OR regenerar IS NULL) AND company_id > ?
                  ORDER BY company_id LIMIT 500",
                [$ultimo]
            )->getResultArray();
            if (!$filas) {
                break;
            }

            foreach ($filas as $f) {
                $id = (int) $f['company_id'];
                $ultimo = $id;
                $vistos++;
                if ($c['generadas'] >= $limite) {
                    break 2;
                }

                $company = $model->getById($id);
                if (!$company) {
                    $this->sacar($db, $id, $guardar);
                    $c['omitidas']++;
                    continue;
                }
                if ($mitad) {
                    // Sin CIF (muchas recién constituidas) se reparte por id, como ficha_grupo_control():
                    // si no, todas las nuevas sin CIF iban a la IA y la prueba quedaba descompensada.
                    $clave = strtoupper(trim((string) ($company['cif'] ?? ''))) ?: ('ID' . $id);
                    if (crc32($clave) % 2 !== 0) {
                        $c['otra_mitad']++;
                        continue;
                    }
                }
                if (!empty($company['ai_seo_text'])) {
                    $this->sacar($db, $id, $guardar);
                    $c['ya_tenia']++;
                    continue;
                }
                $estado = company_estado_registral($company);
                $d = empty($estado['incidencia']) ? seo_ai_datos($company, $borme->getByCompanyId($id)) : null;
                if ($d === null) {
                    $this->sacar($db, $id, $guardar);
                    $c['omitidas']++;
                    continue;
                }

                try {
                    $r = seo_plantilla_generar($d);
                    if (mb_strlen(strip_tags($r['seo_text']), 'UTF-8') < 60 || str_contains($r['seo_text'], '{')) {
                        throw new \RuntimeException('texto demasiado corto o con restos de plantilla');
                    }
                } catch (\Throwable $e) {
                    $c['errores']++;
                    log_message('error', "[seo:plantilla-lote] ID {$id}: " . $e->getMessage());
                    continue;   // se queda en la cola para la IA
                }

                if ($guardar) {
                    // Se la queda borrándola de la cola: si seo:process-queue la ha cogido ya, no está 'pending'.
                    $db->query("DELETE FROM seo_generation_queue WHERE company_id = ? AND status = 'pending'", [$id]);
                    if ($db->affectedRows() !== 1) {
                        $c['cogidas_por_otro']++;
                        continue;
                    }
                    seo_plantilla_guardar($id, $r);
                }
                $c['generadas']++;
                if ($c['generadas'] % 500 === 0) {
                    CLI::write("  {$c['generadas']} generadas · " . round(microtime(true) - $t0) . ' s', 'light_gray');
                }
            }
        }

        $s = round(microtime(true) - $t0, 1);
        CLI::newLine();
        foreach ($c as $k => $n) {
            CLI::write(str_pad(str_replace('_', ' ', $k), 18) . number_format($n, 0, ',', '.'));
        }
        CLI::write("Revisadas {$vistos} filas de la cola en {$s} s" . ($c['generadas'] ? ' (' . round($s * 1000 / max(1, $c['generadas'])) . ' ms por ficha generada)' : '') . '.', 'green');

        flock($lock, LOCK_UN);
        fclose($lock);
    }

    /** Regenera los textos de plantilla ya guardados; guarda solo si el texto cambia. */
    private function rehacer($db, CompanyModel $model, BormePostsModel $borme, int $limite, bool $guardar): void
    {
        if (!$db->fieldExists('ai_origen', 'company_enrichment')) {
            CLI::error('No existe company_enrichment.ai_origen.');
            return;
        }
        CLI::write(($guardar ? 'REHACIENDO Y GUARDANDO' : 'PRUEBA DE REHACER (no escribe; --guardar para guardar)') . " · límite {$limite}", $guardar ? 'yellow' : 'cyan');
        $c = ['revisadas' => 0, 'cambian' => 0, 'iguales' => 0, 'omitidas' => 0, 'errores' => 0];
        $ejemplos = [];
        $t0 = microtime(true);
        $ultimo = 0;
        while ($c['revisadas'] < $limite) {
            $filas = $db->query(
                "SELECT company_id, ai_seo_text FROM company_enrichment WHERE ai_origen = 'plantilla' AND company_id > ? ORDER BY company_id LIMIT 500",
                [$ultimo]
            )->getResultArray();
            if (!$filas) {
                break;
            }
            foreach ($filas as $f) {
                if ($c['revisadas'] >= $limite) {
                    break 2;
                }
                $id = (int) $f['company_id'];
                $ultimo = $id;
                $c['revisadas']++;
                $company = $model->getById($id);
                $d = $company ? seo_ai_datos($company, $borme->getByCompanyId($id)) : null;
                if ($d === null) {
                    $c['omitidas']++;   // se deja el texto que tiene
                    continue;
                }
                try {
                    $r = seo_plantilla_generar($d);
                } catch (\Throwable $e) {
                    $c['errores']++;
                    continue;
                }
                if ($r['seo_text'] === (string) $f['ai_seo_text']) {
                    $c['iguales']++;
                    continue;
                }
                $c['cambian']++;
                if (count($ejemplos) < 8) {
                    $ejemplos[] = "  [{$id}] ANTES: " . str_replace("\n\n", ' ¶ ', strip_tags((string) $f['ai_seo_text'])) . "\n        AHORA: " . str_replace("\n\n", ' ¶ ', strip_tags($r['seo_text']));
                }
                if ($guardar) {
                    seo_plantilla_guardar($id, $r);
                }
                if ($c['revisadas'] % 1000 === 0) {
                    CLI::write("  {$c['revisadas']} revisadas · {$c['cambian']} cambian · " . round(microtime(true) - $t0) . ' s', 'light_gray');
                }
            }
        }
        CLI::newLine();
        foreach ($c as $k => $n) {
            CLI::write(str_pad($k, 12) . number_format($n, 0, ',', '.'));
        }
        if ($ejemplos) {
            CLI::newLine();
            CLI::write('Ejemplos de cambio:', 'cyan');
            CLI::write(implode("\n", $ejemplos));
        }
        CLI::write('Tiempo: ' . round(microtime(true) - $t0, 1) . ' s', 'green');
    }

    /** Fuera de la cola (como hace seo:process-queue con las omitidas). En prueba no hace nada. */
    private function sacar($db, int $id, bool $guardar): void
    {
        if ($guardar) {
            $db->query("DELETE FROM seo_generation_queue WHERE company_id = ? AND status = 'pending'", [$id]);
        }
    }
}
