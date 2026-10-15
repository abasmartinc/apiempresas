<?php

namespace App\Commands;

use App\Services\ContributionService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Comprueba en la web de la empresa los datos aportados desde la ficha ("Añadir
 * teléfono / web / email", 15-10-2026) y, con --aplicar, publica los verificados.
 *
 * Una web aportada vale si lleva el CIF de la empresa. Un teléfono o un email valen si
 * aparecen en una web de la empresa que lleva su CIF, o si los envía alguien con email de
 * ese dominio que ha confirmado el enlace. Ver ContributionService.
 *
 * Simula por defecto (descarga las webs y cuenta, sin escribir nada):
 *
 *   php spark datos:verificar-aportaciones                → simula
 *   php spark datos:verificar-aportaciones --guardar      → guarda el resultado de la comprobación
 *   php spark datos:verificar-aportaciones --aplicar      → guarda y publica lo verificado
 *                                                           (solo si la ficha sigue sin ese dato)
 *   --todas    vuelve a mirar también las ya comprobadas que siguen pendientes
 *   --limite=200
 *
 * Sin --todas solo mira las que no se han comprobado nunca y las confirmadas por
 * correo después de la última comprobación. Se puede poner en el cron cada hora con
 * --aplicar.
 */
class VerificarAportaciones extends BaseCommand
{
    protected $group       = 'Datos';
    protected $name        = 'datos:verificar-aportaciones';
    protected $description = 'Comprueba en la web de la empresa los teléfonos, webs y emails aportados desde la ficha.';
    protected $usage       = 'datos:verificar-aportaciones [--guardar] [--aplicar] [--todas] [--limite=200]';

    public function run(array $params)
    {
        $aplicar = CLI::getOption('aplicar') !== null;
        $guardar = $aplicar || CLI::getOption('guardar') !== null;
        $todas   = CLI::getOption('todas') !== null;
        $limite  = max(1, (int) (CLI::getOption('limite') ?? 200));

        $db  = \Config\Database::connect();
        $svc = new ContributionService($db);

        $b = $db->table('company_contributions')->where('estado', 'pendiente');
        if (!$todas) {
            $b->groupStart()
                ->where('verificado_at IS NULL', null, false)
                ->orWhere('email_confirmado_at > verificado_at', null, false)
              ->groupEnd();
        }
        $pendientes = $b->orderBy('id', 'ASC')->limit($limite)->get()->getResultArray();

        CLI::write(count($pendientes) . ' aportaciones pendientes por comprobar' . ($guardar ? '' : ' (SIMULACIÓN: añade --guardar o --aplicar para escribir)'), 'yellow');

        $porEmpresa = [];
        foreach ($pendientes as $p) {
            $porEmpresa[(int) $p['company_id']][] = $p;
        }

        $verificadas = 0;
        foreach ($porEmpresa as $companyId => $filas) {
            $res = $svc->verificarEmpresa($companyId, $filas, !$guardar);
            foreach ($filas as $f) {
                $r = $res[(int) $f['id']];
                $linea = "#{$f['id']} empresa {$companyId} {$f['campo']} {$f['valor']}: ";
                if ($r['verificado']) {
                    $verificadas++;
                    CLI::write($linea . 'VERIFICADO (' . $r['verificacion'] . ') ' . $r['detalle'], 'green');
                } else {
                    CLI::write($linea . $r['detalle'], 'light_gray');
                }
            }
        }
        CLI::write("{$verificadas} verificadas.", 'yellow');

        if (!$aplicar) {
            return;
        }

        // Publicar lo verificado (de esta pasada y de otras anteriores).
        $listas = $db->table('company_contributions')->where('estado', 'verificado')->orderBy('id', 'ASC')->get()->getResultArray();
        $ok = 0;
        foreach ($listas as $f) {
            $r = $svc->aplicar($f, true, 'Publicado por el comando (' . $f['verificacion'] . ')');
            $linea = "#{$f['id']} empresa {$f['company_id']} {$f['campo']} {$f['valor']}: ";
            if ($r['aplicado']) {
                $ok++;
                CLI::write($linea . 'publicado' . ($r['cache'] === false ? ' [caché NO vaciada]' : ''), 'green');
            } else {
                CLI::write($linea . 'no publicado: ' . ($r['motivo'] ?? '?') . ' (queda en el panel)', 'light_gray');
            }
        }
        CLI::write("{$ok} de " . count($listas) . ' publicadas.', 'yellow');
    }
}
