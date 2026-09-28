<?php

namespace App\Commands;

use App\Services\WebhookDispatcher;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Genera y envía los webhooks de la vigilancia (28-09-2026).
 *
 * Uso:
 *   php spark webhooks:run                 (encola los eventos nuevos y envía los pendientes)
 *   php spark webhooks:run --solo-encolar
 *   php spark webhooks:run --solo-enviar
 *
 * Cron recomendado: cada 15 minutos. Se puede lanzar dos veces seguidas sin duplicar
 * nada (webhook_deliveries.delivery_uuid es único y se reclaman los envíos con un token).
 */
class WebhooksRun extends BaseCommand
{
    protected $group       = 'API';
    protected $name        = 'webhooks:run';
    protected $description = 'Encola los eventos de la vigilancia para los webhooks y envía los pendientes.';
    protected $usage       = 'webhooks:run [--solo-encolar] [--solo-enviar] [--limite 200]';

    public function run(array $params)
    {
        $soloEncolar = CLI::getOption('solo-encolar') !== null;
        $soloEnviar  = CLI::getOption('solo-enviar') !== null;
        $limite      = (int) (CLI::getOption('limite') ?: 200);

        if (!$soloEnviar) {
            $e = WebhookDispatcher::enqueue();
            CLI::write("Encolar: {$e['webhooks']} webhooks, {$e['eventos']} eventos, {$e['encolados']} envíos nuevos.", 'green');
        }
        if (!$soloEncolar) {
            $d = WebhookDispatcher::dispatch($limite);
            CLI::write("Enviar: {$d['enviados']} intentos, {$d['entregados']} entregados, {$d['fallidos']} para reintentar, {$d['muertos']} descartados.", 'green');
        }
    }
}
