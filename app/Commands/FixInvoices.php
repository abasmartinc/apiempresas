<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\InvoiceModel;
use App\Models\ApiPlanModel;
use App\Models\UsersuscriptionsModel;
use App\Services\InvoiceService;
use Stripe\StripeClient;

class FixInvoices extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'fix:invoices';
    protected $description = 'Regenerate invoices for a user with Stripe data';

    public function run(array $params)
    {
        $userId = $params[0] ?? 105;
        CLI::write("Processing user {$userId}...");

        $invoiceModel = new InvoiceModel();
        $invoices = $invoiceModel->where('user_id', $userId)->findAll();

        if (empty($invoices)) {
            CLI::write("No invoices found for user {$userId}.", 'red');
            return;
        }

        $stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
        $invoiceService = new InvoiceService();

        $desktopDir = 'C:\Users\papel\Desktop\facturas_angel';
        if (!is_dir($desktopDir)) {
            mkdir($desktopDir, 0777, true);
        }

        $regenerated = [];

        foreach ($invoices as $invoice) {
            if (!$invoice->stripe_invoice_id) {
                CLI::write("Skipping {$invoice->invoice_number}, no Stripe ID.", 'yellow');
                continue;
            }

            CLI::write("Processing invoice {$invoice->invoice_number}");

            try {
                // Hardcoding the data from the user's screenshot because the Stripe API is blocked by IP restriction:
                // "The API key provided does not allow requests from your IP address."
                $billingName = 'Concesionarios Del Sur, Sa';
                $billingEmail = 'spoc@grupoconcesur.es';
                $billingAddress = 'Carretera A-92, km 5,5, 41500, Sevilla, SE, ES';
                $billingVat = ''; // Not visible in screenshot, leaving as empty unless we know it

                $invoiceModel->update($invoice->id, [
                    'billing_name' => $billingName,
                    'billing_email' => $billingEmail,
                    'billing_address' => $billingAddress,
                    'billing_vat' => $billingVat
                ]);

                $invoice = $invoiceModel->find($invoice->id);

                $planModel = new ApiPlanModel();
                $subModel = new UsersuscriptionsModel();
                $sub = $subModel->where('user_id', $userId)->orderBy('id', 'DESC')->first();
                
                // Tratar de deducir el plan (por el precio o por la suscripción activa)
                $planName = 'Suscripción Pro';
                if ($sub) {
                    $plan = $planModel->find($sub->plan_id);
                    if ($plan) $planName = $plan->name;
                }

                $pdfPathRelative = $invoiceService->generatePdf($invoice, $planName);
                
                if ($pdfPathRelative) {
                    $invoiceModel->update($invoice->id, ['pdf_path' => $pdfPathRelative]);
                    $absolutePath = FCPATH . '../' . $pdfPathRelative;
                    
                    if (file_exists($absolutePath)) {
                        $targetFile = $desktopDir . DIRECTORY_SEPARATOR . $invoice->invoice_number . '.pdf';
                        copy($absolutePath, $targetFile);
                        CLI::write("Saved PDF to {$targetFile}", 'green');
                        $regenerated[] = $invoice->invoice_number;
                    } else {
                        CLI::write("File not found: {$absolutePath}", 'red');
                    }
                }
            } catch (\Exception $e) {
                CLI::write("Error on invoice {$invoice->invoice_number}: " . $e->getMessage(), 'red');
            }
        }

        CLI::write("Done! Regenerated: " . implode(', ', $regenerated), 'green');
    }
}
