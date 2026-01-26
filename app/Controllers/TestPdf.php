<?php
namespace App\Controllers;
use App\Models\InvoiceModel;
use App\Models\UserModel;
use App\Services\InvoiceService;

class TestPdf extends BaseController {
    public function index() {
        $invService = new InvoiceService();
        $userModel = new UserModel();
        $user = $userModel->find(43); // HARDCODED USER ID from logs
        if (!$user) die("User not found");
        
        // Mocking an invoice creation
        try {
            $logoPath = ROOTPATH . 'public/images/logo.png';
            echo "ROOTPATH: " . ROOTPATH . "<br>";
            echo "Target Logo Path: " . $logoPath . "<br>";
            if (file_exists($logoPath)) {
                echo "Logo FOUND.<br>";
            } else {
                echo "Logo NOT FOUND.<br>";
            }

            echo "Starting PDF generation test...<br>";
            $invoice = $invService->createInvoiceFromPayment(43, 1, [], 'test_'.time());
            
            if ($invoice && !empty($invoice->pdf_path)) {
                echo "Success! Invoice created at: " . $invoice->pdf_path;
            } else {
                echo "Failed to create invoice or PDF path empty.";
                // Force a manual generatePdf call to see output
                // Accessing private method via reflection or just copying logic?
                // easier to just debug inside Service, but let's try to isolate.
            }
        } catch (\Throwable $e) {
            echo "Exception caught: " . $e->getMessage() . "<br>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    }
}
