<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameEmpresiaCompanyDetails extends Migration
{
    public function up()
    {
        $this->forge->renameTable('empresia_company_details', 'companies');
    }

    public function down()
    {
        $this->forge->renameTable('companies', 'empresia_company_details');
    }
}
