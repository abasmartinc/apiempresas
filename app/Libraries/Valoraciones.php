<?php

namespace App\Libraries;

/**
 * Valoraciones de clientes de la API (14-10-2026).
 *
 * Sustituye a la ventana de la home (partials/review_modal.php), que salía a visitantes
 * con 3 búsquedas y casi nunca a quien había usado la API. Ahora se pide en dos sitios:
 *
 *  - Tarjeta en el panel (partials/dashboard/review_card.php), a quien ya le ha
 *    funcionado: MIN_CONSULTAS consultas en total, o un plan de pago con DIAS_PAGO días.
 *  - Correo (EmailService::sendReviewRequest, tipo 'api_review_request'), con enlaces
 *    firmados a /valorar.
 *
 * Se guarda en user_reviews (sin migración). El comentario lleva delante unas marcas:
 *   [PANEL] o [CORREO]   de dónde viene
 *   [CITAR]              ha dado permiso para mostrar su nombre y empresa
 *   [PRIVADO]            1-3 estrellas: es para nosotros, no se publica
 * Ejemplo: "[PANEL][CITAR] La uso para el alta de proveedores y va muy bien".
 * Una valoración por usuario: si vuelve a valorar (p. ej. desde el correo), se actualiza.
 */
class Valoraciones
{
    /** Consultas totales a partir de las que se pide la valoración en el panel. */
    public const MIN_CONSULTAS = 50;

    /** Días con un plan de pago a partir de los que se pide (con algo de uso). */
    public const DIAS_PAGO = 7;

    /** Consultas mínimas para pedirla por tener plan de pago. */
    public const MIN_CONSULTAS_PAGO = 5;

    /** Si cierra la tarjeta, no se vuelve a mostrar en estos días. */
    public const DESCARTE_DIAS = 60;

    /** Quién recibe el aviso de cada valoración nueva. */
    public const AVISO_A = 'papelo.amh@gmail.com';

    public static function yaValoro(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }
        return \Config\Database::connect()->table('user_reviews')
            ->where('user_id', $userId)->countAllResults() > 0;
    }

    /** Consultas de la API de toda la vida del usuario (api_usage_daily). */
    public static function consultasTotales(int $userId): int
    {
        $fila = \Config\Database::connect()->table('api_usage_daily')
            ->selectSum('requests_count', 'total')
            ->where('user_id', $userId)
            ->get()->getRow();
        return (int) ($fila->total ?? 0);
    }

    /**
     * ¿Se enseña la tarjeta en el panel?
     *
     * @param object|array|null $plan plan activo (getActivePlanByUserId)
     */
    public static function mostrarEnPanel(int $userId, bool $isPaid, $plan): bool
    {
        if ($userId <= 0 || session('impersonator_id') || session('is_admin')) {
            return false;
        }
        if (self::descartada($userId) || self::yaValoro($userId)) {
            return false;
        }

        $total = self::consultasTotales($userId);
        if ($total >= self::MIN_CONSULTAS) {
            return true;
        }

        if ($isPaid && $plan) {
            $desde = is_array($plan) ? ($plan['created_at'] ?? null) : ($plan->created_at ?? null);
            $desde = $desde ? strtotime((string) $desde) : false;
            if ($desde && $desde <= strtotime('-' . self::DIAS_PAGO . ' days') && $total >= self::MIN_CONSULTAS_PAGO) {
                return true;
            }
        }
        return false;
    }

    public static function descartar(int $userId): void
    {
        cache()->save('valoracion_descartada_' . $userId, 1, self::DESCARTE_DIAS * 86400);
    }

    public static function descartada(int $userId): bool
    {
        return (bool) cache()->get('valoracion_descartada_' . $userId);
    }

    /**
     * Guarda (o actualiza) la valoración del usuario y avisa por correo.
     *
     * @param string $origen 'panel' o 'correo'
     */
    public static function guardar(int $userId, int $rating, string $comentario, bool $citar, string $origen, string $ip): bool
    {
        if ($userId <= 0 || $rating < 1 || $rating > 5) {
            return false;
        }

        $comentario = trim(preg_replace('/\s+/u', ' ', strip_tags($comentario)) ?? '');
        $comentario = mb_substr($comentario, 0, 1000);

        $marcas = '[' . ($origen === 'correo' ? 'CORREO' : 'PANEL') . ']';
        if ($rating <= 3) {
            $marcas .= '[PRIVADO]';
        } elseif ($citar) {
            $marcas .= '[CITAR]';
        }
        $texto = $marcas . ($comentario !== '' ? ' ' . $comentario : '');

        $db    = \Config\Database::connect();
        $tabla = $db->table('user_reviews');
        $antes = $tabla->where('user_id', $userId)->orderBy('id', 'DESC')->get()->getRowArray();

        if ($antes) {
            $ok = $db->table('user_reviews')->where('id', $antes['id'])->update([
                'rating'     => $rating,
                'comment'    => $texto,
                'ip_address' => $ip,
            ]);
        } else {
            $ok = $db->table('user_reviews')->insert([
                'user_id'    => $userId,
                'ip_address' => $ip,
                'rating'     => $rating,
                'comment'    => $texto,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        self::avisar($userId, $rating, $texto, (bool) $antes);
        return (bool) $ok;
    }

    private static function avisar(int $userId, int $rating, string $texto, bool $actualizada): void
    {
        try {
            $u = \Config\Database::connect()->table('users')->select('email, name')
                ->where('id', $userId)->get()->getRow();
            $quien = $u ? trim(($u->name ?? '') . ' <' . ($u->email ?? '') . '>') : 'usuario ' . $userId;

            $email = \Config\Services::email();
            $email->setTo(self::AVISO_A);
            $email->setSubject(($rating <= 3 ? '⚠️ ' : '⭐ ') . 'Valoración ' . $rating . '/5 de ' . $quien . ($actualizada ? ' (actualizada)' : ''));
            $email->setMessage(
                "Estrellas: {$rating}/5\n"
                . "Usuario: {$quien} (id {$userId})\n"
                . "Comentario: {$texto}\n\n"
                . ($rating <= 3 ? "Es una valoración baja: conviene escribirle y preguntar qué ha fallado.\n" : '')
                . (str_contains($texto, '[CITAR]') ? "Ha dado permiso para mostrar su nombre y empresa.\n" : '')
            );
            $email->setMailType('text');
            $email->send();
        } catch (\Throwable $e) {
            log_message('error', '[Valoraciones] No se pudo avisar: ' . $e->getMessage());
        }
    }

    /** Firma de los enlaces del correo (no caduca: una valoración se puede cambiar). */
    public static function firma(int $userId): string
    {
        return hash_hmac('sha256', 'valorar:' . $userId, (string) env('encryption.key', 'apiempresas-secret-key'));
    }

    public static function firmaValida(int $userId, string $firma): bool
    {
        return $userId > 0 && $firma !== '' && hash_equals(self::firma($userId), $firma);
    }

    /** Enlace del correo con la puntuación ya marcada (no guarda nada hasta enviar). */
    public static function enlace(int $userId, int $rating): string
    {
        return site_url('valorar') . '?u=' . $userId . '&r=' . $rating . '&t=' . self::firma($userId);
    }
}
