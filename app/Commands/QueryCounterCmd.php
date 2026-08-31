<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use App\Libraries\B2B\B2BOpportunityScorer;
use App\Libraries\RadarAnalyzer;

class QueryCounterCmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:querycount';
    protected $description = 'Count queries';

    public function run(array $params) {
        $db = Database::connect();
        $scorer = new B2BOpportunityScorer();
        
        $runTest = function($companies, $products, $desc, $db, $scorer) {
            CLI::write("--- Test: $desc ---");
            $startQueries = $db->query("SHOW SESSION STATUS LIKE 'Com_select'")->getRow()->Value;
            $start = microtime(true);
            
            foreach ($products as $p) {
                foreach ($companies as $c) {
                    RadarAnalyzer::calculateMatch($c, $p);
                    $scorer->calculate($c, $p);
                }
            }
            
            $end = microtime(true);
            $endQueries = $db->query("SHOW SESSION STATUS LIKE 'Com_select'")->getRow()->Value;
            // Subtract 1 for the endQueries call itself
            $count = $endQueries - $startQueries - 1;
            CLI::write("Total SELECTs: $count");
            CLI::write("Runtime: " . round($end - $start, 4) . "s
");
        };
        
        $c1 = [$db->query("SELECT * FROM companies LIMIT 1")->getRowArray()];
        $runTest($c1, ["consultoría"], "1 company x 1 product", $db, $scorer);
        
        $c10 = $db->query("SELECT * FROM companies LIMIT 10")->getResultArray();
        $runTest($c10, ["consultoría"], "10 companies x 1 product", $db, $scorer);
        
        $c25 = $db->query("SELECT * FROM companies LIMIT 25")->getResultArray();
        $runTest($c25, ["consultoría", "software"], "25 companies x 2 products", $db, $scorer);
    }
}