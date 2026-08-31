<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
class TestCheck extends BaseCommand {
    protected $group = 'B2B';
    protected $name = 'b2b:check';
    protected $description = 'Check DB and OpenAI';
    public function run(array $params) {
        $openai = new \App\Services\OpenAiService();
        try {
            $embedding = $openai->getEmbeddings("test");
            if (is_array($embedding) && count($embedding) > 0) {
                CLI::write("OpenAI Embeddings request: PASS");
            } else {
                CLI::write("OpenAI Embeddings request: FAIL - returned null/empty");
            }
        } catch (\Exception $e) {
            CLI::write("OpenAI Embeddings request: FAIL (" . $e->getMessage() . ")", 'red');
        }
    }
}