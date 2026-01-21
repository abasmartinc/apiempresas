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
        $email->clear(true); 

        // Forzar remitente desde configuración o valor seguro
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

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
            log_message('info', "[EmailService] Notificación de pago ENVIADA a {$adminEmail}. Remitente: {$fromEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar notificación de pago a {$adminEmail}: " . $email->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Send the invoice PDF to the user after a successful payment.
     */
    public function sendInvoiceToUser(array $data)
    {
        $email = Services::email();
        $email->clear(true); 
        
        // Forzar remitente
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $userEmail = $data['customer_email'];
        $subject   = "🧾 Tu factura de APIEmpresas.es - " . ($data['invoice_number'] ?? '');

        $email->setTo($userEmail);
        $email->setSubject($subject);

        $body = view('emails/user_invoice', [
            'name'           => $data['customer_name'] ?? 'Cliente',
            'plan_name'      => $data['plan_name'] ?? 'Plan API',
            'amount'         => $data['amount'] ?? '0.00',
            'currency'       => $data['currency'] ?? 'EUR',
            'invoice_number' => $data['invoice_number'] ?? '',
        ]);

        $email->setMessage($body);

        // Adjuntar PDF si existe
        if (!empty($data['pdf_path'])) {
            // Intentar con ROOTPATH y verificar existencia real
            $relativePath = ltrim($data['pdf_path'], '/\\');
            $fullPath = ROOTPATH . $relativePath;
            
            if (file_exists($fullPath)) {
                $email->attach($fullPath, 'attachment', 'factura_' . $data['invoice_number'] . '.pdf');
                log_message('info', "[EmailService] PDF localizado y adjuntado: " . $fullPath);
            } else {
                // Fallback a FCPATH por si ROOTPATH no es lo que esperamos
                $fullPath = FCPATH . $relativePath;
                if (file_exists($fullPath)) {
                    $email->attach($fullPath, 'attachment', 'factura_' . $data['invoice_number'] . '.pdf');
                    log_message('info', "[EmailService] PDF localizado y adjuntado (vía FCPATH): " . $fullPath);
                } else {
                    log_message('error', "[EmailService] No se encontró el archivo PDF en ninguna ruta conocida para: " . $relativePath);
                }
            }
        }

        if ($email->send()) {
            log_message('info', "[EmailService] Factura ENVIADA a {$userEmail}. Remitente: {$fromEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar factura a {$userEmail}: " . $email->printDebugger(['headers', 'subject']));
            return false;
        }
    }
}
