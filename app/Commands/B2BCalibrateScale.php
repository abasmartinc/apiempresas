<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use App\Libraries\B2B\FinancialParser;

class B2BCalibrateScale extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:calibrate-scale';
    protected $description = 'Calculate scale thresholds from valid ventas_raw';

    public function run(array $params) {
        CLI::write("B2B Scale Calibration Tool", 'green');
        $db = Database::connect();
        
        // LIMIT to 100,000 for reasonable execution time in this phase
        $query = $db->query("SELECT ventas_raw FROM companies WHERE ventas_raw IS NOT NULL AND ventas_raw != '' LIMIT 100000");
        
        $parsed = [];
        $rejected = 0;
        
        while ($r = $query->getUnbufferedRow('array')) {
            $val = FinancialParser::parse($r['ventas_raw']);
            if ($val !== null) {
                $parsed[] = $val;
            } else {
                $rejected++;
            }
        }
        
        $sampleSize = count($parsed);
        CLI::write("Valid revenue sample size: " . $sampleSize);
        CLI::write("Rejected/malformed values: " . $rejected);
        if ($sampleSize === 0) return;
        
        sort($parsed);
        
        $min = $parsed[0];
        $max = $parsed[$sampleSize - 1];
        $p10 = $parsed[(int)floor($sampleSize * 0.10)];
        $p25 = $parsed[(int)floor($sampleSize * 0.25)];
        $p50 = $parsed[(int)floor($sampleSize * 0.50)];
        $p75 = $parsed[(int)floor($sampleSize * 0.75)];
        $p90 = $parsed[(int)floor($sampleSize * 0.90)];
        
        CLI::write("Minimum: " . $min);
        CLI::write("P10: " . $p10);
        CLI::write("P25: " . $p25);
        CLI::write("P50 (Median): " . $p50);
        CLI::write("P75: " . $p75);
        CLI::write("P90: " . $p90);
        CLI::write("Maximum: " . $max);
        
        $version = date('Y-m-d_H-i-s');
        CLI::write("
SQL to activate manually:");
        CLI::write("START TRANSACTION;", 'yellow');
        CLI::write("UPDATE b2b_scale_calibration_snapshot SET is_active = 0 WHERE is_active = 1;", 'yellow');
        CLI::write("INSERT INTO b2b_scale_calibration_snapshot (version, is_active, sample_size, p10_ventas, p25_ventas, p50_ventas, p75_ventas, p90_ventas) VALUES ('$version', 1, $sampleSize, $p10, $p25, $p50, $p75, $p90);", 'yellow');
        CLI::write("COMMIT;", 'yellow');
    }
}