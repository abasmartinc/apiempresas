<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CompanyModel;

class TestSearchProfile extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:profile';
    protected $description = 'Profiles the searchMany method.';

    public function run(array $params)
    {
        $model = new CompanyModel();
        
        $start = microtime(true);
        $term = 'restaurante';
        $limit = 100;
        
        // CIF
        $t0 = microtime(true);
        $builderCif = $model->builder();
        $builderCif->select('companies.id, companies.cif');
        $builderCif->like('companies.cif', $term, 'after');
        $builderCif->limit($limit + 1);
        $builderCif->get()->getResultArray();
        CLI::write("CIF Time: " . round(microtime(true) - $t0, 3) . " s");

        // FULLTEXT 1: Boolean with wildcard
        $t1 = microtime(true);
        $sql = "SELECT companies.id, 
                MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST ('+restaurante*' IN BOOLEAN MODE) as score
                FROM companies
                WHERE MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST ('+restaurante*' IN BOOLEAN MODE)
                ORDER BY score DESC LIMIT 101";
        $model->db->query($sql)->getResultArray();
        CLI::write("FULLTEXT BOOLEAN WILDCARD: " . round(microtime(true) - $t1, 3) . " s");

        // FULLTEXT 2: Boolean NO wildcard
        $t1 = microtime(true);
        $sql = "SELECT companies.id, 
                MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST ('+restaurante' IN BOOLEAN MODE) as score
                FROM companies
                WHERE MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST ('+restaurante' IN BOOLEAN MODE)
                ORDER BY score DESC LIMIT 101";
        $model->db->query($sql)->getResultArray();
        CLI::write("FULLTEXT BOOLEAN NO WILDCARD: " . round(microtime(true) - $t1, 3) . " s");

        // FULLTEXT 3: Natural Language
        $t1 = microtime(true);
        $sql = "SELECT companies.id, 
                MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST ('restaurante' IN NATURAL LANGUAGE MODE) as score
                FROM companies
                WHERE MATCH(companies.company_name, companies.cnae_label, companies.registro_mercantil) AGAINST ('restaurante' IN NATURAL LANGUAGE MODE)
                LIMIT 101";
        $model->db->query($sql)->getResultArray();
        CLI::write("FULLTEXT NATURAL LANGUAGE: " . round(microtime(true) - $t1, 3) . " s");

        // FULLTEXT 4: LIKE ONLY
        $t1 = microtime(true);
        $sql = "SELECT companies.id 
                FROM companies
                WHERE companies.company_name LIKE '%restaurante%'
                LIMIT 101";
        $model->db->query($sql)->getResultArray();
        CLI::write("LIKE ONLY: " . round(microtime(true) - $t1, 3) . " s");
    }
}
