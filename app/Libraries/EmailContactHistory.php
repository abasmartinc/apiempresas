<?php

namespace App\Libraries;

/**
 * Historial de contacto por correo de cada usuario, leído de `email_logs`.
 *
 * Lo usan los paneles de administración (API y Solvencia) para saber a quién se
 * ha escrito ya, cuándo y con qué asunto. Un solo sitio para las reglas de qué
 * cuenta como "contacto":
 *
 *  - Los envíos fallidos no cuentan: el usuario no los ha recibido.
 *  - Las bienvenidas automáticas no cuentan: las recibe todo el mundo al darse
 *    de alta y no son una conversación iniciada por nosotros.
 *  - El aviso de nuevo registro al admin tampoco: va a nuestro buzón, pero se
 *    guarda con el user_id del usuario nuevo.
 */
class EmailContactHistory
{
    /**
     * Plantillas cuyos envíos NO cuentan como contacto con el usuario.
     */
    public const NON_CONTACT_TEMPLATE_SLUGS = [
        'welcome_email',
        'welcome_risk',
        'risk_pack_welcome',
        'risk_pro_welcome',
        'admin_registration',
    ];

    /**
     * Asuntos originales del seed de esas plantillas. Se usan además de los
     * actuales de `email_templates` para seguir reconociendo envíos antiguos si
     * el asunto se editó después.
     */
    private const SEED_SUBJECTS = [
        '🚀 [Configuración] Tu acceso a la API de Empresas España',
        '🛡️ Tu cuenta está lista: dispones de 3 informes de riesgo gratis este mes',
        '🛡️ Tu pack de {credits} auditorías de solvencia ya está activo',
        '⭐ Tu suscripción Solvencia Pro está activa: ya puedes vigilar tu cartera',
        '🆕 Nuevo registro de usuario: {name}',
    ];

    /**
     * Mapa user_id => [
     *   sent_count, failed_count, opened_count,
     *   last_sent_at, last_subject, last_opened_at,
     *   subjects  (asunto => fecha del último envío correcto, para detectar duplicados),
     *   history   (últimos $historyLimit envíos correctos, del más reciente al más antiguo:
     *              [subject, sent_at, opened_at])
     * ]
     *
     * Los usuarios sin ningún envío que cuente no aparecen en el mapa.
     */
    public function forUsers($db, array $userIds, int $historyLimit = 10): array
    {
        $map = [];
        if (empty($userIds)) {
            return $map;
        }

        // Una sola consulta ordenada por fecha: al recorrerla de más antiguo a más
        // reciente, el último valor que se escribe es el bueno.
        $rows = $db->table('email_logs')
            ->select('user_id, subject, status, opened_at, created_at')
            ->whereIn('user_id', $userIds)
            ->orderBy('created_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        $patterns = $this->nonContactSubjectPatterns($db);

        foreach ($rows as $r) {
            $uid = (int)$r['user_id'];
            $subject = trim((string)$r['subject']);

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $subject)) {
                    continue 2;
                }
            }

            if (!isset($map[$uid])) {
                $map[$uid] = [
                    'sent_count'     => 0,
                    'failed_count'   => 0,
                    'opened_count'   => 0,
                    'last_sent_at'   => null,
                    'last_subject'   => null,
                    'last_opened_at' => null,
                    'subjects'       => [],
                    'history'        => [],
                ];
            }

            if (($r['status'] ?? '') !== 'success') {
                $map[$uid]['failed_count']++;
                continue;
            }

            $map[$uid]['sent_count']++;
            $map[$uid]['last_sent_at'] = $r['created_at'];
            $map[$uid]['last_subject'] = $r['subject'];

            if ($subject !== '') {
                $map[$uid]['subjects'][$subject] = $r['created_at'];
            }

            if (!empty($r['opened_at'])) {
                $map[$uid]['opened_count']++;
                $map[$uid]['last_opened_at'] = $r['opened_at'];
            }

            $map[$uid]['history'][] = [
                'subject'   => $r['subject'],
                'sent_at'   => $r['created_at'],
                'opened_at' => $r['opened_at'] ?? null,
            ];
        }

        // Historial del más reciente al más antiguo, recortado
        foreach ($map as $uid => $info) {
            $map[$uid]['history'] = array_slice(array_reverse($info['history']), 0, $historyLimit);
        }

        return $map;
    }

    /**
     * Expresiones regulares que reconocen, por el asunto, los envíos de las
     * plantillas de NON_CONTACT_TEMPLATE_SLUGS.
     *
     * `email_logs` no guarda la plantilla, solo el asunto ya renderizado, así que
     * se compara contra los asuntos de las plantillas (es y en) tratando cada
     * {placeholder} como comodín.
     */
    private function nonContactSubjectPatterns($db): array
    {
        $subjects = self::SEED_SUBJECTS;

        try {
            $templates = $db->table('email_templates')
                ->select('subject, subject_en')
                ->whereIn('slug', self::NON_CONTACT_TEMPLATE_SLUGS)
                ->get()->getResultArray();
            foreach ($templates as $t) {
                $subjects[] = (string)($t['subject'] ?? '');
                $subjects[] = (string)($t['subject_en'] ?? '');
            }
        } catch (\Throwable $e) {
            log_message('error', '[EmailContactHistory] No se pudieron leer los asuntos de email_templates: ' . $e->getMessage());
        }

        $patterns = [];
        foreach (array_unique(array_map('trim', $subjects)) as $subject) {
            if ($subject === '') continue;
            $parts = preg_split('/\{[a-zA-Z0-9_]+\}/', $subject);
            $quoted = array_map(fn($p) => preg_quote($p, '/'), $parts);
            $patterns[] = '/^' . implode('.*', $quoted) . '$/u';
        }

        return $patterns;
    }
}
