<?php
namespace App\Libraries\B2B\Scorers;

class TriggerScorer {
    public static function score(array $company): array {
        $config = config('B2BScoring');
        $events = $company['mock_triggers'] ?? []; // injected for tests
        
        if (empty($events)) {
            return ['score' => 0, 'confidence' => 0.4]; // Not observed, not known zero. Low confidence.
        }
        
        $primary = 0;
        foreach($events as $e) {
            $primary = max($primary, $e['effective']);
        }
        
        $supportRaw = 0;
        $first = true;
        foreach($events as $e) {
            if ($e['effective'] == $primary && $first) {
                $first = false; continue;
            }
            $supportRaw += $e['effective'];
        }
        
        // Saturation formula: MAX * (1 - e^(-raw/SCALE))
        $boost = $config->trigger_max_support_boost * (1 - exp(-$supportRaw / $config->trigger_support_scale));
        
        return [
            'score' => min(100, $primary + $boost),
            'confidence' => 0.85
        ];
    }
}