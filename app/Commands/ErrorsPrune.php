<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Limpieza del registro de errores (error_events). Para el cron, una vez al dia:
 *
 *   php spark errors:prune            borra los eventos de mas de 90 dias y deja como mucho 200 por issue
 *   php spark errors:prune 30 100     30 dias y 100 por issue
 *
 * Los issues (error_issues) y su historial NO se borran: son la memoria de que paso y de que se arreglo.
 */
class ErrorsPrune extends BaseCommand
{
    protected $group = 'Maintenance';
    protected $name = 'errors:prune';
    protected $description = 'Borra los eventos de error viejos (por defecto mas de 90 dias, y mas de 200 por issue).';
    protected $usage = 'errors:prune [dias] [por_issue]';

    public function run(array $params)
    {
        $dias = max(1, (int) ($params[0] ?? 90));
        $porIssue = max(1, (int) ($params[1] ?? 200));
        $db = \Config\Database::connect();

        $db->query('DELETE FROM error_events WHERE created_at < ?', [date('Y-m-d H:i:s', strtotime("-{$dias} days"))]);
        $viejos = $db->affectedRows();

        // De cada issue con demasiados eventos, se quedan los ultimos $porIssue.
        $sobran = 0;
        $issues = $db->query('SELECT issue_id FROM error_events GROUP BY issue_id HAVING COUNT(*) > ?', [$porIssue])->getResultArray();

        foreach ($issues as $f) {
            $corte = $db->query('SELECT id FROM error_events WHERE issue_id = ? ORDER BY id DESC LIMIT 1 OFFSET ?', [(int) $f['issue_id'], $porIssue - 1])->getRow();

            if ($corte !== null) {
                $db->query('DELETE FROM error_events WHERE issue_id = ? AND id < ?', [(int) $f['issue_id'], (int) $corte->id]);
                $sobran += $db->affectedRows();
            }
        }

        CLI::write("Eventos borrados: {$viejos} de mas de {$dias} dias, {$sobran} por pasar de {$porIssue} por issue.");
    }
}
