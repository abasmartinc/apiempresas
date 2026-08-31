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
            try {
                $this->db->query(
                    "INSERT INTO b2b_product_profiles (product_hash, normalized_product_text, profile_json, classifier_model, profile_version) 
                     VALUES (?, ?, ?, ?, ?) 
                     ON DUPLICATE KEY UPDATE profile_json = VALUES(profile_json), updated_at = NOW()",
                    [$hash, $normalized, json_encode($profileData), 'gpt-4o-mini', $config->product_profile_version]
                );
            } catch (\Exception $e) {
                log_message('error', 'b2b_product_profiles cache write error: ' . $e->getMessage());
            }
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
        1. 'target_industries' represents the BUYING COMPANIES' industries, NOT internal departments or business functions. For example, HR software is NOT restricted to HR firms, Accounting software is NOT restricted to Accounting firms, Consulting is NOT restricted to Consulting/Accounting firms, Telephony is NOT restricted to Telecom firms.
        2. For TRULY HORIZONTAL / GENERIC PRODUCTS with no defensible sector concentration (e.g. 'control horario para empresas', 'consultoría', 'consultoría general', 'consultoría de subvenciones', 'software de facturación para empresas', 'telefonía empresarial', 'software de correo electrónico'), set source='unknown' and value=[].
        3. Infer buyer industries ONLY when the product's commercial use has strong, defensible concentration in specific buyer sectors (e.g. 'gestión documental' -> Asesorías, Abogados, Notarías, Sanidad; 'prevención de riesgos' -> Industria, Construcción, Transporte; 'mantenimiento maquinaria CNC' -> Industria manufacturera; 'software clínicas dentales' -> Odontología; 'software abogados' -> Abogados). Prefer 1-4 strongest buyer concepts.
        4. DO NOT return English translations like 'Manufacturing'. Return keywords that match Spanish CNAE.
        5. If the product wording does not clearly narrow the buyer vertical, use source='unknown' and value=[].";
        
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