<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\OpenAiService;

class TestOpenAiCmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:test-openai';
    protected $description = 'Test OpenAI connectivity for embeddings';

    public function run(array $params) {
        $openai = new OpenAiService();
        CLI::write("Testing OpenAI getEmbeddings with full error catch...");
        
        $text = "Esta es una empresa de prueba para verificar la API.";
        try {
            $t0 = microtime(true);
            $reflection = new \ReflectionClass($openai);
            $property = $reflection->getProperty('client');
            $property->setAccessible(true);
            $client = $property->getValue($openai);

            $response = $client->embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);
            CLI::write("Success.");
        } catch (\Exception $e) {
            CLI::write("Error: " . $e->getMessage());
        }
    }
}