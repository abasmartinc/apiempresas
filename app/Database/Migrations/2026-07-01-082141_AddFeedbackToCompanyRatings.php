<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeedbackToCompanyRatings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('company_ratings', [
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'rating'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('company_ratings', 'feedback');
    }
}
