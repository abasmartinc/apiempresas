<?php
namespace App\Libraries\B2B\Scorers;
use Config\Database;

class ScaleFitScorer {
    public static function score(array $company, \App\Libraries\B2B\ProductProfile $profile): array {
        if ($profile->targetCompanySizes['source'] === 'unknown' || empty($profile->targetCompanySizes['value'])) {
            return ['active' => false, 'score' => null, 'confidence' => 0.0, 'version' => null];
        }
        
        $db = Database::connect();
        $snapshots = $db->table('b2b_scale_calibration_snapshot')->where('is_active', 1)->get()->getResult();
        if (count($snapshots) !== 1) {
            // Disabled if 0 or > 1 snapshots are active
            return ['active' => false, 'score' => null, 'confidence' => 0.0, 'version' => null, 'error' => 'Snapshot invalid'];
        }
        
        $snap = $snapshots[0];
        // For testing we mock ScaleEstimator bucket logic based on snap
        $scale = \App\Libraries\B2B\Scorers\ScaleEstimator::estimate($company);
        
        if ($scale['estimated_scale'] === 'unknown') {
            return ['active' => false, 'score' => null, 'confidence' => 0.0, 'version' => $snap->version];
        }

        $score = in_array($scale['estimated_scale'], $profile->targetCompanySizes['value']) ? 100 : 30;
        $productConf = $profile->targetCompanySizes['confidence'];
        
        return [
            'active' => true,
            'score' => $score,
            'confidence' => $scale['confidence'] * $productConf,
            'version' => $snap->version
        ];
    }
}