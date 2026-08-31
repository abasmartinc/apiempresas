<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\B2B\ProductProfileBuilder;
use App\Libraries\B2B\ProductNormalizer;
use App\Libraries\B2B\FinancialParser;
use App\Libraries\B2B\B2BOpportunityScorer;

class TestV4 extends BaseCommand {
    protected $group = 'B2B';
    protected $name = 'b2b:test';
    
    public function run(array $params) {
        CLI::write("--- REAL TEST SUITE ---", 'yellow');
        
        CLI::write("
1. FinancialParser");
        $tests = [
            '3.000 Euros' => 3000.0,
            '3.000,00 €' => 3000.0,
            '3000' => 3000.0,
            '3 000 EUR' => 3000.0,
            '12.500.000,50' => 12500000.50,
            '12,500,000.50' => 12500000.50, // will fail with current parser which replaces . with '' and , with .
            '0' => 0.0,
            '0,00' => 0.0,
            'N/D' => null,
            '-' => null,
            null => null,
            '' => null,
            'texto inválido' => null
        ];
        foreach ($tests as $in => $exp) {
            $act = FinancialParser::parse($in);
            $status = $act === $exp ? 'PASS' : 'FAIL';
            CLI::write("Input: " . json_encode($in) . " | Expected: " . json_encode($exp) . " | Actual: " . json_encode($act) . " | " . $status);
        }
        
        CLI::write("
2. TargetRole Isolation");
        $scorer = new B2BOpportunityScorer();
        $c = ['cnae_label' => 'Software', 'ventas_raw' => '1000000'];
        $ceo = $scorer->calculate($c, "Software B2B (Dirigido a: CEO)");
        $cfo = $scorer->calculate($c, "Software B2B (Dirigido a: CFO)");
        if ($ceo['opportunity_fit'] === $cfo['opportunity_fit']) CLI::write("OpportunityFit CEO == CFO: PASS"); else CLI::write("OpportunityFit CEO == CFO: FAIL");
        
        CLI::write("
3. Missing/Corrupted Evidence tests");
        // We will execute a test on ProductProfile caching
        $builder = new ProductProfileBuilder();
        CLI::write("ProductProfile validation: " . ($builder->isValidSchema([]) ? "FAIL" : "PASS"));
    }
}