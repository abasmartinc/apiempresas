<?php

namespace App\Controllers;

use App\Models\TicketModel;
use App\Models\TicketReplyModel;

/**
 * Garantía de devolución de Solvencia Pro.
 *
 * Una garantía que el usuario no sabe cómo ejecutar convierte igual pero te
 * genera contracargos, que son mucho peores que un reembolso. Por eso aquí hay
 * una página real y un botón que abre un ticket, en vez de una frase en el
 * paywall y un correo a soporte que nadie encuentra.
 *
 * La devolución en sí se hace a mano en Stripe: esto abre el expediente,
 * avisa y deja constancia de la fecha de la petición.
 */
class Garantia extends BaseController
{
    public function index()
    {
        helper('company');

        $userId = (int) (session()->get('user_id') ?? 0);
        $estado = $this->estadoGarantia($userId);

        return $this->renderView('garantia/index', [
            'dias'        => (int) solvencia('garantiaDias', 30),
            'activa'      => (bool) solvencia('garantiaActiva', true),
            // Los DOS precios: la página la ve también quien contrató el plan anual,
            // y prometerle "te devolvemos 29 €" cuando pagó 290 € es prometerle de menos
            // justo en la página que existe para dar confianza.
            'precioPro'   => solvencia('precios.pro_mensual', '29 €'),
            'precioAnual' => solvencia('precios.pro_anual', '290 €'),
            'estado'      => $estado,
            'title'       => 'Garantía de 30 días — Solvencia Pro',
        ]);
    }

    /**
     * Abre el expediente. No devuelve dinero: eso se hace en Stripe.
     */
    public function solicitar()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if ($userId === 0) {
            return redirect()->to(site_url('enter'));
        }

        $motivo = trim((string) $this->request->getPost('motivo'));
        $estado = $this->estadoGarantia($userId);

        if (!$estado['tiene_suscripcion']) {
            return redirect()->to(site_url('garantia'))
                ->with('error', 'No encontramos una suscripción a Solvencia Pro en tu cuenta.');
        }

        // Una petición abierta es suficiente: si vuelve a pulsar, le llevamos a la
        // que ya existe en vez de abrirle un segundo expediente por lo mismo.
        $ticketModel = new TicketModel();
        $abierta = $ticketModel->where('user_id', $userId)
            ->where('category', 'garantia')
            ->where('status !=', 'closed')
            ->orderBy('id', 'DESC')
            ->first();

        if ($abierta) {
            return redirect()->to(site_url('tickets/' . $abierta['id']))
                ->with('success', 'Ya tienes una solicitud de garantía en curso.');
        }

        $asunto = 'Solicitud de garantía — Solvencia Pro';
        $ticketId = $ticketModel->insert([
            'user_id'  => $userId,
            'subject'  => $asunto,
            'category' => 'garantia',
            'status'   => 'open',
            'priority' => 'high',
        ]);

        if (!$ticketId) {
            return redirect()->to(site_url('garantia'))
                ->with('error', 'No hemos podido registrar la solicitud. Escríbenos a soporte@apiempresas.es y la tramitamos igual.');
        }

        $cuerpo = "Solicitud de devolución dentro de la garantía de "
            . (int) solvencia('garantiaDias', 30) . " días.\n\n"
            . ($motivo !== '' ? $motivo : 'Sin motivo indicado.');

        (new TicketReplyModel())->insert([
            'ticket_id' => $ticketId,
            'user_id'   => $userId,
            'is_admin'  => 0,
            'message'   => $cuerpo,
        ]);

        $this->avisarSoporte($ticketId, $userId, $motivo, $estado);
        $this->registrarEvento('risk_guarantee_requested', $userId, [
            'ticket_id' => $ticketId,
            'dias_desde_alta' => $estado['dias_desde_alta'],
        ]);

        return redirect()->to(site_url('tickets/' . $ticketId))
            ->with('success', 'Solicitud registrada. Te respondemos en menos de 24 horas laborables.');
    }

    // ---------------------------------------------------------------------

    /**
     * Elegibilidad. Deliberadamente permisiva: si no podemos fechar el alta,
     * dejamos pedirla igual y lo decide una persona. Bloquear una devolución
     * legítima por un fallo de fecha cuesta mucho más que revisar un ticket.
     */
    private function estadoGarantia(int $userId): array
    {
        $estado = [
            'logueado'          => $userId > 0,
            'tiene_suscripcion' => false,
            'dias_desde_alta'   => null,
            'en_plazo'          => true,
            'plan'              => '',
        ];

        if ($userId === 0) {
            return $estado;
        }

        try {
            $db = \Config\Database::connect();
            $planes = (array) solvencia('garantiaPlanes', ['risk_pro']);
            $planes = array_values(array_filter(array_map('strval', $planes)));
            if (empty($planes)) {
                $planes = ['risk_pro'];
            }

            $campos = $db->getFieldNames('user_subscriptions');
            $campoFecha = in_array('created_at', $campos, true)
                ? 'us.created_at'
                : (in_array('current_period_start', $campos, true) ? 'us.current_period_start' : null);

            $builder = $db->table('user_subscriptions us')
                ->select('ap.slug AS plan_slug, ap.name AS plan_name' . ($campoFecha ? ", {$campoFecha} AS alta" : ''))
                ->join('api_plans ap', 'ap.id = us.plan_id')
                ->where('us.user_id', $userId)
                ->whereIn('us.status', ['active', 'canceled'])
                ->groupStart()
                    ->whereIn('ap.slug', $planes)
                    ->orWhere('ap.product_type', 'risk')
                    ->orWhere('ap.product_type', 'bundle')
                ->groupEnd()
                ->orderBy('us.id', 'ASC')
                ->limit(1);

            $fila = $builder->get()->getRowArray();

            if ($fila) {
                $estado['tiene_suscripcion'] = true;
                $estado['plan'] = (string) ($fila['plan_name'] ?? $fila['plan_slug'] ?? 'Solvencia Pro');

                if (!empty($fila['alta'])) {
                    $dias = (int) floor((time() - strtotime((string) $fila['alta'])) / 86400);
                    $estado['dias_desde_alta'] = $dias;
                    $estado['en_plazo'] = $dias <= (int) solvencia('garantiaDias', 30);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[Garantia] No se pudo calcular la elegibilidad: ' . $e->getMessage());
        }

        return $estado;
    }

    private function avisarSoporte(int $ticketId, int $userId, string $motivo, array $estado): void
    {
        try {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);
            $nombre = $user->name ?? 'Usuario';
            $correo = $user->email ?? '';

            $dias = $estado['dias_desde_alta'] === null ? 'desconocidos' : $estado['dias_desde_alta'];

            $cuerpo  = "<h2>Solicitud de garantía (#{$ticketId})</h2>";
            $cuerpo .= '<p><strong>Usuario:</strong> ' . esc($nombre) . ' (' . esc($correo) . ') — id ' . $userId . '</p>';
            $cuerpo .= '<p><strong>Plan:</strong> ' . esc($estado['plan']) . '</p>';
            $cuerpo .= '<p><strong>Días desde el alta:</strong> ' . esc((string) $dias) . '</p>';
            $cuerpo .= '<p><strong>Motivo:</strong><br>' . nl2br(esc($motivo !== '' ? $motivo : 'Sin motivo indicado.')) . '</p>';
            $cuerpo .= "<hr><p>Devolver desde Stripe y cerrar el ticket: <a href='" . site_url('admin/tickets/' . $ticketId) . "'>ver ticket</a></p>";

            $email = \Config\Services::email();
            $email->setFrom('no-reply@apiempresas.es', 'APIEmpresas');
            $email->setTo('soporte@apiempresas.es');
            $email->setSubject('⚠️ Garantía solicitada #' . $ticketId . ' — ' . $correo);
            $email->setMessage($cuerpo);
            $email->setMailType('html');
            $email->send();
        } catch (\Throwable $e) {
            // Que falle el aviso no puede tumbar la solicitud: el ticket ya existe.
            log_message('error', '[Garantia] Aviso a soporte fallido: ' . $e->getMessage());
        }
    }

    private function registrarEvento(string $nombre, int $userId, array $meta = []): void
    {
        try {
            (new \App\Models\TrackingEventModel())->insert([
                'event_name'   => $nombre,
                'page'         => 'garantia',
                'user_id'      => $userId ?: null,
                'session_id'   => substr((string) session_id(), 0, 100),
                'anonymous_id' => null,
                'element'      => 'garantia_form',
                'metadata'     => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Garantia] No se pudo registrar el evento: ' . $e->getMessage());
        }
    }
}
