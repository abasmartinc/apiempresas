<?php

namespace App\Services;

use App\Models\UserModel;

/**
 * Enlaces de acceso de un solo uso ("magic link").
 *
 * Existe para cerrar un agujero concreto: el registro rápido abría sesión en la
 * cuenta de CUALQUIER usuario existente con solo escribir su email. Se hacía para
 * no poner fricción a quien se registró solo con email y nunca puso contraseña,
 * pero el efecto era que cualquiera podía entrar en la cuenta de otro — con sus
 * facturas, sus empresas vigiladas y su suscripción.
 *
 * Ahora, si el email ya existe, se le manda un enlace a ESE buzón. Quien lo pulsa
 * demuestra que es el dueño del correo, que es exactamente lo que la contraseña
 * habría demostrado.
 *
 * Decisiones:
 *
 *  - En la BD se guarda el SHA-256 del token, nunca el token. Si alguien lee la
 *    tabla `users` (un volcado, un backup) no puede entrar en ninguna cuenta.
 *  - Un solo uso y 30 minutos. El consumo es un UPDATE condicionado al hash, así
 *    que dos clics simultáneos no abren dos sesiones.
 *  - Consumir exige un POST. Los antivirus de correo (Outlook Safe Links,
 *    Defender, Proofpoint...) ABREN los enlaces para analizarlos: si el GET
 *    consumiera el token, el cliente real se encontraría un "enlace caducado".
 *    El GET solo enseña un botón "Entrar"; el POST es el que abre sesión.
 *  - Un envío por minuto y usuario, para que el formulario no sirva para llenar
 *    de correos el buzón de otra persona.
 *  - El destino se guarda con el token y se limpia: solo rutas internas.
 */
class LoginLinkService
{
    /** Minutos de validez del enlace. */
    public const VALIDEZ_MIN = 30;

    /** Segundos mínimos entre dos envíos al mismo usuario. */
    public const ESPERA_REENVIO = 60;

    protected UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    /**
     * Genera y envía un enlace de acceso.
     *
     * @return string 'enviado' | 'reciente' (se envió hace menos de un minuto; no se
     *                reenvía) | 'error' (no se pudo enviar el correo)
     */
    public function enviar(object $user, string $redirect = ''): string
    {
        // Antiabuso: si ya se le mandó uno hace menos de un minuto, no se manda otro.
        // Se contesta igual que si se hubiera enviado (el primero sigue valiendo).
        if (!empty($user->login_token_sent_at)) {
            $hace = time() - strtotime((string) $user->login_token_sent_at);
            if ($hace >= 0 && $hace < self::ESPERA_REENVIO) {
                return 'reciente';
            }
        }

        $token = bin2hex(random_bytes(32));

        $this->users->update($user->id, [
            'login_token_hash'     => hash('sha256', $token),
            'login_token_expires'  => date('Y-m-d H:i:s', time() + self::VALIDEZ_MIN * 60),
            'login_token_redirect' => self::limpiarDestino($redirect),
            'login_token_sent_at'  => date('Y-m-d H:i:s'),
        ]);

        try {
            $res = (new EmailService())->sendLoginLinkEmail(
                (string) $user->email,
                site_url('acceso/' . $token),
                self::VALIDEZ_MIN,
                (int) $user->id
            );
        } catch (\Throwable $e) {
            log_message('error', '[LoginLink] Fallo enviando el enlace al usuario ' . $user->id . ': ' . $e->getMessage());
            return 'error';
        }

        if (is_array($res) && empty($res['success'])) {
            log_message('error', '[LoginLink] El correo no salió para el usuario ' . $user->id . ': ' . ($res['error'] ?? '?'));
            return 'error';
        }

        return 'enviado';
    }

    /**
     * Busca el usuario de un token SIN consumirlo. Para pintar la pantalla de
     * confirmación: un GET no debe gastar el enlace (ver docblock de la clase).
     */
    public function buscar(string $token): ?object
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        $user = $this->users
            ->where('login_token_hash', hash('sha256', $token))
            ->where('login_token_expires >=', date('Y-m-d H:i:s'))
            ->where('source_app', 'apiempresas')
            ->first();

        if (!$user || (isset($user->is_active) && (int) $user->is_active !== 1)) {
            return null;
        }

        return $user;
    }

    /**
     * Consume el token: lo invalida y devuelve el usuario, o null si no vale.
     *
     * El UPDATE va condicionado al hash, así que solo UNA petición consigue
     * afectar a la fila: un doble clic o un reenvío del formulario no abren dos
     * sesiones con el mismo enlace.
     */
    public function consumir(string $token): ?object
    {
        $user = $this->buscar($token);
        if (!$user) {
            return null;
        }

        $db = \Config\Database::connect();
        $db->table('users')
            ->where('id', $user->id)
            ->where('login_token_hash', hash('sha256', $token))
            ->update([
                'login_token_hash'    => null,
                'login_token_expires' => null,
                'last_login_at'       => date('Y-m-d H:i:s'),
            ]);

        if ($db->affectedRows() !== 1) {
            return null;
        }

        return $user;
    }

    /**
     * Deja el destino en una ruta interna relativa, o '' si no lo es.
     *
     * `site_url()` ya antepone el dominio, pero se limpia igual: el valor viaja
     * por formularios y se guarda en la BD, y "//otro-dominio.com" o
     * "https://..." no tienen por qué llegar a ninguna parte.
     */
    public static function limpiarDestino(?string $destino): string
    {
        $destino = trim((string) $destino);
        if ($destino === '' || strlen($destino) > 500) {
            return '';
        }
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $destino) || str_starts_with($destino, '//') || str_contains($destino, '\\')) {
            return '';
        }

        return ltrim($destino, '/');
    }
}
