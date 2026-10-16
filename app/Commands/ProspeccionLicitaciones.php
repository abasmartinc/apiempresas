<?php

namespace App\Commands;

use App\Libraries\LicitacionesPlacsp;
use App\Libraries\Prospeccion;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Carga las licitaciones de informática de la Plataforma de Contratación en
 * prospect_tenders (pestaña "Licitaciones" de /admin/prospeccion).
 *
 *   php spark prospeccion:licitaciones                 últimos 3 días de cambios (para la tarea diaria)
 *   php spark prospeccion:licitaciones --dias 30       primera carga: lo publicado o cambiado en 30 días
 *   php spark prospeccion:licitaciones --feed placsp   solo un feed (placsp | agregadas | todas)
 *   php spark prospeccion:licitaciones --cpv 72,48     otros CPV (por el principio del código)
 *   php spark prospeccion:licitaciones --prueba        lee y cuenta, sin escribir en la base
 *   php spark prospeccion:licitaciones --fichero x.atom   analiza un fichero ya descargado (sin seguir páginas)
 *   php spark prospeccion:licitaciones --max-paginas 50   tope de páginas por feed (por defecto 400)
 *   php spark prospeccion:licitaciones --solo-evaluar     solo evalúa con IA las licitaciones en plazo pendientes
 *   php spark prospeccion:licitaciones --sin-ia           carga sin evaluar con IA
 *
 * Después de cargar, evalúa con IA (OpenAiService) las licitaciones en plazo que aún no lo están:
 * si son desarrollo y cuánto encajan con tu perfil (app/Config/Prospeccion.php).
 *
 * Solo escribe en prospect_tenders. No toca company_contracts ni ninguna otra tabla.
 */
class ProspeccionLicitaciones extends BaseCommand
{
    protected $group       = 'Prospeccion';
    protected $name        = 'prospeccion:licitaciones';
    protected $description = 'Licitaciones abiertas de informática (PLACSP) para /admin/prospeccion';
    protected $usage       = 'prospeccion:licitaciones [--dias 3] [--feed todas] [--cpv 72,48,80533,79342] [--prueba] [--fichero f.atom] [--max-paginas 400] [--solo-evaluar] [--sin-ia]';

    public function run(array $params)
    {
        $dias = max(1, (int) (CLI::getOption('dias') ?? 3));
        $feed = (string) (CLI::getOption('feed') ?? 'todas');
        $cpv = CLI::getOption('cpv') ? explode(',', (string) CLI::getOption('cpv')) : null;
        $prueba = CLI::getOption('prueba') !== null;
        $fichero = CLI::getOption('fichero');
        $maxPaginas = max(1, (int) (CLI::getOption('max-paginas') ?? 400));

        @ini_set('memory_limit', '1024M');
        @set_time_limit(0);

        Prospeccion::asegurarTablas();   // solo crea las tablas si faltan (también en --prueba, para poder comparar)
        $lp = new LicitacionesPlacsp($cpv);

        if (CLI::getOption('solo-evaluar') !== null) {
            $this->evaluar();
            return;
        }

        if ($fichero) {
            if (!is_file($fichero)) {
                CLI::error('No existe ' . $fichero);
                return;
            }
            $a = $lp->analizar((string) file_get_contents($fichero), $feed === 'agregadas' ? 'agregadas' : 'placsp');
            $g = $lp->guardar($a['entradas'], $prueba);
            $b = $lp->marcarBorradas($a['borradas'], $prueba);
            CLI::write(sprintf('%s: %d entradas, %d de informática (%d nuevas, %d actualizadas, %d sin cambios), %d retiradas.',
                basename($fichero), $a['leidas'], count($a['entradas']), $g['nuevas'], $g['actualizadas'], $g['iguales'], $b), 'green');
            return;
        }

        $feeds = $feed === 'todas' ? LicitacionesPlacsp::FEEDS : array_intersect_key(LicitacionesPlacsp::FEEDS, [$feed => true]);
        if (!$feeds) {
            CLI::error('Feed desconocido: ' . $feed . ' (placsp | agregadas | todas)');
            return;
        }

        $desde = date('Y-m-d H:i:s', strtotime("-{$dias} days"));
        CLI::write("Cambios desde {$desde}" . ($prueba ? ' (PRUEBA: no se escribe nada)' : ''), 'cyan');

        $total = ['leidas' => 0, 'interesan' => 0, 'nuevas' => 0, 'actualizadas' => 0, 'iguales' => 0, 'borradas' => 0];
        foreach ($feeds as $nombre => $url) {
            $pag = 0;
            while ($url && $pag < $maxPaginas) {
                $pag++;
                try {
                    $xml = $lp->descargar($url);
                    $a = $lp->analizar($xml, $nombre);
                } catch (\Throwable $e) {
                    CLI::error("[{$nombre}] página {$pag}: " . $e->getMessage());
                    break;
                }
                unset($xml);
                $g = $lp->guardar($a['entradas'], $prueba);
                $b = $lp->marcarBorradas($a['borradas'], $prueba);

                $total['leidas'] += $a['leidas'];
                $total['interesan'] += count($a['entradas']);
                $total['borradas'] += $b;
                foreach ($g as $k => $v) {
                    $total[$k] += $v;
                }
                CLI::write(sprintf('[%s] pág. %d: %d entradas hasta %s · %d de informática (+%d nuevas, %d actualizadas)',
                    $nombre, $pag, $a['leidas'], $a['mas_antigua'] ?? '?', count($a['entradas']), $g['nuevas'], $g['actualizadas']));

                if ($a['mas_antigua'] !== null && $a['mas_antigua'] < $desde) {
                    break;
                }
                $url = $a['siguiente'];
                usleep(300000); // sin prisa con el servidor público
            }
            if ($pag >= $maxPaginas) {
                CLI::write("[{$nombre}] parado en el tope de {$maxPaginas} páginas.", 'yellow');
            }
        }

        CLI::write(sprintf('Hecho: %d entradas leídas, %d de informática: %d nuevas, %d actualizadas, %d sin cambios; %d retiradas del feed.',
            $total['leidas'], $total['interesan'], $total['nuevas'], $total['actualizadas'], $total['iguales'], $total['borradas']), 'green');

        if (!$prueba && CLI::getOption('sin-ia') === null) {
            $this->evaluar();
        }
    }

    /** Evalúa con IA las licitaciones en plazo que aún no lo están (ver Prospeccion::evaluarLicitacionesIa) */
    private function evaluar(): void
    {
        $lib = new Prospeccion();
        $pend = $lib->pendientesIa();
        if ($pend === 0) {
            CLI::write('IA: no hay licitaciones en plazo pendientes de evaluar.');
            return;
        }
        CLI::write("IA: evaluando {$pend} licitaciones en plazo con tu perfil…", 'cyan');
        $total = 0;
        while (true) {
            $r = $lib->evaluarLicitacionesIa(50);
            $total += $r['evaluadas'];
            CLI::write("IA: {$total} evaluadas, quedan {$r['pendientes']}.");
            if ($r['error'] !== null) {
                CLI::error('IA: ' . $r['error']);
                break;
            }
            if ($r['evaluadas'] === 0 || $r['pendientes'] === 0) {
                break;
            }
        }
    }
}
