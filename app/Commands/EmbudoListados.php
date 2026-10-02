<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Embudo de compra de listados: cuánta gente llega a cada paso.
 *
 *   php spark listados:embudo        (últimos 30 días)
 *   php spark listados:embudo 7      (últimos 7 días)
 *
 * Los pasos salen de tracking_events (eventos que mandan las páginas) y las compras, de
 * writable/listados/*.json (una por pago cobrado: no depende de que el navegador del
 * cliente cargue la página de éxito). Ejecutar en el SERVIDOR.
 *
 * Pasos:
 *   directory_index_buy_click   clic en "CSV · N €" de una fila del índice
 *   map_checkout_click          clic en "Descargar CSV" del mapa
 *   directory_summary_view      ve el resumen de compra
 *   directory_phone_offer_click pulsa "solo las que tienen teléfono"
 *   directory_quote_request     pide el presupuesto por correo
 *   directory_checkout_click    pulsa "Pagar"
 *   directory_excel_purchase    llega a la página de éxito
 *   directory_excel_download    descarga el CSV   ·   directory_xlsx_download: el Excel
 */
class EmbudoListados extends BaseCommand
{
    protected $group       = 'Informes';
    protected $name        = 'listados:embudo';
    protected $description = 'Embudo de compra de listados (visitas al resumen, pagos iniciados, compras).';
    protected $usage       = 'listados:embudo [días]';

    private const PASOS = [
        'directory_index_buy_click'   => 'Clic en "CSV" del índice',
        'map_checkout_click'          => 'Clic en "Descargar" del mapa',
        'directory_summary_view'      => 'Ve el resumen de compra',
        'directory_phone_offer_click' => 'Pulsa "solo con teléfono"',
        'directory_quote_request'     => 'Pide presupuesto por correo',
        'directory_checkout_click'    => 'Pulsa "Pagar"',
        'directory_excel_purchase'    => 'Página de éxito',
        'directory_excel_download'    => 'Descarga el CSV',
        'directory_xlsx_download'     => 'Descarga el Excel',
    ];

    public function run(array $params)
    {
        $dias  = max(1, min(365, (int) ($params[0] ?? 30)));
        $desde = date('Y-m-d 00:00:00', strtotime("-{$dias} days"));

        $filas = \Config\Database::connect()->table('tracking_events')
            ->select('event_name, anonymous_id, metadata')
            ->whereIn('event_name', array_keys(self::PASOS))
            ->where('created_at >=', $desde)
            ->get()->getResultArray();

        $eventos = $personas = $porProvincia = [];
        foreach ($filas as $f) {
            $e = $f['event_name'];
            $eventos[$e] = ($eventos[$e] ?? 0) + 1;
            $personas[$e][(string) $f['anonymous_id']] = true;

            if (in_array($e, ['directory_summary_view', 'directory_checkout_click'], true)) {
                $m = json_decode((string) $f['metadata'], true);
                $p = is_array($m) ? trim((string) ($m['provincia'] ?? '')) : '';
                if ($e === 'directory_summary_view' && $p !== '') {
                    $porProvincia[$p]['vistas'][(string) $f['anonymous_id']] = true;
                }
            }
        }

        // Compras cobradas (una por sesión de pago)
        $compras = $ingresos = $sinDescargar = $conFallos = 0;
        $comprasProvincia = $pendientes = [];
        foreach (glob(WRITEPATH . 'listados/*.json') ?: [] as $archivo) {
            $d = json_decode((string) file_get_contents($archivo), true);
            if (!is_array($d) || empty($d['pagado_en']) || $d['pagado_en'] < $desde) {
                continue;
            }
            $compras++;
            $ingresos += (float) ($d['importe'] ?? 0);
            // Compras que no han llegado a descargarse enteras (seguimiento desde el 02-10-2026)
            if (isset($d['descargas']) || isset($d['descargas_fallidas'])) {
                if ((int) ($d['descargas'] ?? 0) === 0) {
                    $sinDescargar++;
                    $pendientes[] = [(string) ($d['pagado_en'] ?? ''), (string) ($d['email'] ?? ''), (string) ($d['session_id'] ?? ''), (int) ($d['descargas_fallidas'] ?? 0)];
                }
                if ((int) ($d['descargas_fallidas'] ?? 0) > 0) {
                    $conFallos++;
                }
            }
            $p = trim((string) ($d['contexto']['provincia'] ?? '')) ?: '(sin provincia)';
            $comprasProvincia[$p] = ($comprasProvincia[$p] ?? 0) + 1;
        }

        CLI::write("Embudo de listados · últimos {$dias} días (desde " . substr($desde, 0, 10) . ')', 'yellow');
        $tabla = [];
        $base  = count($personas['directory_summary_view'] ?? []);
        foreach (self::PASOS as $e => $nombre) {
            $n = count($personas[$e] ?? []);
            $tabla[] = [$nombre, $eventos[$e] ?? 0, $n, $base > 0 ? round($n * 100 / $base, 1) . ' %' : '—'];
        }
        $tabla[] = ['COMPRAS COBRADAS', $compras, '', $base > 0 ? round($compras * 100 / $base, 1) . ' %' : '—'];
        CLI::table($tabla, ['Paso', 'Eventos', 'Personas', '% sobre quien ve el resumen']);
        CLI::write('Ingresos (con IVA): ' . number_format($ingresos, 2, ',', '.') . ' €');
        if ($sinDescargar > 0 || $conFallos > 0) {
            CLI::write("Compras con intentos de descarga y ninguna completa: {$sinDescargar} · con algún intento fallido o cortado: {$conFallos}", $sinDescargar > 0 ? 'red' : 'yellow');
            if ($pendientes) {
                CLI::table($pendientes, ['Pagado', 'Cliente', 'Sesión de Stripe', 'Intentos fallidos']);
            }
        }

        if ($porProvincia || $comprasProvincia) {
            CLI::newLine();
            CLI::write('Por provincia (personas que ven el resumen → compras)', 'yellow');
            $t = [];
            foreach (array_unique(array_merge(array_keys($porProvincia), array_keys($comprasProvincia))) as $p) {
                $t[] = [$p, count($porProvincia[$p]['vistas'] ?? []), $comprasProvincia[$p] ?? 0];
            }
            usort($t, static fn ($a, $b) => [$b[2], $b[1]] <=> [$a[2], $a[1]]);
            CLI::table(array_slice($t, 0, 25), ['Provincia', 'Ven el resumen', 'Compras']);
        }

        if ($base === 0) {
            CLI::write('Aún no hay visitas al resumen registradas: los eventos empiezan a guardarse al subir este cambio.', 'light_gray');
        }
    }
}
