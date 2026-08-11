<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\InvoiceModel;
use App\Services\InvoiceService;

class GenPdf extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'pdf:gen';
    protected $description = 'Generate PDF for an invoice to Desktop';

    public function run(array $params)
    {
        $invoiceId = 74;
        $invoiceModel = new InvoiceModel();
        $invoice = $invoiceModel->find($invoiceId);

        if (!$invoice) {
            CLI::error("Invoice not found");
            return;
        }

        $invoiceService = new InvoiceService();
        $pdfPath = $invoiceService->generatePdf($invoice, 'Paquete de 10000 créditos');

        if ($pdfPath) {
            $desktopPath = getenv('USERPROFILE') . '\\Desktop\\INV-2026-0060.pdf';
            copy($pdfPath, $desktopPath);
            CLI::write("PDF copiado a: " . $desktopPath, 'green');
        } else {
            CLI::error("Fallo al generar PDF.");
        }
    }
}
