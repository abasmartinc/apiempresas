<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ApikeysModel;
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
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_32'));
        }

        if ($this->request->getGet('intent')) {
            session()->set('signup_intent', trim((string)$this->request->getGet('intent')));
        }

        // Destino tras entrar (p. ej. "Activar Pro" → billing?plan=pro). Antes se ignoraba
        // y quien se registraba desde un botón de compra acababa en el panel.
        $destino = trim((string) ($this->request->getGet('redirect') ?? ''));
        if ($destino !== '' && $this->destinoSeguro($destino)) {
            session()->set('linkedin_redirect', $destino);
        } else {
            session()->remove('linkedin_redirect');
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
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_33'));
        }

        if ($state !== session('linkedin_state')) {
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_34'));
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
            $intent = session()->get('signup_intent') ?: 'api';
            session()->remove('signup_intent');

            $userData = [
                'name'              => $name,
                'email'             => $email,
                'linkedin_id'       => $linkedinId,
                'avatar'            => $avatar,
                'is_active'         => 1,
                'api_access'        => 1,
                'source_app'        => 'apiempresas',
                'signup_intent'     => $intent,
                'password_hash'     => password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT),
                // Sin esto quedaba 0000-00-00 y la cuenta no entraba en los correos por antigüedad
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $userId = $this->userModel->insert($userData);
            $user = $this->userModel->find($userId);

            // Plan Gratuito
            $this->subsModel->insert([
                'user_id'   => $userId,
                'plan_id'   => 1,
                'status'               => 'active',
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end'   => date('Y-m-d H:i:s', strtotime('+1 month')),
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ]);

            // API Key en api_keys, que es la tabla que leen el panel y la API. Antes se
            // guardaba en users.api_key (que nadie lee): el usuario se quedaba sin clave
            // válida. La suscripción usaba starts_at/ends_at, columnas que no existen.
            (new ApikeysModel())->insert([
                'user_id'    => $userId,
                'name'       => 'Default API Key',
                'api_key'    => bin2hex(random_bytes(32)),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            \App\Libraries\Embudo::alta((int) $userId, 'linkedin', $intent, (string) (session('linkedin_redirect') ?? ''));

            // Email de bienvenida
            try {
                $emailService = new EmailService();
                $userArr = (array)$user;
                $userArr['signup_intent'] = $intent;
                if ($intent === 'view_risk_profile') {
                    $emailService->sendRiskWelcomeEmail($userArr);
                } elseif ($intent === 'api') {
                    $emailService->sendWelcomeEmail($userArr);
                }
            } catch (\Exception $e) {
                log_message('error', '[LinkedinAuth] Error enviando email: ' . $e->getMessage());
            }
        }

        // Iniciar sesión con un identificador de sesión nuevo (evita fijación de sesión)
        $destino = (string) (session('linkedin_redirect') ?? '');
        session()->remove('linkedin_redirect');
        session()->regenerate();
        session()->set([
            'user_id'     => $user->id,
            'user_email'  => $user->email,
            'user_name'   => $user->name,
            'user_avatar' => $user->avatar,
            'is_admin'    => $user->is_admin,
            'logged_in'   => true,
        ]);

        if ($destino !== '' && $this->destinoSeguro($destino)) {
            return redirect()->to(site_url($destino));
        }
        return redirect()->to(site_url('dashboard'));
    }

    /**
     * Destino interno seguro tras el alta/login (mismo criterio que GoogleAuth): ruta
     * relativa del sitio, sin esquema ni barra inicial.
     */
    private function destinoSeguro(string $ruta): bool
    {
        $ruta = trim($ruta);
        if ($ruta === '' || $ruta[0] === '/' || strpos($ruta, '\\') !== false) {
            return false;
        }
        $primeraBarra = strpos($ruta, '/');
        $cabeza = $primeraBarra === false ? $ruta : substr($ruta, 0, $primeraBarra);
        return strpos($cabeza, ':') === false;
    }
}
