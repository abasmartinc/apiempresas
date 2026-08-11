<?php
namespace App\Controllers;
use App\Models\InvoiceModel;
use App\Services\InvoiceService;

class TestPdf2 extends BaseController {
    public function index() {
        $invService = new InvoiceService();
        $invoiceModel = new InvoiceModel();
        $invoice = $invoiceModel->find(74); // Invoice ID to regenerate
        if (!$invoice) die("Invoice not found");
        
        try {
            $pdfPath = $invService->generatePdf($invoice, 'Paquete de 10000 créditos');
            echo "PDF Regenerado en: " . $pdfPath;
        } catch (\Throwable $e) {
            echo "Exception caught: " . $e->getMessage() . "<br>";
        }
    }
}
