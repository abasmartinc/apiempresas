<?php
require 'vendor/autoload.php';
$envFile = file_get_contents('.env');
preg_match('/OPENAI_API_KEY=(.*)/', $envFile, $matches);
$apiKey = trim($matches[1] ?? '');
echo 'Key: ' . substr((string)$apiKey, 0, 10) . "...\n";

try {
    $client = \OpenAI::factory()
        ->withApiKey((string)$apiKey)
        ->withHttpClient(new \GuzzleHttp\Client(['verify' => false]))
        ->make();

    $response = $client->chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [['role' => 'user', 'content' => 'Test']]
    ]);
    echo "Success! Response: " . $response->choices[0]->message->content . "\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
