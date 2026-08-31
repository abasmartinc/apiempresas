<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use App\Libraries\B2B\ProductProfileBuilder;
use App\Libraries\B2B\B2BOpportunityScorer;

class B2BBenchmark extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:benchmark';
    protected $description = 'Run Benchmark for V2 vs V1';

    public function run(array $params) {
        CLI::write("B2B Benchmark Tool", 'green');
        $db = Database::connect();
        
        $db->query('TRUNCATE TABLE b2b_product_profiles');
        
        $builder = new ProductProfileBuilder();
        $scorer = new B2BOpportunityScorer();
        
        $products = [
            "software de control horario para empresas",
            "servicios de ciberseguridad para empresas industriales",
            "mantenimiento de maquinaria CNC",
            "SEO para clínicas dentales",
            "consultoría de subvenciones",
            "instalación de placas solares para empresas",
            "ERP para empresas industriales de más de 100 empleados",
            "servicio de limpieza de oficinas en Madrid",
            "software para empresas que trabajan con administraciones públicas",
            "consultoría"
        ];
        
        CLI::write("Generating ProductProfiles...");
        $profiles = [];
        foreach ($products as $p) {
            CLI::write("-> $p");
            $prof = $builder->getProfile($p);
            $profiles[$p] = $prof;
            CLI::write("   " . json_encode($prof));
        }
    }
}