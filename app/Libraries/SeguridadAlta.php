<?php

namespace App\Libraries;

/**
 * Seguridad del alta y del acceso con Google, GitHub y LinkedIn.
 *
 * El alta con correo y contraseña no comprueba que el correo sea de quien lo escribe,
 * y las entradas sociales unen por correo con la cuenta que ya exista. Juntas, las dos
 * cosas permitían registrar el correo de otro con una contraseña propia y conservar el
 * acceso cuando el dueño entrara después con Google (y contratara un plan).
 */
class SeguridadAlta
{
    /** Altas nuevas por IP y hora en cada vía (la misma cifra que QuickUnlock). */
    public const ALTAS_POR_HORA = 3;

    /**
     * Llamar ANTES de unir una identidad social a una cuenta encontrada por correo.
     *
     * Si nada demuestra que el correo de la cuenta es de quien la creó (no tenía
     * ninguna identidad social y nunca ha pulsado un enlace de un correo nuestro), la
     * contraseña que tuviera deja de valer. Y si la cuenta no ha hecho ninguna
     * consulta, se cambia también la API Key (con uso no se toca, para no romper una
     * integración en marcha).
     */
    public static function alVincular(object $user, string $proveedor = ''): void
    {
        try {
            $userId = (int) ($user->id ?? 0);
            if ($userId <= 0) {
                return;
            }
            // Ya entró antes con una identidad social: el correo quedó demostrado entonces
            if (!empty($user->google_id) || !empty($user->github_id) || !empty($user->linkedin_id)) {
                return;
            }

            $db = \Config\Database::connect();

            // Ha pulsado un enlace de un correo nuestro: tiene acceso al buzón
            $conClic = $db->table('email_logs')
                ->where('user_id', $userId)
                ->groupStart()->where('clicked_at IS NOT NULL')->orWhere('logged_in_at IS NOT NULL')->groupEnd()
                ->countAllResults() > 0;
            if ($conClic) {
                return;
            }

            $db->table('users')->where('id', $userId)->update([
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'reset_token'   => null,
                'reset_expires' => null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);

            $conUso = $db->table('api_usage_daily')->where('user_id', $userId)->countAllResults() > 0;
            if (!$conUso) {
                foreach ($db->table('api_keys')->select('id')->where('user_id', $userId)->get()->getResultArray() as $k) {
                    $db->table('api_keys')->where('id', (int) $k['id'])->update([
                        'api_key'    => bin2hex(random_bytes(32)),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            log_message('info', "[SeguridadAlta] Cuenta {$userId} sin correo verificado unida a un acceso social: contraseña anulada"
                . ($conUso ? '' : ' y API Key cambiada') . '.');

            // Que el usuario sepa qué ha pasado y cómo entrar a partir de ahora: aviso
            // en la página a la que llega y un correo. Sin esto, quien intentara luego
            // su contraseña de siempre pensaría que el acceso no funciona.
            $via = $proveedor !== '' ? $proveedor : 'tu cuenta social';
            try {
                session()->setFlashdata('info', 'A partir de ahora entras con ' . $via . '. La contraseña que tenía esta cuenta ha dejado de valer; si quieres una, créala desde «¿Olvidaste la clave?».');
            } catch (\Throwable $e) {
            }
            try {
                (new \App\Services\EmailService())->sendAccesoSocialVinculado(
                    ['id' => $userId, 'email' => (string) ($user->email ?? ''), 'name' => (string) ($user->name ?? '')],
                    $via,
                    !$conUso
                );
            } catch (\Throwable $e) {
                log_message('error', '[SeguridadAlta] Aviso de acceso vinculado: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            // Nunca impedir la entrada por un fallo aquí
            log_message('error', '[SeguridadAlta::alVincular] ' . $e->getMessage());
        }
    }

    /** ¿Puede entrar esta cuenta? (las entradas sociales no miraban is_active) */
    public static function activa(?object $user): bool
    {
        return $user !== null && (!isset($user->is_active) || (int) $user->is_active === 1);
    }

    /**
     * Límite de altas nuevas por IP y hora para una vía. false = se ha pasado.
     * Cada vía tiene su contador.
     */
    public static function permiteAlta(string $ip, string $via): bool
    {
        try {
            return \Config\Services::throttler()->check(md5($ip . '_alta_' . $via), self::ALTAS_POR_HORA, HOUR) !== false;
        } catch (\Throwable $e) {
            return true;   // si el limitador falla, no se bloquea un alta real
        }
    }

    /**
     * Alta atómica: usuario, API Key y suscripción gratuita se crean juntos o no se crea
     * nada. Antes, si fallaba la segunda inserción, quedaba el usuario creado sin clave
     * y al reintentar se le decía "ya existe una cuenta".
     *
     *   SeguridadAlta::altaInicio();  ...inserciones...  SeguridadAlta::altaFin();
     *
     * altaFin() lanza una excepción si alguna inserción falló (también cuando la base
     * de datos no lanza errores por sí misma).
     */
    public static function altaInicio(): void
    {
        \Config\Database::connect()->transBegin();
    }

    public static function altaFin(): void
    {
        $db = \Config\Database::connect();
        if ($db->transStatus() === false) {
            $db->transRollback();
            throw new \RuntimeException('No se pudo completar el alta: se ha deshecho.');
        }
        $db->transCommit();
    }

    /** Deshace un alta a medias (para los catch). No falla si no hay nada abierto. */
    public static function altaDeshacer(): void
    {
        try {
            $db = \Config\Database::connect();
            if ($db->transDepth > 0) {
                $db->transRollback();
            }
        } catch (\Throwable $e) {
        }
    }
}
