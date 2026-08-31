<?php
namespace App\Libraries\B2B;

class ProductProfile {
    public array $targetIndustries;
    public array $excludedIndustries;
    public array $targetCompanySizes;

    public function __construct(array $data) {
        $this->targetIndustries = $data['target_industries'] ?? ['value'=>[], 'source'=>'unknown', 'confidence'=>0.0];
        $this->excludedIndustries = $data['excluded_industries'] ?? ['value'=>[], 'source'=>'unknown', 'confidence'=>0.0];
        $this->targetCompanySizes = $data['target_company_sizes'] ?? ['value'=>null, 'source'=>'unknown', 'confidence'=>0.0];
    }
}