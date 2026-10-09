<?php

namespace App\Commands;

use App\Models\BormePostsModel;
use App\Models\CompanyModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Prueba del texto de IA de la ficha (09-10-2026). Por defecto NO guarda nada: enseña los
 * datos que recibe la IA, lo que hay guardado ahora y lo que generaría el prompt nuevo.
 *
 *   php spark seo:probar-texto B76010529 A28000032 12345
 *   php spark seo:probar-texto B76010529 --guardar     (sobrescribe el texto de esa ficha)
 */
class ProbarTextoIa extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:probar-texto';
    protected $description = 'Genera el texto de IA de una o varias fichas (CIF o id) y lo enseña sin guardarlo.';
    protected $usage       = 'seo:probar-texto <cif|id> [<cif|id> ...] [--guardar]';

    public function run(array $params)
    {
        helper(['seo_dynamic_helper', 'company']);
        $guardar = CLI::getOption('guardar') !== null || in_array('--guardar', $params, true);
        $claves  = array_values(array_filter($params, static fn ($p) => is_string($p) && $p !== '' && $p[0] !== '-'));

        if (!$claves) {
            CLI::error('Indica uno o varios CIF o id. Ejemplo: php spark seo:probar-texto B76010529');
            return;
        }
        if (empty(env('OPENAI_API_KEY'))) {
            CLI::error('No hay OPENAI_API_KEY en el .env');
            return;
        }

        $model = new CompanyModel();
        $borme = new BormePostsModel();

        foreach ($claves as $clave) {
            $clave   = strtoupper(trim($clave));
            $company = ctype_digit($clave) ? $model->getById((int) $clave) : $model->getByCif($clave);
            CLI::newLine();
            CLI::write(str_repeat('=', 90), 'cyan');
            if (!$company) {
                CLI::error("No encontrada: {$clave}");
                continue;
            }
            CLI::write("{$company['name']} ({$company['cif']}) id {$company['id']}", 'cyan');

            $estado = company_estado_registral($company);
            if (!empty($estado['incidencia'])) {
                CLI::write("Estado adverso ({$estado['etiqueta']}): la cola no le genera texto.", 'yellow');
                continue;
            }

            $posts = $borme->getByCompanyId((int) $company['id']);
            $datos = seo_ai_datos($company, $posts);
            if ($datos === null) {
                CLI::write('Sin objeto social ni CNAE: la cola no le genera texto.', 'yellow');
                continue;
            }

            CLI::write('--- Datos que recibe la IA', 'light_gray');
            foreach ($datos as $k => $v) {
                if ($k === 'actos') {
                    foreach ($v as $a) {
                        CLI::write("    {$a}");
                    }
                    continue;
                }
                CLI::write(str_pad($k, 12) . ': ' . (is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v));
            }

            if (!empty($company['ai_seo_text'])) {
                CLI::write('--- Texto guardado AHORA', 'light_gray');
                CLI::write('pitch: ' . ($company['ai_pitch'] ?? ''));
                CLI::write('tags : ' . ($company['ai_tags'] ?? ''));
                CLI::write(strip_tags((string) $company['ai_seo_text'], '<strong>'));
                CLI::write('faqs : ' . ($company['ai_faqs'] ?? ''));
                CLI::write('borme: ' . ($company['ai_borme_summary'] ?? ''));
            }

            try {
                $t0 = microtime(true);
                $r  = seo_ai_generar($datos);
                $s  = round(microtime(true) - $t0, 1);
            } catch (\Throwable $e) {
                CLI::error('RECHAZADO por la validación: ' . $e->getMessage() . ' (la cola lo reintentaría)');
                continue;
            }

            CLI::write("--- Texto NUEVO ({$s} s)", 'green');
            CLI::write('pitch: ' . ($r['seo_pitch'] ?? ''));
            CLI::write('tags : ' . implode(' | ', $r['seo_tags']));
            CLI::newLine();
            CLI::write($r['seo_text']);
            CLI::newLine();
            foreach ($r['faqs'] as $f) {
                CLI::write('P: ' . $f['q']);
                CLI::write('R: ' . $f['a']);
            }
            CLI::newLine();
            CLI::write('borme: ' . ($r['borme_summary'] ?? '(vacío)'));
            CLI::write('palabras del texto: ' . count(preg_split('/\s+/u', trim(strip_tags($r['seo_text'])))), 'light_gray');

            if ($guardar) {
                seo_ai_guardar((int) $company['id'], $r);
                CLI::write('GUARDADO en company_enrichment.', 'yellow');
            }
            sleep(1);
        }
    }
}
