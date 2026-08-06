<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;
use App\Models\AiCopilotLogModel;
use App\Services\OpenAiService;
use CodeIgniter\Controller;
use OpenAI;

class AiCopilotController extends Controller
{
    /**
     * Endpoint to generate a sales script using AI.
     */
    public function generate()
    {
        $isLoggedIn = session('logged_in');
        $ip = $this->request->getIPAddress();
        $cache = \Config\Services::cache();
        $cacheKey = "ai_guest_trial_" . str_replace(':', '_', $ip);

        $userId = session('user_id');
        $cif = $this->request->getPost('cif');
        $rawProduct = $this->request->getPost('product');
        $modifier = $this->request->getPost('modifier');
        $targetRole = $this->request->getPost('target_role') ?: 'CEO';
        
        // Append role to product for caching and context purposes
        $product = $rawProduct . " (Dirigido a: " . $targetRole . ")";

        if (empty($cif) || empty($product)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Faltan parámetros obligatorios.'])->setStatusCode(400);
        }

        $currentPlanSlug = 'free';
        $user = null;

        if ($isLoggedIn) {
            // 1. Check User Plan & Trials
            $userModel = new UserModel();
            $user = $userModel->find($userId);
            
            $subModel = new UsersuscriptionsModel();
            $plan = $subModel->getActivePlanByUserId($userId);
            
            if ($plan) {
                $currentPlanSlug = $plan->plan_slug ?? strtolower(trim($plan->plan_name));
            }

            // Check trials via logs instead of user table
            $logModel = new AiCopilotLogModel();
            $trialsUsed = $logModel->where('user_id', $userId)->countAllResults();
            $hasAccess = in_array($currentPlanSlug, ['business', 'agencia', 'copiloto_ventas']) || ($trialsUsed < 5);

            if (!$hasAccess) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'premium_required'])->setStatusCode(403);
            }
        } else {
            // Check Guest IP limit via logs
            $logModel = new AiCopilotLogModel();
            $trialsUsed = $logModel->where('ip_address', $ip)->where('user_id', null)->countAllResults();
            if ($trialsUsed >= 1) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'guest_limit_reached'])->setStatusCode(403);
            }
        }

        // 2. Fetch Company Data
        $companyModel = new CompanyModel();
        $company = $companyModel->getByCif($cif);

        if (!$company) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Empresa no encontrada.'])->setStatusCode(404);
        }

        // 3. Fetch scoring data to calculate deterministic match score (syncs Copilot with API endpoint)
        $db = \Config\Database::connect();
        $scoring = $db->table('company_radar_scores')
            ->where('company_id', $company['id'])
            ->get()->getRowArray();
        if ($scoring) {
            $company = array_merge($company, $scoring);
        }
        $matchResult = \App\Libraries\RadarAnalyzer::calculateMatch($company, $product);
        $hardcodedScore = $matchResult['match_score'];

        // 3.5 Check Cache (Only if no modifier is requested)
        $cachedLog = null;
        $logModel = new AiCopilotLogModel();
        if (empty($modifier)) {
            $cachedLog = $logModel->where('company_cif', $cif)
                ->where('product_input', $product)
                ->where('score', $hardcodedScore)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                ->orderBy('id', 'DESC')
                ->first();
        }

        $adminModel = new \App\Models\CompanyAdministratorModel();
        $admins = $adminModel->getByCompanyId($company['id']);
        $adminData = "";
        if (!empty($admins)) {
            $adminData = implode(", ", array_slice(array_column($admins, 'name'), 0, 3));
        }
        $bestPhone = $company['phone_enriched'] ?? $company['phone_mobile_enriched'] ?? $company['phone'] ?? $company['phone_mobile'] ?? '';
        if (!empty($bestPhone)) {
            $cleanPhone = str_replace([' ', '-', '.'], '', $bestPhone);
            if (preg_match('/(?:(?:\+|00)34)?[6789]\d{8}/', $cleanPhone, $matches)) {
                $bestPhone = $matches[0];
            }
        }

        if ($cachedLog && !empty($cachedLog['ai_response_json'])) {
            $parsedResult = json_decode($cachedLog['ai_response_json'], true);
            if ($parsedResult) {
                // Calculate trials left
                $trialsLeft = 'ilimitado';
                if ($isLoggedIn) {
                    if (!in_array($currentPlanSlug, ['business', 'agencia', 'copiloto_ventas'])) {
                        $trialsLeft = 5 - ($trialsUsed + 1);
                    }
                } else {
                    $trialsLeft = 0;
                }

                // Insert a new log to consume trial and track history
                $logModel->insert([
                    'user_id' => $isLoggedIn ? $userId : null,
                    'ip_address' => $ip,
                    'company_cif' => $cif,
                    'product_input' => $product,
                    'ai_response_json' => $cachedLog['ai_response_json'],
                    'score' => $hardcodedScore,
                    'is_useful' => null
                ]);
                $logId = $logModel->getInsertID();

                return $this->response->setJSON([
                    'status' => 'success',
                    'dossier' => $parsedResult,
                    'admins' => $adminData,
                    'phone' => $bestPhone,
                    'trials_left' => $trialsLeft,
                    'log_id' => $logId,
                    'cached' => true
                ]);
            }
        }


        // 4. Fetch Contracts Data
        $contracts = $db->table('company_contracts')
            ->where('company_cif', $cif)
            ->orderBy('fecha_adjudicacion', 'DESC')
            ->limit(3)
            ->get()->getResultArray();
        
        $totalContracts = $db->table('company_contracts')->where('company_cif', $cif)->selectSum('importe_adjudicacion')->get()->getRow()->importe_adjudicacion ?? 0;

        $contractsData = "";
        if (!empty($contracts)) {
            $contractsData = "Han ganado licitaciones públicas recientemente por un total histórico de " . number_format($totalContracts, 0, ',', '.') . "€. Últimos contratos: ";
            $cRows = [];
            foreach ($contracts as $c) {
                $cRows[] = $c['titulo_contrato'] . " (" . number_format($c['importe_adjudicacion'], 0, ',', '.') . "€)";
            }
            $contractsData .= implode("; ", $cRows) . ".";
        }

        // 5. Fetch Subsidies Data
        $subsidies = $db->table('company_subsidies')
            ->where('company_cif', $cif)
            ->orderBy('fecha_concesion', 'DESC')
            ->limit(3)
            ->get()->getResultArray();
            
        $totalSubsidies = $db->table('company_subsidies')->where('company_cif', $cif)->selectSum('importe')->get()->getRow()->importe ?? 0;
        
        $subsidiesData = "";
        if (!empty($subsidies)) {
            $subsidiesData = "Han recibido subvenciones/ayudas recientemente por un total histórico de " . number_format($totalSubsidies, 0, ',', '.') . "€. Últimas ayudas: ";
            $sRows = [];
            foreach ($subsidies as $s) {
                $sRows[] = $s['convocatoria'] . " (" . number_format($s['importe'], 0, ',', '.') . "€)";
            }
            $subsidiesData .= implode("; ", $sRows) . ".";
        }

        // 6. Prepare prompt data
        $bormeData = $company['ai_borme_summary'] ?? "";

        // $adminModel and $adminData moved to cache check section


        $prompt = "Contexto de la empresa:\n- Nombre: {$company['name']}\n- Sector/Actividad: " . ($company['cnae_label'] ?? 'Desconocido') . "\n";
        if (!empty($company['founded'])) $prompt .= "- Año de fundación: " . substr($company['founded'], 0, 4) . "\n";
        if (!empty($company['capital_social_raw']) && (float)$company['capital_social_raw'] > 0) $prompt .= "- Capital Social: " . number_format((float)$company['capital_social_raw'], 0, ',', '.') . "€\n";
        if (!empty($company['province'])) $prompt .= "- Ubicación: {$company['municipality']} ({$company['province']})\n";
        if (!empty($company['website_official'])) $prompt .= "- Web: {$company['website_official']}\n";
        if (!empty($company['corporate_purpose'])) $prompt .= "- Objeto Social: {$company['corporate_purpose']}\n";
        if (!empty($company['ai_pitch'])) $prompt .= "- A qué se dedican (Pitch comercial): {$company['ai_pitch']}\n";
        
        if (!empty($adminData)) $prompt .= "- Directivos: {$adminData}\n";
        
        // BORME Fallback logic
        if (!empty($bormeData)) {
            $prompt .= "- Últimos eventos (BORME): {$bormeData}\n";
        } else {
            $bormeModel = new \App\Models\BormePostsModel();
            $recentBorme = $bormeModel->where('company_id', $company['id'])
                ->where('borme_date >=', date('Y-m-d', strtotime('-1 year')))
                ->orderBy('borme_date', 'DESC')
                ->limit(5)
                ->findAll();
                
            if (!empty($recentBorme)) {
                $bormeRawData = [];
                foreach ($recentBorme as $post) {
                    $bormeRawData[] = "{$post['borme_date']} - {$post['act_types']}: {$post['description']}";
                }
                $prompt .= "- Últimos eventos crudos (BORME): " . implode(" | ", $bormeRawData) . "\n";
            }
        }
        
        if (!empty($contractsData)) $prompt .= "- Contratos Públicos: {$contractsData}\n";
        if (!empty($subsidiesData)) $prompt .= "- Subvenciones Recibidas: {$subsidiesData}\n";
        
        $prompt .= "\nYo soy el SDR y vendo el siguiente producto/servicio: {$product}.\n\n";
        $prompt .= "Instrucciones estrictas:\n";
        $prompt .= "1. Actúa como un Director Comercial (CRO) implacable y experto en cierre B2B en España.\n";
        $prompt .= "2. TONO: PROHIBIDO usar palabras débiles como 'podría', 'creo', 'quizás', 'intentaremos'. Usa lenguaje imperativo, asertivo y de autoridad. Ve al grano, sin florituras. Escribe mensajes que le dejen claro al comercial EXACTAMENTE qué tiene que hacer.\n";
        $prompt .= "3. TRIGGER: Ajusta la orden inicial según el score de {$hardcodedScore}. Si es < 65 empieza diciendo 'NO LLAMES AHORA, no hay encaje claro. Si decides llamar, usa la excusa de...'. Si es >= 65 empieza con 'LLAMA AHORA porque...'. Usa siempre importes exactos o el Objeto Social como excusa dura.\n";
        $prompt .= "4. COLD CALL: El guion debe ser un 'pattern interrupt' agresivo pero educado. Nada de '¿Cómo estáis?'. Debe ser: 'Hola [Nombre], te llamo porque he visto [Trigger]. ¿Estáis perdiendo dinero por [Pain Point] o ya lo tenéis cubierto?'. Corto y a matar.\n";
        
        // Role Injection
        if ($targetRole !== 'Genérico') {
            $prompt .= "REGLA DE ROL: El interlocutor es el {$targetRole}. Adapta los beneficios, dolores (pain points) y el enfoque a las prioridades típicas de este perfil directivo.\n";
        }
        
        // Name Injection
        if (in_array($targetRole, ['CEO', 'Genérico']) && !empty($adminData)) {
            $prompt .= "REGLA DE NOMBRE: He detectado que los directivos de esta empresa son: {$adminData}. Usa obligatoriamente uno de estos nombres reales para el saludo del email y el guion telefónico en lugar de usar '[Nombre]'.\n";
        } else {
            $prompt .= "REGLA DE NOMBRE: Usa '[Nombre del {$targetRole}]' como marcador para el saludo si no te he dado nombres.\n";
        }

        if (!empty($modifier)) {
            switch ($modifier) {
                case 'agresivo':
                    $prompt .= "REGLA ABSOLUTA: Haz que el tono sea extremadamente asertivo, agresivo comercialmente y presionando al cierre inmediato.\n";
                    break;
                case 'corto':
                    $prompt .= "REGLA ABSOLUTA: Reduce la longitud del guion y de los emails a la mitad. Ve directa y agresivamente al grano, eliminando cualquier frase de relleno.\n";
                    break;
                case 'precio':
                    $prompt .= "REGLA ABSOLUTA: Cambia el enfoque principal para centrarte en la pérdida de dinero, el ahorro de costes y el ROI inmediato. Habla de números.\n";
                    break;
                case 'suave':
                    $prompt .= "REGLA ABSOLUTA: Cambia a un tono más consultivo, empático, suave y enfocado en construir relación y aportar valor a largo plazo, sin perder el objetivo de venta.\n";
                    break;
            }
        }

        $prompt .= "5. Debes devolver tu respuesta EXCLUSIVAMENTE en formato JSON válido con esta estructura exacta:\n";
        $prompt .= "{\n";
        $prompt .= "  \"trigger_event\": \"(1-2 líneas EMPEZANDO CON LA ORDEN de llamar o no llamar según el score de {$hardcodedScore}, seguido de la excusa dura de venta)\",\n";
        $prompt .= "  \"pain_points\": \"(El dolor principal expresado de forma cruda, ej: 'Están perdiendo cuota de mercado local por no tener SEO')\",\n";
        $prompt .= "  \"score\": {$hardcodedScore}, // ¡IMPORTANTE! USA ESTE NÚMERO EXACTAMENTE ({$hardcodedScore}), NO LO CAMBIES NI CALCULES OTRO.\n";
        $prompt .= "  \"score_insight\": \"(Veredicto final sobre el score de {$hardcodedScore}. Si es <65 pon '🔴 DESCARTAR / CUIDADO: [Razón]'. Si es 65-79 pon '🟡 NUTRIR: [Razón]'. Si es >=80 pon '🟢 PRIORIDAD MÁXIMA: [Razón]. Ve a por el cierre hoy mismo'.)\",\n";
        $prompt .= "  \"cold_call\": \"(Guion de llamada en frío hiper-corto, agresivo y directo, sin saludos largos)\",\n";
        $prompt .= "  \"linkedin\": \"(Mensaje directo de LinkedIn al cuello pero profesional)\",\n";
        $prompt .= "  \"objections\": [\n";
        $prompt .= "      { \"excuse\": \"(Objeción 1 muy probable, ej. 'Ya tenemos proveedor')\", \"rebuttal\": \"(Contra-argumento directo y ganador para rebatirla)\" },\n";
        $prompt .= "      { \"excuse\": \"(Objeción 2 muy probable)\", \"rebuttal\": \"(Contra-argumento)\" },\n";
        $prompt .= "      { \"excuse\": \"(Objeción 3 muy probable)\", \"rebuttal\": \"(Contra-argumento)\" }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"email_subjects\": [\n";
        $prompt .= "      \"(Asunto opción 1: Directo y al grano)\",\n";
        $prompt .= "      \"(Asunto opción 2: Despierta curiosidad/misterio)\",\n";
        $prompt .= "      \"(Asunto opción 3: Centrado en pérdida económica o dolor)\"\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"email_1\": \"(Cuerpo del primer correo yendo al grano de la pérdida de dinero/tiempo, sin asunto)\",\n";
        $prompt .= "  \"email_2\": \"(Email de follow-up corto presionando)\"\n";
        $prompt .= "}\n";

        // 7. Call OpenAI
        try {
            $apiKey = env('OPENAI_API_KEY') ?? '';
            $httpClient = new \GuzzleHttp\Client(['verify' => false]);
            $client = OpenAI::factory()
                ->withApiKey($apiKey)
                ->withHttpClient($httpClient)
                ->make();

            $response = $client->chat()->create([
                'model' => 'gpt-4o',
                'temperature' => 0.4,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un sistema de Inteligencia de Cuentas B2B. Tu salida debe ser siempre un JSON válido.'],
                    ['role' => 'user', 'content' => iconv('UTF-8', 'UTF-8//IGNORE', mb_convert_encoding($prompt, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252'))]
                ],
            ]);

            $jsonResult = $response->choices[0]->message->content;
            $parsedResult = json_decode($jsonResult, true);
            
            if (!$parsedResult) {
                throw new \Exception("OpenAI no devolvió un JSON válido.");
            }

            $trialsLeft = 'ilimitado';
            if ($isLoggedIn) {
                if (!in_array($currentPlanSlug, ['business', 'agencia', 'copiloto_ventas'])) {
                    $trialsLeft = 5 - ($trialsUsed + 1); // Substracting the one we are about to insert
                }
            } else {
                $trialsLeft = 0; // Guest used their 1 trial
            }

            $logModel = new AiCopilotLogModel(); // Use the existing one instantiated earlier
            $logModel->insert([
                'user_id' => $isLoggedIn ? $userId : null,
                'ip_address' => $ip,
                'company_cif' => $cif,
                'product_input' => $product,
                'ai_response_json' => json_encode($parsedResult, JSON_UNESCAPED_UNICODE),
                'score' => $hardcodedScore
            ]);
            $logId = $logModel->getInsertID();

            return $this->response->setJSON([
                'status' => 'success',
                'dossier' => $parsedResult,
                'admins' => $adminData,
                'phone' => $bestPhone,
                'trials_left' => $trialsLeft,
                'log_id' => $logId,
                'cached' => false
            ]);

        } catch (\Exception $e) {
            log_message('error', 'AI Copilot Error: ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error al contactar con la IA.'])->setStatusCode(500);
        }
    }

    /**
     * Endpoint to email the latest generated dossier to the logged-in user.
     */
    public function emailDossier()
    {
        $userId = session('user_id');
        $cif = $this->request->getPost('cif');

        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Debes iniciar sesión para enviarte resúmenes por correo.'])->setStatusCode(401);
        }

        if (empty($cif)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Falta el CIF de la empresa.'])->setStatusCode(400);
        }

        // Fetch user data
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        if (!$user || empty($user->email)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Usuario no válido.'])->setStatusCode(404);
        }

        // Fetch company data for the email subject
        $companyModel = new CompanyModel();
        $company = $companyModel->getByCif($cif);
        $companyName = $company ? $company['name'] : $cif;

        // Fetch the latest log for this user and CIF
        $logModel = new AiCopilotLogModel();
        $log = $logModel->where('user_id', $userId)
                        ->where('company_cif', $cif)
                        ->orderBy('id', 'DESC')
                        ->first();

        if (!$log || empty($log['ai_response_json'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No se ha encontrado el análisis para esta empresa.'])->setStatusCode(404);
        }

        $dossier = json_decode($log['ai_response_json'], true);

        // Build the email
        $email = \Config\Services::email();
        $email->setTo($user->email);
        $email->setFrom('alertas@apiempresas.es', 'API Empresas Copiloto');
        $email->setSubject('💡 Tu Guion de Ventas para ' . $companyName);

        $body = view('emails/copilot_dossier', [
            'user' => $user,
            'companyName' => $companyName,
            'cif' => $cif,
            'dossier' => $dossier,
            'product' => $log['product_input'] ?? ''
        ]);

        $email->setMessage($body);

        if ($email->send()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Email enviado correctamente.']);
        } else {
            log_message('error', 'Error sending Copilot email: ' . $email->printDebugger(['headers']));
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error al enviar el correo.'])->setStatusCode(500);
        }
    }

    /**
     * Endpoint to save user feedback (Useful / Not useful) on the generated dossier.
     */
    public function submitFeedback()
    {
        $logId = $this->request->getPost('log_id');
        $feedbackScore = (int)$this->request->getPost('feedback_score'); // 1 (useful) or -1 (not useful)

        if (empty($logId) || !in_array($feedbackScore, [1, -1])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Datos inválidos.'])->setStatusCode(400);
        }

        $logModel = new AiCopilotLogModel();
        $log = $logModel->find($logId);

        if (!$log) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Log no encontrado.'])->setStatusCode(404);
        }

        // Optional: verify if it belongs to the logged in user or the same IP
        $userId = session('user_id');
        if ($log['user_id'] && $log['user_id'] != $userId) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado.'])->setStatusCode(403);
        }

        $logModel->update($logId, ['feedback_score' => $feedbackScore]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Feedback guardado con éxito.']);
    }
}
