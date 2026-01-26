<?php

namespace App\Services;

use App\Models\InvoiceModel;
use App\Models\UserModel;
use App\Models\ApiPlanModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoiceService
{
    protected $invoiceModel;

    public function __construct()
    {
        $this->invoiceModel = new InvoiceModel();
    }

    /**
     * Genera un registro de factura y su PDF correspondiente
     */
    public function createInvoiceFromPayment(int $userId, int $planId, array $billingData = [], ?string $stripeInvoiceId = null)
    {
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        $planModel = new ApiPlanModel();
        $plan = $planModel->find($planId);

        if (!$user || !$plan) {
            return null;
        }

        // 1. Evitar duplicados si ya existe esta factura de Stripe
        if ($stripeInvoiceId) {
            $existing = $this->invoiceModel->where('stripe_invoice_id', $stripeInvoiceId)->first();
            if ($existing) {
                log_message('info', "[InvoiceService] Saltando creación de factura duplicada para Stripe ID: {$stripeInvoiceId}");
                return $existing;
            }
        }

        // Datos de la factura
        $amount = (float)$plan->price_monthly;
        $taxRate = 0.21; // 21% IVA
        $taxAmount = $amount * $taxRate;
        $totalAmount = $amount + $taxAmount;

        $invoiceData = [
            'user_id'           => $userId,
            'invoice_number'    => $this->invoiceModel->getNextInvoiceNumber(),
            'amount'            => $amount,
            'tax_amount'        => $taxAmount,
            'total_amount'      => $totalAmount,
            'currency'          => 'EUR',
            'status'            => 'paid',
            'stripe_invoice_id' => $stripeInvoiceId,
            'billing_name'      => $billingData['name'] ?? $user->name,
            'billing_email'     => $billingData['email'] ?? $user->email,
            'billing_address'   => $billingData['address'] ?? '',
            'billing_vat'       => $billingData['vat'] ?? '',
        ];

        $invoiceId = $this->invoiceModel->insert($invoiceData);
        $invoice = $this->invoiceModel->find($invoiceId);

        // Generar PDF
        $pdfPath = $this->generatePdf($invoice, $plan->name);
        
        if ($pdfPath) {
            $this->invoiceModel->update($invoiceId, ['pdf_path' => $pdfPath]);
            $invoice->pdf_path = $pdfPath; // Asegurar que el objeto en memoria tiene el valor
        }

        return $invoice;
    }

    private function generatePdf($invoice, $planName)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        $html = view('invoices/professional_template', [
            'invoice'   => $invoice,
            'plan_name' => $planName
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();
        
        // Usar WRITEPATH para asegurar consistencia con el Controller
        $directory = WRITEPATH . 'invoices/' . date('Y/m');
        
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true)) {
                log_message('critical', "[InvoiceService] Failed to create directory: $directory");
                return null;
            }
        }

        $filename = $invoice->invoice_number . '.pdf';
        $fullPath = $directory . '/' . $filename;
        
        if (file_put_contents($fullPath, $output) === false) {
             log_message('critical', "[InvoiceService] Failed to write PDF to: $fullPath");
             return null;
        }

        // Retornamos la ruta relativa para guardar en BD (compatible con logic del controller)
        return 'writable/invoices/' . date('Y/m') . '/' . $filename;
    }
}
