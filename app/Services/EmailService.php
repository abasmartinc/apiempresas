<?php

namespace App\Services;

use CodeIgniter\Config\Services;

class EmailService
{
    /**
     * Send a notification email to the admin for a successful payment.
     */
    public function sendPaymentNotification(array $data)
    {
        $email = Services::email();
        $adminEmail = 'papelo.amh@gmail.com';

        $subject = "💰 ¡Nuevo Pago Recibido! - " . ($data['invoice_number'] ?? '');
        
        $email->setTo($adminEmail);
        $email->setSubject($subject);

        $body = view('emails/payment_notification', [
            'invoice'   => $data['invoice'],
            'customer'  => $data['customer_name'] ?? 'Cliente',
            'email'     => $data['customer_email'] ?? 'N/A',
            'plan'      => $data['plan_name'] ?? 'Plan API',
            'amount'    => $data['amount'] ?? '0.00',
            'currency'  => $data['currency'] ?? 'EUR',
            'subject'   => $subject
        ]);

        $email->setMessage($body);

        if ($email->send()) {
            log_message('info', "[EmailService] Notificación de pago enviada a {$adminEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar notificación de pago: " . $email->printDebugger(['headers']));
            return false;
        }
    }
}
