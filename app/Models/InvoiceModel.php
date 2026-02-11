<?php

namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table         = 'invoices';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'invoice_number',
        'amount',
        'tax_amount',
        'total_amount',
        'currency',
        'status',
        'pdf_path',
        'stripe_invoice_id',
        'billing_name',
        'billing_email',
        'billing_address',
        'billing_vat',
    ];

    public function getNextInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = $this->where('invoice_number LIKE', "INV-{$year}-%")
                            ->orderBy('invoice_number', 'DESC')
                            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "INV-{$year}-{$newNumber}";
    }
    public function getMonthlyRevenue($ym)
    {
        return $this->selectSum('total_amount', 'total')
                    ->where('status', 'paid')
                    ->like('created_at', $ym, 'after')
                    ->first();
    }
}
