<?php

namespace App\Services;

/**
 * Pedidos de PDF de Solvencia (Informe de riesgo 3,90 € y Dossier 360º 5,90 €).
 *
 * Antes el pago solo se daba por hecho al abrir la página de gracias
 * (Company::successPremiumPdf), y el comprador no recibía ningún correo: ni
 * confirmación, ni enlace de descarga (si cerraba la pestaña perdía el PDF), ni
 * nada con lo que pedir factura. Además la sesión de Stripe no decía qué se
 * vendía y llevaba el id del PEDIDO en client_reference_id, que el webhook lee
 * como id de USUARIO: la venta se apuntaba a otro usuario.
 *
 * Ahora confirmarPago() lo llaman la página de gracias y el webhook, lo que llegue
 * primero. Es idempotente: marca pagado una vez, avisa al admin una vez y manda el
 * correo al comprador una vez.
 */
class PdfOrderService
{
    /** Valores de metadata.plan en la sesión de Stripe de estos pedidos */
    public const PLANES = ['risk_pdf_single', 'risk_dossier_single'];

    /** Evento en tracking_events que marca "comprador ya avisado" (sin tocar el esquema) */
    private const EVENTO_AVISO = 'pdf_buyer_emailed';

    /**
     * @param string $uuid        pdf_orders.uuid
     * @param string $sessionId   id de la sesión de Stripe (o sim_... en el simulador)
     * @param string $emailStripe email que el comprador dio en Stripe (customer_details)
     * @param int    $userId      usuario con sesión, si se sabe (webhook: metadata.user_id)
     * @return array{ok: bool, pagado_ahora?: bool, avisado?: bool, motivo?: string}
     */
    public function confirmarPago(string $uuid, string $sessionId = '', string $emailStripe = '', int $userId = 0): array
    {
        helper('company');   // solvencia(), company_url(), company_display_name()
        $db    = \Config\Database::connect();
        $order = $db->table('pdf_orders')->where('uuid', $uuid)->get()->getRowArray();
        if (!$order) {
            return ['ok' => false, 'motivo' => 'pedido no encontrado'];
        }

        // 1. Pagado, una sola vez aunque lleguen a la vez la página y el webhook
        $pagadoAhora = false;
        if (($order['status'] ?? '') !== 'paid') {
            $cambios = ['status' => 'paid'];
            if ($sessionId !== '' && empty($order['stripe_session_id'])) {
                $cambios['stripe_session_id'] = $sessionId;
            }
            $db->table('pdf_orders')->where('id', (int) $order['id'])->where('status !=', 'paid')->update($cambios);
            $pagadoAhora = $db->affectedRows() > 0;
        }

        // 2. A quién escribir: el correo del formulario, si no el de Stripe, si no el
        //    de la cuenta con la que compró
        $email = trim((string) ($order['email'] ?? ''));
        if ($email === '') {
            $email = trim($emailStripe);
            if ($email === '' && $userId > 0) {
                $email = (string) ($db->table('users')->select('email')->where('id', $userId)->get()->getRow()->email ?? '');
            }
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $db->table('pdf_orders')->where('id', (int) $order['id'])->update(['email' => $email]);
            }
        }

        $empresa   = $db->table('companies')->select('id, cif, company_name')->where('id', (int) $order['company_id'])->get()->getRowArray() ?: [];
        $esDossier = strpos((string) ($order['footer_text'] ?? ''), '[RISK_REPORT]') === false;

        // 3. Aviso al admin, solo al pasar a pagado (como antes)
        if ($pagadoAhora) {
            $this->avisarAdmin($order, $email, $esDossier);
        }

        // 4. Quien compró con sesión (sabido por el webhook) se queda con la empresa
        //    desbloqueada aunque no vuelva a la página de gracias
        if ($userId > 0 && !empty($empresa['cif'])) {
            try {
                (new CompanyRiskService())->desbloquearPorCompra($userId, (string) $empresa['cif']);
            } catch (\Throwable $e) {
                log_message('error', '[PdfOrderService] desbloquearPorCompra: ' . $e->getMessage());
            }
        }

        // 5. Correo al comprador, una vez por pedido
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            log_message('warning', "[PdfOrderService] Pedido {$uuid} pagado sin email del comprador: no se le puede avisar.");
            return ['ok' => true, 'pagado_ahora' => $pagadoAhora, 'avisado' => false, 'motivo' => 'sin email'];
        }
        if ($this->yaAvisado($uuid)) {
            return ['ok' => true, 'pagado_ahora' => $pagadoAhora, 'avisado' => false, 'motivo' => 'ya avisado'];
        }

        // La marca va antes del envío: si la página y el webhook llegan a la vez, el
        // segundo ve la marca y no repite. Si el envío falla, se quita para reintentar.
        $marca = $this->marcarAvisado($uuid, $userId);

        $nombreEmpresa = function_exists('company_display_name')
            ? company_display_name((string) ($empresa['company_name'] ?? ''), 'la empresa')
            : (string) ($empresa['company_name'] ?? '');
        $urlFicha = '';
        if (!empty($empresa['cif']) && function_exists('company_url')) {
            $urlFicha = company_url(['cif' => $empresa['cif'], 'name' => $empresa['company_name'] ?? '']);
        }

        $sesion  = $sessionId !== '' ? $sessionId : (string) ($order['stripe_session_id'] ?? '');
        $res = (new EmailService())->sendPdfPurchaseConfirmation($email, [
            'user_id'      => $userId,
            'empresa'      => $nombreEmpresa !== '' ? $nombreEmpresa : 'la empresa',
            'cif'          => (string) ($empresa['cif'] ?? ''),
            'dossier'      => $esDossier,
            'url_descarga' => site_url('empresa/download-premium-pdf?uuid=' . rawurlencode($uuid)),
            // Cambiar logo, colores y pie: la página de gracias lo permite con el pedido
            'url_ajustes'  => $sesion !== '' ? site_url('empresa/success-premium-pdf?session_id=' . rawurlencode($sesion) . '&uuid=' . rawurlencode($uuid)) : '',
            'url_ficha'    => $urlFicha,
        ]);

        if (empty($res['success'])) {
            if ($marca > 0) {
                $db->table('tracking_events')->where('id', $marca)->delete();
            }
            log_message('error', "[PdfOrderService] No se pudo avisar al comprador del pedido {$uuid}: " . ($res['error'] ?? 'sin detalle'));
            return ['ok' => true, 'pagado_ahora' => $pagadoAhora, 'avisado' => false, 'motivo' => 'fallo de envío'];
        }

        return ['ok' => true, 'pagado_ahora' => $pagadoAhora, 'avisado' => true];
    }

    private function yaAvisado(string $uuid): bool
    {
        return \Config\Database::connect()->table('tracking_events')
            ->where('event_name', self::EVENTO_AVISO)
            ->like('metadata', '"uuid":"' . $uuid . '"')
            ->countAllResults() > 0;
    }

    private function marcarAvisado(string $uuid, int $userId): int
    {
        try {
            $db = \Config\Database::connect();
            $db->table('tracking_events')->insert([
                'event_name'   => self::EVENTO_AVISO,
                'page'         => 'empresa/pdf',
                'user_id'      => $userId,
                'session_id'   => '',
                'anonymous_id' => '',
                'element'      => 'pdf_single',
                'metadata'     => json_encode(['uuid' => $uuid]),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
            return (int) $db->insertID();
        } catch (\Throwable $e) {
            log_message('error', '[PdfOrderService] marcarAvisado: ' . $e->getMessage());
            return 0;
        }
    }

    /** El mismo aviso que enviaba Company::successPremiumPdf al marcar el pedido pagado. */
    private function avisarAdmin(array $order, string $email, bool $esDossier): void
    {
        try {
            $centimos = (int) solvencia($esDossier ? 'centimos.dossier' : 'centimos.pdf', $esDossier ? 590 : 390);
            (new EmailService())->sendPaymentNotification([
                'invoice_number' => 'PDF-' . strtoupper(substr((string) $order['uuid'], 0, 8)),
                'customer_name'  => !empty($order['agency_name']) ? $order['agency_name'] : 'Cliente',
                'customer_email' => $email !== '' ? $email : 'No especificado',
                'plan_name'      => $esDossier ? 'Dossier Completo 360º (Marca Blanca)' : 'Informe de Riesgo y Solvencia (PDF)',
                'amount'         => number_format($centimos / 100, 2, '.', ''),
                'currency'       => 'EUR',
                'invoice'        => 'N/A',
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[PdfOrderService] aviso al admin: ' . $e->getMessage());
        }
    }
}
