<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;
use App\Services\EmailService;
use GuzzleHttp\Client as GuzzleClient;

class LinkedinAuth extends BaseController
{
    protected $userModel;
    protected $subsModel;
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->subsModel = new UsersuscriptionsModel();
        
        $this->clientId     = env('LINKEDIN_CLIENT_ID');
        $this->clientSecret = env('LINKEDIN_CLIENT_SECRET');
        $this->redirectUri  = env('LINKEDIN_REDIRECT_URI');
    }

    public function login()
    {
        if (empty($this->clientId) || empty($this->redirectUri)) {
            return redirect()->to(site_url('enter'))->with('error', 'Error de configuración: Las credenciales de LinkedIn no se han cargado correctamente del .env');
        }

        $state = bin2hex(random_bytes(16));
        session()->set('linkedin_state', $state);

        $url = "https://www.linkedin.com/oauth/v2/authorization?" . http_build_query([
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'state'         => $state,
            'scope'         => 'openid profile email',
        ]);

        return redirect()->to($url);
    }

    public function callback()
    {
        $code  = $this->request->getGet('code') ?? $_GET['code'] ?? null;
        $state = $this->request->getGet('state') ?? $_GET['state'] ?? null;
        $error = $this->request->getGet('error') ?? $_GET['error'] ?? null;

        if ($error) {
            log_message('error', '[LinkedinAuth] Error de LinkedIn: ' . $error);
            return redirect()->to(site_url('enter'))->with('error', 'LinkedIn reportó un error: ' . $error);
        }

        if (!$code) {
            return redirect()->to(site_url('enter'))->with('error', 'No se recibió el código de LinkedIn.');
        }

        if ($state !== session('linkedin_state')) {
            return redirect()->to(site_url('enter'))->with('error', 'Error de seguridad: El estado de la sesión no coincide.');
        }

        try {
            $client = new GuzzleClient();

            // 1. Intercambiar código por Access Token
            $response = $client->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'form_params' => [
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                    'redirect_uri'  => $this->redirectUri,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            $accessToken = $data['access_token'] ?? null;

            if (!$accessToken) {
                throw new \Exception('No se pudo obtener el Access Token de LinkedIn.');
            }

            // 2. Obtener datos del usuario (OpenID Connect)
            $userResponse = $client->get('https://api.linkedin.com/v2/userinfo', [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                ]
            ]);

            $linkedinUser = json_decode($userResponse->getBody(), true);
            
            $linkedinId = $linkedinUser['sub']; // 'sub' es el ID único en OpenID
            $name       = $linkedinUser['name'] ?? ($linkedinUser['given_name'] . ' ' . $linkedinUser['family_name']);
            $email      = $linkedinUser['email'] ?? null;
            $avatar     = $linkedinUser['picture'] ?? null;

            if (!$email) {
                throw new \Exception('No se pudo obtener el email de tu cuenta de LinkedIn.');
            }

            return $this->loginUser($linkedinId, $email, $name, $avatar);

        } catch (\Exception $e) {
            log_message('error', '[LinkedinAuth] Error en callback: ' . $e->getMessage());
            return redirect()->to(site_url('enter'))->with('error', 'Error durante la autenticación con LinkedIn: ' . $e->getMessage());
        }
    }

    private function loginUser($linkedinId, $email, $name, $avatar)
    {
        $email = strtolower($email);

        // 1. Buscar por linkedin_id
        $user = $this->userModel->where('linkedin_id', $linkedinId)->first();

        // 2. Si no existe por ID, buscar por email (vincular cuenta)
        if (!$user) {
            $user = $this->userModel->where('email', $email)->first();
            if ($user) {
                $this->userModel->update($user->id, [
                    'linkedin_id' => $linkedinId,
                    'avatar'      => $user->avatar ?: $avatar
                ]);
            }
        }

        // 3. Si sigue sin existir, crear usuario nuevo
        if (!$user) {
            $apiKey = 'sk_' . bin2hex(random_bytes(16));
            $userData = [
                'name'          => $name,
                'email'         => $email,
                'linkedin_id'   => $linkedinId,
                'avatar'        => $avatar,
                'api_key'       => $apiKey,
                'is_active'     => 1,
                'api_access'    => 1,
                'source_app'    => 'apiempresas',
                'password_hash' => password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT)
            ];

            $userId = $this->userModel->insert($userData);
            $user = $this->userModel->find($userId);

            // Plan Gratuito
            $this->subsModel->insert([
                'user_id'   => $userId,
                'plan_id'   => 1,
                'status'    => 'active',
                'starts_at' => date('Y-m-d H:i:s'),
                'ends_at'   => date('Y-m-d H:i:s', strtotime('+100 years'))
            ]);

            // Email de bienvenida
            try {
                $emailService = new EmailService();
                $emailService->sendWelcomeEmail((array)$user, $apiKey);
            } catch (\Exception $e) {
                log_message('error', '[LinkedinAuth] Error enviando email: ' . $e->getMessage());
            }
        }

        // Iniciar sesión
        session()->set([
            'user_id'     => $user->id,
            'user_email'  => $user->email,
            'user_name'   => $user->name,
            'user_avatar' => $user->avatar,
            'is_admin'    => $user->is_admin,
            'logged_in'   => true,
        ]);

        return redirect()->to(site_url('dashboard'));
    }
}
