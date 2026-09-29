<?php

namespace App\Libraries;

/**
 * Contactos "fríos": gente a la que seguir escribiendo perjudica la entrega del resto.
 *
 * Si un buzón recibe correo nuestro una y otra vez sin abrirlo ni hacer clic, Gmail y
 * compañía aprenden que no interesa y empiezan a mandar a spam también lo que sí importa
 * (avisos de cupo, cobros, alertas). Por eso los envíos masivos del admin los dejan fuera
 * salvo que se marque lo contrario.
 *
 * Frío = TODO esto a la vez:
 *   - cuenta de 30 días o más;
 *   - nunca ha hecho una consulta a la API;
 *   - no ha entrado en la web en los últimos 30 días (users.last_active_at);
 *   - ha recibido 5 correos o más (email_logs, envíos correctos);
 *   - no ha hecho clic en ninguno. Se miran clics y no aperturas: las aperturas no se
 *     miden a propósito (BCC al admin y precarga de Apple Mail las falsean).
 */
class ContactosFrios
{
    public const MIN_CORREOS = 5;
    public const DIAS        = 30;

    /**
     * De una lista de ids, los que son contactos fríos.
     *
     * @param  list<int> $userIds
     * @return array<int, true> mapa id => true
     */
    public static function de(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (empty($userIds)) {
            return [];
        }

        $db     = \Config\Database::connect();
        $limite = date('Y-m-d H:i:s', strtotime('-' . self::DIAS . ' days'));
        $frios  = [];

        foreach (array_chunk($userIds, 500) as $trozo) {
            $marcadores = implode(',', array_fill(0, count($trozo), '?'));
            try {
                $filas = $db->query("
                    SELECT u.id
                    FROM users u
                    JOIN (
                        SELECT user_id,
                               SUM(status = 'success')  AS enviados,
                               SUM(clicked_at IS NOT NULL) AS clics
                        FROM email_logs
                        WHERE user_id IN ({$marcadores})
                          -- Avisos al admin que se apuntaban con el id del cliente
                          AND COALESCE(template_slug, '') NOT IN ('admin_registration', 'payment_notification')
                          AND subject NOT LIKE '%Nuevo registro de usuario%'
                          AND subject NOT LIKE '%Nuevo Pago Recibido%'
                        GROUP BY user_id
                    ) e ON e.user_id = u.id
                    WHERE u.id IN ({$marcadores})
                      AND u.created_at <= ?
                      AND (u.last_active_at IS NULL OR u.last_active_at < ?)
                      AND e.enviados >= ?
                      AND e.clics = 0
                      AND NOT EXISTS (
                          SELECT 1 FROM api_usage_daily d
                          WHERE d.user_id = u.id AND d.requests_count > 0
                      )
                ", array_merge($trozo, $trozo, [$limite, $limite, self::MIN_CORREOS]))->getResultArray();
            } catch (\Throwable $e) {
                // Si falla, no se filtra a nadie: mejor enviar de más que dejar sin enviar
                log_message('error', '[ContactosFrios] ' . $e->getMessage());
                continue;
            }

            foreach ($filas as $f) {
                $frios[(int) $f['id']] = true;
            }
        }

        return $frios;
    }
}
