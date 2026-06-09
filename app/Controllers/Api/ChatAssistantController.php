<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenAI;
use Config\Database;

class ChatAssistantController extends ResourceController
{
    public function handle()
    {
        $req = $this->request->getJSON(true);
        $chatHistory = $req['messages'] ?? [];
        
        if (empty($chatHistory)) {
            return $this->fail('Mensajes vacíos', 400);
        }

        // 1. Configurar el System Prompt
        $systemPrompt = [
            'role' => 'system',
            'content' => "Eres un asistente experto en Bases de Datos B2B para APIEmpresas. Tu objetivo es ayudar al usuario a configurar los filtros para descargar un listado de empresas españolas.
El usuario te hablará en lenguaje natural ('Quiero restaurantes en Madrid'). Tú debes extraer los filtros estructurados.
Reglas:
- province: Solo nombres de provincias de España válidos (ej: 'Madrid', 'Barcelona', 'Sevilla'). Nunca quites la provincia si el usuario la menciona. Solo ponlo a null si el usuario dice explícitamente 'borra la provincia', 'quita madrid', o 'toda españa'.
- municipality: Nombre del municipio (ej: 'Móstoles').
- cnae_prefix: Solo si estás seguro del código CNAE de 2, 3 o 4 dígitos. Ej: 'Restaurantes' o 'Bares' -> '56'. 'Construcción' -> '41'. 'Informática' -> '62'. 'Clínicas dentales' -> '8623'. Si no lo sabes, usa null y rellena cnae_text.
- cnae_text: Texto libre para buscar en la actividad. Ej: 'limpieza', 'marketing'.
- estado: Solo puede ser 'ACTIVA', 'CERRADA' o null. 'que sigan abiertas' -> 'ACTIVA'.
- has_phone: booleano (true/false) si piden que tengan telefono (o null).
- date_min / date_max: Formato YYYY-MM-DD. Usar si el usuario pide empresas creadas recientemente, en un año, o en los últimos meses. Si pide 'últimos 3 meses', calcula aproximadamente y usa date_min. Si pide 'creadas en 2023', usa date_min=2023-01-01 y date_max=2023-12-31.
- Si el usuario dice 'comprar', 'descargar', 'lo quiero', 'checkout', pon is_ready_for_checkout a true.
- reply_text: Debe ser corto, amigable y confirmar qué filtros has aplicado o quitado. No menciones cuántos resultados hay.
- REGLA ESTRICTA DE ENFOQUE: Eres exclusivamente un asistente de prospección B2B. Bajo NINGUNA circunstancia debes responder a preguntas ajenas a empresas, filtros, sectores o negocios. Si el usuario hace preguntas fuera de lugar (ej: chistes, historia, recetas, código), debes denegar amablemente indicando que solo puedes ayudar con los listados de APIEmpresas."
        ];

        array_unshift($chatHistory, $systemPrompt);

        // 2. Llamada a OpenAI con JSON Schema (Structured Outputs)
        $apiKey = env('OPENAI_API_KEY');
        $httpClient = new \GuzzleHttp\Client(['verify' => false]);
        $client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withHttpClient($httpClient)
            ->make();

        try {
            $response = $client->chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => $chatHistory,
                'response_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'b2b_filters',
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'reply_text' => ['type' => 'string'],
                                'filters' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'province' => ['type' => ['string', 'null']],
                                        'municipality' => ['type' => ['string', 'null']],
                                        'cnae_prefix' => ['type' => ['string', 'null']],
                                        'cnae_text' => ['type' => ['string', 'null']],
                                        'estado' => ['type' => ['string', 'null']],
                                        'has_phone' => ['type' => ['boolean', 'null']],
                                        'date_min' => ['type' => ['string', 'null']],
                                        'date_max' => ['type' => ['string', 'null']]
                                    ],
                                    'required' => ['province', 'municipality', 'cnae_prefix', 'cnae_text', 'estado', 'date_min', 'date_max', 'has_phone'],
                                    'additionalProperties' => false
                                ],
                                'is_ready_for_checkout' => ['type' => 'boolean']
                            ],
                            'required' => ['reply_text', 'filters', 'is_ready_for_checkout'],
                            'additionalProperties' => false
                        ],
                        'strict' => true
                    ]
                ]
            ]);

            $jsonString = $response->choices[0]->message->content;
            $aiData = json_decode($jsonString, true);

            // 3. Cruzar filtros con la BD para obtener conteo y muestra
            $db = Database::connect();
            $bList = $db->table('companies');
            $fields = ['company_name', 'address', 'registro_mercantil AS province', 'cnae_label', 'estado'];
            $bList->select($fields);

            $filters = $aiData['filters'];
            
            if (!empty($filters['province'])) {
                $bList->where('registro_mercantil', $filters['province']);
            }
            if (!empty($filters['municipality'])) {
                $bList->like('address', $filters['municipality'], 'both');
            }
            if (!empty($filters['estado'])) {
                $bList->where('estado', $filters['estado']);
            }
            if (!empty($filters['cnae_prefix'])) {
                $bList->like('cnae_code', $filters['cnae_prefix'], 'after');
            } elseif (!empty($filters['cnae_text'])) {
                $bList->groupStart()
                      ->like('cnae_label', $filters['cnae_text'], 'both')
                      ->orLike('objeto_social', $filters['cnae_text'], 'both')
                      ->groupEnd();
            }
            if (!empty($filters['date_min'])) {
                $bList->where('estado_fecha >=', $filters['date_min']);
            }
            if (!empty($filters['date_max'])) {
                $bList->where('estado_fecha <=', $filters['date_max']);
            }
            if (!empty($filters['has_phone'])) {
                $bList->groupStart()
                    ->groupStart()->where('phone IS NOT NULL', null, false)->where('phone !=', '')->groupEnd()
                    ->orGroupStart()->where('phone_mobile IS NOT NULL', null, false)->where('phone_mobile !=', '')->groupEnd()
                    ->groupEnd();
            }

            // Ignoramos el bounding box del mapa para dar resultados exactos basados en el texto
            $totalCount = $bList->countAllResults(false);
            $bList->orderBy('estado_fecha', 'DESC')->limit(3);
            $sampleData = $bList->get()->getResultArray();

            helper('pricing');
            $priceData = calculate_directory_price($totalCount);
            $price = $priceData['base_price'] ?? 9;

            $params = [];
            if (!empty($filters['province'])) $params['provincia'] = $filters['province'];
            if (!empty($filters['cnae_text'])) $params['cnae_text'] = $filters['cnae_text'];
            if (!empty($filters['cnae_prefix'])) $params['cnae'] = $filters['cnae_prefix'];
            if (!empty($filters['estado'])) $params['estado'] = $filters['estado'];
            if (!empty($filters['has_phone'])) $params['has_phone'] = 1;
            if (!empty($filters['date_min'])) $params['date_min'] = $filters['date_min'];
            if (!empty($filters['date_max'])) $params['date_max'] = $filters['date_max'];
            $checkoutUrl = site_url('billing/directory_checkout?' . http_build_query($params));
            
            // Limit on massive downloads
            if ($totalCount > 200000) {
                $aiData['is_ready_for_checkout'] = false;
                $aiData['reply_text'] = "⚠️ Este listado es demasiado grande (" . number_format($totalCount, 0, ',', '.') . " empresas) y supera nuestro límite máximo de seguridad por descarga (200.000). Por favor, acota tu búsqueda añadiendo un municipio, sector o estado específico.";
                $checkoutUrl = null;
            }

            return $this->respond([
                'assistant_message' => $aiData,
                'real_count' => $totalCount,
                'sample_data' => $sampleData,
                'price' => $price,
                'checkout_url' => $checkoutUrl
            ]);

        } catch (\Exception $e) {
            log_message('error', 'ChatAssistant Error: ' . $e->getMessage());
            return $this->fail('Error al procesar con IA', 500);
        }
    }
}
