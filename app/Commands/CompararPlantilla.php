<?php

namespace App\Commands;

use App\Models\BormePostsModel;
use App\Models\CompanyModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Compara el texto de IA guardado con el que sale de la plantilla en código (15-10-2026).
 * NO guarda nada en la base: escribe writable/comparar_plantilla.html con las dos versiones
 * lado a lado.
 *
 *   php spark seo:comparar-plantilla B76010529 A28000032 12345   (CIF o id)
 *   php spark seo:comparar-plantilla --azar 20     (fichas al azar con texto del prompt nuevo, desde el 10-10)
 *   php spark seo:comparar-plantilla --cola 20     (fichas al azar de la cola pendiente: aún sin texto)
 */
class CompararPlantilla extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:comparar-plantilla';
    protected $description = 'Enseña el texto de IA guardado y el de la plantilla en código, sin guardar nada.';
    protected $usage       = 'seo:comparar-plantilla [<cif|id> ...] [--azar N] [--cola N]';

    public function run(array $params)
    {
        helper(['seo_dynamic_helper', 'seo_plantilla', 'company', 'ficha_hechos']);
        $db = \Config\Database::connect();

        $claves = [];
        $azar = CLI::getOption('azar');
        $cola = CLI::getOption('cola');
        if ($azar) {
            // Solo textos del prompt nuevo: updated_at lo tocan también otros procesos, así que se
            // descartan además los que tienen el lenguaje del prompt antiguo ("referente", "líder"…).
            $rows = $db->query("SELECT company_id FROM company_enrichment
                WHERE ai_seo_text IS NOT NULL AND ai_seo_text <> '' AND updated_at >= '2026-10-10'
                  AND ai_seo_text NOT REGEXP 'referente|l[ií]der|compromiso|trayectoria|excelencia|innovador'
                ORDER BY RAND() LIMIT " . max(1, (int) $azar))->getResultArray();
            $claves = array_merge($claves, array_column($rows, 'company_id'));
        }
        if ($cola) {
            $rows = $db->query("SELECT company_id FROM seo_generation_queue WHERE status = 'pending' ORDER BY RAND() LIMIT " . max(1, (int) $cola))->getResultArray();
            $claves = array_merge($claves, array_column($rows, 'company_id'));
        }
        foreach ($params as $p) {
            if (is_string($p) && $p !== '' && $p[0] !== '-' && !in_array($p, [(string) $azar, (string) $cola], true)) {
                $claves[] = $p;
            }
        }
        if (!$claves) {
            CLI::error('Indica CIF o id, o --azar N / --cola N.');
            return;
        }

        $model = new CompanyModel();
        $borme = new BormePostsModel();
        $html = [];
        $ms = [];

        foreach ($claves as $clave) {
            $clave = strtoupper(trim((string) $clave));
            $company = ctype_digit($clave) ? $model->getById((int) $clave) : $model->getByCif($clave);
            if (!$company) {
                CLI::error("No encontrada: {$clave}");
                continue;
            }
            $titulo = "{$company['name']} ({$company['cif']}) id {$company['id']}";
            CLI::newLine();
            CLI::write(str_repeat('=', 90), 'cyan');
            CLI::write($titulo, 'cyan');

            $estado = company_estado_registral($company);
            if (!empty($estado['incidencia'])) {
                CLI::write("Estado adverso ({$estado['etiqueta']}): no se le genera texto.", 'yellow');
                continue;
            }
            $posts = $borme->getByCompanyId((int) $company['id']);
            $d = seo_ai_datos($company, $posts);
            if ($d === null) {
                CLI::write('Sin objeto social ni CNAE: no se le genera texto.', 'yellow');
                continue;
            }

            $t0 = microtime(true);
            $r = seo_plantilla_generar($d);
            $ms[] = (microtime(true) - $t0) * 1000;

            CLI::write('--- PLANTILLA', 'green');
            CLI::write('pitch: ' . $r['seo_pitch']);
            CLI::write('tags : ' . implode(' | ', $r['seo_tags']));
            CLI::write($r['seo_text']);
            foreach ($r['faqs'] as $f) {
                CLI::write('P: ' . $f['q']);
                CLI::write('R: ' . $f['a']);
            }
            CLI::write('borme: ' . ($r['borme_summary'] ?? ''));

            $ia = [
                'seo_text'      => (string) ($company['ai_seo_text'] ?? ''),
                'faqs'          => json_decode((string) ($company['ai_faqs'] ?? ''), true) ?: [],
                'seo_tags'      => json_decode((string) ($company['ai_tags'] ?? ''), true) ?: [],
                'seo_pitch'     => (string) ($company['ai_pitch'] ?? ''),
                'borme_summary' => (string) ($company['ai_borme_summary'] ?? ''),
            ];
            // Fecha del texto (company_enrichment), no de la ficha
            $fechaIa = (string) ($db->query('SELECT updated_at FROM company_enrichment WHERE company_id = ?', [(int) $company['id']])->getRow()->updated_at ?? '');
            $html[] = $this->bloque($titulo, $d['objeto'], $ia, $r, $fechaIa);
        }

        if ($html) {
            $ruta = WRITEPATH . 'comparar_plantilla.html';
            file_put_contents($ruta, $this->pagina($html));
            CLI::newLine();
            CLI::write('Comparación lado a lado: ' . $ruta, 'green');
            CLI::write('Tiempo medio de la plantilla: ' . round(array_sum($ms) / max(1, count($ms)), 2) . ' ms por ficha', 'light_gray');
        }
    }

    private function columna(array $r): string
    {
        $e = static fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        if (trim($r['seo_text']) === '') {
            return '<p class="vacio">Sin texto</p>';
        }
        // El texto lleva <strong>: se escapa todo y se devuelven solo esas etiquetas.
        $texto = str_replace(['&lt;strong&gt;', '&lt;/strong&gt;'], ['<strong>', '</strong>'], $e($r['seo_text']));
        $o = '<p class="pitch">' . $e($r['seo_pitch']) . '</p>';
        $o .= '<p class="tags">' . implode('', array_map(static fn ($t) => '<span>' . $e($t) . '</span>', (array) $r['seo_tags'])) . '</p>';
        $o .= '<div class="texto">' . nl2br($texto) . '</div>';
        $o .= '<dl>';
        foreach ((array) $r['faqs'] as $f) {
            $o .= '<dt>' . $e($f['q'] ?? '') . '</dt><dd>' . $e($f['a'] ?? '') . '</dd>';
        }
        $o .= '</dl><p class="borme"><b>BORME:</b> ' . $e($r['borme_summary']) . '</p>';
        $o .= '<p class="cuenta">' . str_word_count(strip_tags($r['seo_text'])) . ' palabras aprox.</p>';
        return $o;
    }

    private function bloque(string $titulo, string $objeto, array $ia, array $pl, string $fechaIa): string
    {
        $e = static fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        return '<section><h2>' . $e($titulo) . '</h2>'
            . ($objeto !== '' ? '<details><summary>Objeto social original</summary><p>' . $e($objeto) . '</p></details>' : '')
            . '<div class="par"><div><h3>IA (guardado' . ($fechaIa !== '' ? ', ' . $e(substr($fechaIa, 0, 10)) : '') . ')</h3>' . $this->columna($ia) . '</div>'
            . '<div><h3>Plantilla (código)</h3>' . $this->columna($pl) . '</div></div></section>';
    }

    private function pagina(array $bloques): string
    {
        return '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>IA vs plantilla</title><style>
body{font:15px/1.55 system-ui,sans-serif;margin:0;padding:24px;background:#f6f7f9;color:#1d2330}
h1{font-size:20px;margin:0 0 16px}section{background:#fff;border:1px solid #e2e5ea;border-radius:8px;padding:16px 20px;margin:0 0 20px}
h2{font-size:16px;margin:0 0 8px}h3{font-size:13px;text-transform:uppercase;letter-spacing:.04em;color:#5b6475;margin:12px 0 8px}
.par{display:grid;grid-template-columns:1fr 1fr;gap:24px}@media(max-width:800px){.par{grid-template-columns:1fr}}
.pitch{font-style:italic;color:#38415a}.tags span{display:inline-block;background:#eef1f6;border-radius:12px;padding:1px 9px;margin:0 4px 4px 0;font-size:13px}
dt{font-weight:600;margin-top:8px}dd{margin:2px 0 0}.borme{font-size:14px}.cuenta,.vacio{color:#8a93a3;font-size:13px}
details{font-size:13px;color:#5b6475}
</style></head><body><h1>Texto de la ficha: IA frente a plantilla en código</h1>' . implode('', $bloques) . '</body></html>';
    }
}
