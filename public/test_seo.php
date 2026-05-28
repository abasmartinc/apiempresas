<?php
// Bootstrap CodeIgniter
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require realpath(FCPATH . '../app/Config/Paths.php') ?: FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();

$cif = 'B53524898';
$companyModel = new \App\Models\CompanyModel();
$company = $companyModel->getByCif($cif);

if (!$company) {
    die("Company not found");
}

echo "Testing OpenAI Service...\n";
$aiService = new \App\Services\OpenAiService();

$name = $company['name'] ?? 'la empresa';
$cnae = $company['cnae_label'] ?? 'un sector no especificado';
$founded = $company['founded'] ?? 'fecha desconocida';
$prov = $company['province'] ?? $company['provincia'] ?? 'España';
$purpose = $company['corporate_purpose'] ?? '';

$prompt = "Escribe un resumen ejecutivo y analítico (máximo 2 párrafos concisos) sobre la empresa {$name}. 
Se fundó en {$founded}, su sede está en {$prov} y se dedica a {$cnae}.
Objeto social (si hay): {$purpose}.
El texto debe ser profesional, objetivo y optimizado para SEO, aportando valor al lector. 
No repitas la misma estructura típica de Wikipedia. Sé original y directo. No uses Markdown ni negritas, solo texto plano con saltos de línea (párrafos).";

echo "Calling OpenAI API...\n";
try {
    $generatedText = $aiService->getChatResponse([
        ['role' => 'system', 'content' => 'Eres un analista experto en empresas españolas. Escribes de forma corporativa y objetiva.'],
        ['role' => 'user', 'content' => $prompt],
    ], ['max_tokens' => 300]);

    echo "Result:\n";
    echo $generatedText;
    echo "\n";
} catch (\Throwable $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
