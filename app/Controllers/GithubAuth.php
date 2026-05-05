<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;
use App\Services\EmailService;
use GuzzleHttp\Client as GuzzleClient;

class GithubAuth extends BaseController
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
        
        $this->clientId     = env('GITHUB_CLIENT_ID');
        $this->clientSecret = env('GITHUB_CLIENT_SECRET');
        $this->redirectUri  = env('GITHUB_REDIRECT_URI');
    }

    public function login()
    {
        if (empty($this->clientId) || empty($this->redirectUri)) {
            return redirect()->to(site_url('enter'))->with('error', 'Error de configuración: Las credenciales de GitHub no se han cargado correctamente del .env');
        }

        $url = "https://github.com/login/oauth/authorize?client_id={$this->clientId}&redirect_uri={$this->redirectUri}&scope=user:email";
        return redirect()->to($url);
    }

    public function callback()
    {
        // DEBUG: Loggear todo lo que llega para ver qué está pasando
        log_message('error', '[GithubAuth] Callback recibido. Query params: ' . json_encode($_GET));
        
        $code = $this->request->getGet('code') ?? $_GET['code'] ?? null;
        $error = $this->request->getGet('error') ?? $_GET['error'] ?? null;

        if ($error) {
            log_message('error', '[GithubAuth] Error de GitHub: ' . $error);
            return redirect()->to(site_url('enter'))->with('error', 'GitHub reportó un error: ' . $error);
        }

        if (!$code) {
            return redirect()->to(site_url('enter'))->with('error', 'No se recibió el código de GitHub (Revisa los logs para ver qué llegó).');
        }

        try {
            $client = new GuzzleClient();

            // 1. Intercambiar código por Access Token
            $response = $client->post('https://github.com/login/oauth/access_token', [
                'form_params' => [
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'code'          => $code,
                    'redirect_uri'  => $this->redirectUri,
                ],
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            $accessToken = $data['access_token'] ?? null;

            if (!$accessToken) {
                throw new \Exception('No se pudo obtener el Access Token de GitHub.');
            }

            // 2. Obtener datos del usuario
            $userResponse = $client->get('https://api.github.com/user', [
                'headers' => [
                    'Authorization' => "token {$accessToken}",
                    'User-Agent'    => 'APIEmpresas-App'
                ]
            ]);

            $githubUser = json_decode($userResponse->getBody(), true);
            $githubId   = $githubUser['id'];
            $name       = $githubUser['name'] ?? $githubUser['login'];
            $avatar     = $githubUser['avatar_url'] ?? null;
            $email      = $githubUser['email'] ?? null;

            // 3. Si el email es null (común en GitHub), pedir los emails específicamente
            if (!$email) {
                $emailsResponse = $client->get('https://api.github.com/user/emails', [
                    'headers' => [
                        'Authorization' => "token {$accessToken}",
                        'User-Agent'    => 'APIEmpresas-App'
                    ]
                ]);
                $emails = json_decode($emailsResponse->getBody(), true);
                foreach ($emails as $e) {
                    if ($e['primary'] && $e['verified']) {
                        $email = $e['email'];
                        break;
                    }
                }
            }

            if (!$email) {
                throw new \Exception('No se pudo obtener un email verificado de tu cuenta de GitHub.');
            }

            return $this->loginUser($githubId, $email, $name, $avatar);

        } catch (\Exception $e) {
            log_message('error', '[GithubAuth] Error en callback: ' . $e->getMessage());
            return redirect()->to(site_url('enter'))->with('error', 'Error durante la autenticación con GitHub.');
        }
    }

    private function loginUser($githubId, $email, $name, $avatar)
    {
        $email = strtolower($email);

        // 1. Buscar por github_id
        $user = $this->userModel->where('github_id', $githubId)->first();

        // 2. Si no existe por ID, buscar por email (vincular cuenta)
        if (!$user) {
            $user = $this->userModel->where('email', $email)->first();
            if ($user) {
                // Vincular cuenta existente con GitHub
                $this->userModel->update($user->id, [
                    'github_id' => $githubId,
                    'avatar'    => $user->avatar ?: $avatar
                ]);
            }
        }

        // 3. Si sigue sin existir, crear usuario nuevo
        if (!$user) {
            $apiKey = 'sk_' . bin2hex(random_bytes(16));
            $userData = [
                'name'          => $name,
                'email'         => $email,
                'github_id'     => $githubId,
                'avatar'        => $avatar,
                'api_key'       => $apiKey,
                'is_active'     => 1,
                'source_app'    => 'github_auth',
                'password_hash' => password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT) // Password aleatorio
            ];

            $userId = $this->userModel->insert($userData);
            $user = $this->userModel->find($userId);

            // Asignar Plan Gratuito inicial
            $this->subsModel->insert([
                'user_id'   => $userId,
                'plan_id'   => 1, // Plan Básico/Gratis
                'status'    => 'active',
                'starts_at' => date('Y-m-d H:i:s'),
                'ends_at'   => date('Y-m-d H:i:s', strtotime('+100 years'))
            ]);

            // Enviar email de bienvenida
            try {
                $emailService = new EmailService();
                // Convertimos el objeto $user a array para que EmailService no explote
                $emailService->sendWelcomeEmail((array)$user, $apiKey);
            } catch (\Exception $e) {
                log_message('error', '[GithubAuth] Error enviando email bienvenida: ' . $e->getMessage());
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
