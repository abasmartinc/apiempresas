<?php

namespace App\Controllers;

use App\Services\OpenAiService;
use App\Services\AiContextService;
use CodeIgniter\Controller;
use OpenAI;

class AiChat extends Controller
{
    protected $aiService;
    protected $contextService;

    public function __construct()
    {
        $this->aiService = new OpenAiService();
        $this->contextService = new AiContextService();
    }

    /**
     * Endpoint API para procesar mensajes del chat
     */
    public function sendMessage()
    {
        $message = $this->request->getPost('message');
        if (empty($message)) {
            return $this->response->setJSON(['error' => 'No se recibió ningún mensaje.'])->setStatusCode(400);
        }

        // 1. Recuperar historial de la sesión (máximo 10 mensajes para no saturar)
        $session = session();
        $history = $session->get('chat_history') ?? [];
        
        // 2. Construir mensajes para OpenAI
        $messages = [
            ['role' => 'system', 'content' => $this->contextService->getSystemPrompt()]
        ];
        
        // Añadir historial previo
        foreach ($history as $msg) {
            $messages[] = $msg;
        }
        
        // Añadir mensaje actual
        $messages[] = ['role' => 'user', 'content' => $message];

        // 3. Loop de Orquestación (Soporte para Tool Calling)
        try {
            $start = microtime(true);
            $finalResponse = $this->runChatOrchestration($messages);
            $duration = round(microtime(true) - $start, 2);
            
            log_message('info', "Chat Response Time: {$duration}s | Message: {$message}");

            // 4. Actualizar historial en sesión
            $history[] = ['role' => 'user', 'content' => $message];
            $history[] = ['role' => 'assistant', 'content' => $finalResponse];
            
            // Mantener solo los últimos 10
            if (count($history) > 10) array_splice($history, 0, 2);
            
            // Asegurar que la respuesta es UTF-8 válida antes de setJSON para evitar errores de Malformed UTF-8
            $finalResponseClean = iconv('UTF-8', 'UTF-8//IGNORE', $finalResponse);

            return $this->response->setJSON([
                'status' => 'success',
                'reply'  => $finalResponseClean
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Chat Controller Error: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Error interno del servidor.'])->setStatusCode(500);
        }
    }

    /**
     * Limpia recursivamente un array o string para asegurar UTF-8 válido
     */
    protected function cleanUtf8($data)
    {
        if (is_string($data)) {
            return iconv('UTF-8', 'UTF-8//IGNORE', mb_convert_encoding($data, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'));
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->cleanUtf8($value);
            }
        }
        return $data;
    }

    /**
     * Ejecuta el loop de OpenAI permitiendo llamadas a funciones
     */
    protected function runChatOrchestration(array $messages)
    {
        $apiKey = env('OPENAI_API_KEY');
        $httpClient = new \GuzzleHttp\Client(['verify' => false]);
        $client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withHttpClient($httpClient)
            ->make();
        
        $params = [
            'model' => 'gpt-4o-mini',
            'messages' => $this->cleanUtf8($messages), // Limpiar todo antes de enviar
            'tools' => $this->contextService->getAvailableTools(),
            'tool_choice' => 'auto',
        ];

        // IMPORTANTE: Cerramos la sesión antes de la llamada larga a la IA 
        session_write_close();

        $t1 = microtime(true);
        $response = $client->chat()->create($params);
        $d1 = round(microtime(true) - $t1, 2);
        log_message('info', "AI Step 1 (Intent/Tools): {$d1}s");

        $message = $response->choices[0]->message;

        if (!empty($message->toolCalls)) {
            $t_tools_start = microtime(true);
            
            $messages[] = [
                'role' => 'assistant',
                'content' => null,
                'tool_calls' => array_map(fn($tc) => [
                    'id' => $tc->id,
                    'type' => 'function',
                    'function' => [
                        'name' => $tc->function->name,
                        'arguments' => $tc->function->arguments,
                    ],
                ], $message->toolCalls),
            ];

            foreach ($message->toolCalls as $toolCall) {
                $functionName = $toolCall->function->name;
                $functionArgs = json_decode($toolCall->function->arguments, true) ?? [];
                
                $result = $this->contextService->callTool($functionName, $functionArgs);
                $messages[] = ['role' => 'tool', 'tool_call_id' => $toolCall->id, 'name' => $functionName, 'content' => $result];
            }
            
            $d_tools = round(microtime(true) - $t_tools_start, 2);
            log_message('info', "AI Step 2 (Total Tools Logic): {$d_tools}s");

            try {
                $t2 = microtime(true);
                $secondResponse = $client->chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => $this->cleanUtf8($messages), // Limpiar todo de nuevo
                ]);
                $d2 = round(microtime(true) - $t2, 2);
                log_message('info', "AI Step 3 (Final Response): {$d2}s");

                return $secondResponse->choices[0]->message->content;
            } catch (\Exception $apiEx) {
                log_message('error', "OpenAI Step 3 API Error: " . $apiEx->getMessage());
                throw $apiEx;
            }
        }

        return $message->content;
    }

    /**
     * Resetear conversación
     */
    public function resetChat()
    {
        session()->remove('chat_history');
        return $this->response->setJSON(['status' => 'cleared']);
    }
}
