<?php

namespace App\Commands;

use App\Libraries\Valoraciones;
use App\Services\EmailService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Envía el correo de petición de valoración (api_review_request) a una cuenta concreta,
 * para ver cómo llega (14-10-2026). Ver App\Libraries\Valoraciones.
 *
 * Uso:
 *   php spark valoraciones:probar-correo tu@correo.com
 *   php spark valoraciones:probar-correo tu@correo.com --en
 *   php spark valoraciones:probar-correo tu@correo.com --plan Pro --consultas 1234
 *
 * El correo tiene que ser el de una cuenta que exista: los enlaces 1-5 van firmados con
 * su id. No lo apunta en user_email_automation, así que no impide que el correo de
 * verdad le llegue después. Ojo: si en /valorar se pulsa "Enviar", la valoración se
 * guarda de verdad en user_reviews (y llega el aviso).
 */
class ValoracionesProbarCorreo extends BaseCommand
{
    protected $group       = 'API';
    protected $name        = 'valoraciones:probar-correo';
    protected $description = 'Envía el correo de petición de valoración a una cuenta para ver cómo llega.';
    protected $usage       = 'valoraciones:probar-correo <email> [--en] [--plan Pro] [--consultas 120]';

    public function run(array $params)
    {
        $email = trim((string) ($params[0] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Falta el correo. Uso: php spark ' . $this->usage);
            return EXIT_ERROR;
        }

        $u = \Config\Database::connect()->table('users')
            ->select('id, email, name, lang, unsuscribe')
            ->where('email', $email)->get()->getRowArray();
        if (!$u) {
            CLI::error("No hay ninguna cuenta con el correo {$email}. Tiene que existir: los enlaces van firmados con su id.");
            return EXIT_ERROR;
        }
        if (!empty($u['unsuscribe'])) {
            CLI::write('Aviso: esa cuenta está dada de baja de los correos; puede que no se envíe.', 'yellow');
        }

        $uid       = (int) $u['id'];
        $lang      = CLI::getOption('en') !== null ? 'en' : (($u['lang'] ?? '') === 'en' ? 'en' : 'es');
        $plan      = (string) (CLI::getOption('plan') ?? '');
        $consultas = CLI::getOption('consultas') !== null
            ? (int) CLI::getOption('consultas')
            : Valoraciones::consultasTotales($uid);

        $usuario = ['id' => $uid, 'user_id' => $uid, 'email' => $u['email'], 'name' => $u['name'], 'lang' => $lang];

        CLI::write("Enviando 'api_review_request' a {$u['email']} (id {$uid}, " . strtoupper($lang) . ", {$consultas} consultas" . ($plan !== '' ? ", plan {$plan}" : '') . ')...');
        $res = (new EmailService())->sendReviewRequest($usuario, $consultas, $plan);

        if (!empty($res['skipped'])) {
            CLI::write('No se ha enviado (omitido): ' . ($res['reason'] ?? $res['error'] ?? 'sin motivo'), 'yellow');
        } elseif (!empty($res['success'])) {
            CLI::write('Enviado. Revisa la bandeja de entrada (y la de spam).', 'green');
        } else {
            CLI::error('No se ha podido enviar: ' . ($res['error'] ?? 'error desconocido'));
        }

        CLI::newLine();
        CLI::write('Enlaces del correo (abren /valorar con la nota marcada; no guardan nada hasta pulsar Enviar):');
        for ($i = 1; $i <= 5; $i++) {
            CLI::write("  {$i}: " . Valoraciones::enlace($uid, $i));
        }

        return EXIT_SUCCESS;
    }
}
