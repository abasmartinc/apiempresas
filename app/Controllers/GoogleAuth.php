<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ApikeysModel;
use App\Models\UsersuscriptionsModel;
use App\Services\EmailService;
use Google\Client as GoogleClient;

class GoogleAuth extends BaseController
{
    protected $userModel;
    protected $apiKeyModel;
    protected $subscriptionModel;
    protected $emailService;
    protected $googleClient;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->apiKeyModel = new ApikeysModel();
        $this->subscriptionModel = new UsersuscriptionsModel();
        $this->emailService = new EmailService();

        // Configurar Google Client
        $this->googleClient = new GoogleClient();
        $this->googleClient->setClientId(env('google.client_id'));
        $this->googleClient->setClientSecret(env('google.client_secret'));
        $this->googleClient->setRedirectUri(env('google.redirect_uri'));
        $this->googleClient->addScope("email");
        $this->googleClient->addScope("profile");
    }

    /**
     * Redirige al usuario a la página de login de Google
     */
    public function login()
    {
        return redirect()->to($this->googleClient->createAuthUrl());
    }

    /**
     * Maneja la respuesta de Google
     */
    public function callback()
    {
        $code = $this->request->getGet('code');

        if (!$code) {
            return redirect()->to(site_url('enter'))->with('error', 'No se ha podido autenticar con Google.');
        }

        try {
            $token = $this->googleClient->fetchAccessTokenWithAuthCode($code);
            
            if (isset($token['error'])) {
                throw new \Exception('Error al obtener el token: ' . $token['error_description']);
            }

            $this->googleClient->setAccessToken($token['access_token']);

            // Obtener info del usuario usando Guzzle (más ligero que Google Services)
            $httpClient = new \GuzzleHttp\Client();
            $response = $httpClient->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token['access_token']
                ]
            ]);
            $userInfo = json_decode($response->getBody());

            if (!$userInfo || !isset($userInfo->email)) {
                throw new \Exception('No se pudo obtener la información del usuario de Google.');
            }

            $email = strtolower($userInfo->email);
            $googleId = $userInfo->sub; // Google usa 'sub' como ID único en su API v3
            $name = $userInfo->name;
            $picture = $userInfo->picture ?? null;

            // 1. Buscar por google_id
            $user = $this->userModel->where('google_id', $googleId)->first();

            if (!$user) {
                // 2. Buscar por email (para vincular cuentas existentes)
                $user = $this->userModel->where('email', $email)->first();

                if ($user) {
                    // Vincular cuenta existente
                    $this->userModel->update($user->id, [
                        'google_id' => $googleId,
                        'avatar'    => $picture,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                } else {
                    // 3. Crear nuevo usuario (Registro rápido)
                    $user_id = $this->createNewGoogleUser($email, $name, $googleId, $picture);
                    $user = $this->userModel->find($user_id);
                }
            } else {
                // Actualizar avatar por si ha cambiado
                $this->userModel->update($user->id, [
                    'avatar' => $picture,
                    'last_login_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // 4. Iniciar sesión
            $this->loginUser($user);

            return redirect()->to(site_url('dashboard'))->with('success', '¡Bienvenido de nuevo, ' . $user->name . '!');

        } catch (\Throwable $e) {
            log_message('error', '[GoogleAuth] Error en callback: ' . $e->getMessage());
            return redirect()->to(site_url('enter'))->with('error', 'Ha ocurrido un error durante la autenticación.');
        }
    }

    /**
     * Crea un nuevo usuario desde el flujo de Google
     */
    private function createNewGoogleUser($email, $name, $googleId, $picture)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Datos del usuario
            $userData = [
                'name'          => $name,
                'email'         => $email,
                'google_id'     => $googleId,
                'avatar'        => $picture,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), // Pass aleatoria
                'is_active'     => 1,
                'api_access'    => 1,
                'source_app'    => 'apiempresas',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
                'last_login_at' => date('Y-m-d H:i:s'),
            ];

            $user_id = $this->userModel->insert($userData);

            // Crear API Key
            $this->apiKeyModel->insert([
                'user_id'    => $user_id,
                'name'       => 'Default API Key',
                'api_key'    => bin2hex(random_bytes(32)),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Crear Suscripción Gratis
            $this->subscriptionModel->insert([
                'user_id'              => $user_id,
                'plan_id'              => 1, // Plan gratuito
                'status'               => 'active',
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end'   => date('Y-m-d H:i:s', strtotime('+1 month')),
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ]);

            // Notificaciones
            $this->emailService->sendRegistrationAdminNotification([
                'user_id' => $user_id,
                'name'    => $name,
                'email'   => $email,
                'company' => 'Google Signup'
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de base de datos.');
            }

            log_activity('register_google', ['email' => $email], $user_id);

            return $user_id;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Establece la sesión del usuario
     */
    private function loginUser($user)
    {
        session()->regenerate();
        session()->set([
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'user_name'  => $user->name,
            'user_avatar' => $user->avatar,
            'is_admin'   => $user->is_admin,
            'logged_in'  => true,
        ]);

        $this->userModel->update($user->id, [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
