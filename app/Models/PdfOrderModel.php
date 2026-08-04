<?php

namespace App\Models;

use CodeIgniter\Model;

class PdfOrderModel extends Model
{
    protected $table = 'pdf_orders';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'uuid',
        'company_id',
        'stripe_session_id',
        'status',
        'agency_name',
        'brand_color',
        'footer_text',
        'email',
        'logo_path',
        'created_at'
    ];
}
