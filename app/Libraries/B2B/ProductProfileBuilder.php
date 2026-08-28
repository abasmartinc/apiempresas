<?php
namespace App\Libraries\B2B;

use Config\Database;
use App\Services\OpenAiService;

class ProductProfileBuilder {
    protected $db;
    protected $openai;

    public function __construct() {
        $this->db = Database::connect();
        $this->openai = new OpenAiService();
    }

    protected static $memoryCache = [];

    public function getProfile(string $rawProduct): ProductProfile {
        $normalized = ProductNormalizer::normalize($rawProduct);
        $hash = ProductNormalizer::hash($normalized);
        $config = config('B2BScoring');
        
        if (isset(self::$memoryCache[$hash])) {
            return self::$memoryCache[$hash];
        }
        
        $cached = $this->db->table('b2b_product_profiles')
            ->where('product_hash', $hash)
            ->where('classifier_model', 'gpt-4o-mini')
            ->where('profile_version', $config->product_profile_version)
            ->get()->getRowArray();

        if ($cached && !empty($cached['profile_json'])) {
            $data = json_decode($cached['profile_json'], true);
            if (self::isValidSchema($data)) {
                $profile = new ProductProfile($data);
                self::$memoryCache[$hash] = $profile;
                return $profile;
            }
        }

        $profileData = $this->generateFromLLM($normalized);
        
        if ($profileData && self::isValidSchema($profileData)) {
            // $this->db->query("INSERT INTO b2b_product_profiles (product_hash, normalized_product_text, profile_json, classifier_model, profile_version) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE profile_json = VALUES(profile_json), updated_at = NOW()", [$hash, $normalized, json_encode($profileData), 'gpt-4o-mini', $config->product_profile_version]);
            $profile = new ProductProfile($profileData);
            self::$memoryCache[$hash] = $profile;
            return $profile;
        }
        
        return new ProductProfile(['target_industries' => ['source' => 'unknown', 'value' => [], 'confidence' => 0], 'excluded_industries' => ['source' => 'unknown', 'value' => [], 'confidence' => 0], 'target_company_sizes' => ['source' => 'unknown', 'value' => [], 'confidence' => 0]]); 
    }
    
    public static function isValidSchema($data): bool {
        if (!is_array($data)) return false;
        if (!isset($data['target_industries']['source'])) return false;
        if (!isset($data['excluded_industries']['source'])) return false;
        if (!isset($data['target_company_sizes']['source'])) return false;
        return true;
    }

    private function generateFromLLM(string $product): ?array {
        $prompt = "Analyze this B2B product/service: '$product'.
        Output a JSON object identifying the IDEAL BUYER COMPANIES, NOT the industry of the seller.
        {
          \"target_industries\": {\"source\": \"explicit\"|\"inferred_high_confidence\"|\"unknown\", \"value\": [\"CNAE keywords\"], \"confidence\": 0.0-1.0},
          \"excluded_industries\": {\"source\": \"explicit\"|\"inferred_high_confidence\"|\"unknown\", \"value\": [\"CNAE keywords\"], \"confidence\": 0.0-1.0},
          \"target_company_sizes\": {\"source\": \"explicit\"|\"unknown\", \"value\": [\"micro\", \"small\", \"medium\", \"large\"], \"confidence\": 0.0-1.0}
        }
        CRITICAL RULES:
        1. 'target_industries' represents the BUYERS. For example, 'consultoría de subvenciones' is sold TO ANY COMPANY eligible for grants, so it is TRANSVERSAL. If the buyer industry is transversal or not explicitly narrow, set source='unknown' and value=[].
        2. DO NOT return English translations like 'Manufacturing'. Return keywords that match Spanish CNAE (e.g. 'Industria manufacturera', 'Construcción', 'Transporte', 'Tecnología').
        3. If you are not completely sure, use source: 'unknown' and empty value [].";
        
        $response = $this->openai->getChatResponse([
            ['role' => 'system', 'content' => 'You are a strict B2B classification AI identifying BUYER profiles in Spain. Reply only with valid JSON.'],
            ['role' => 'user', 'content' => $prompt]
        ]);
        
        if ($response) {
            $jsonStr = trim($response, " 	
  `");
            if (str_starts_with($jsonStr, 'json')) $jsonStr = substr($jsonStr, 4);
            return json_decode(trim($jsonStr), true);
        }
        return null;
    }
}