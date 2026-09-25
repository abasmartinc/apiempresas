<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ApikeysModel;
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
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_27'));
        }

        if ($this->request->getGet('intent')) {
            session()->set('signup_intent', trim((string)$this->request->getGet('intent')));
        }

        // Destino tras entrar (p. ej. "Activar Pro" → billing?plan=pro). Antes se ignoraba
        // y quien se registraba desde un botón de compra acababa en el panel.
        $destino = trim((string) ($this->request->getGet('redirect') ?? ''));
        if ($destino !== '' && $this->destinoSeguro($destino)) {
            session()->set('github_redirect', $destino);
        } else {
            session()->remove('github_redirect');
        }

        // state contra CSRF en el login (LinkedIn ya lo usaba; GitHub no)
        $state = bin2hex(random_bytes(16));
        session()->set('github_state', $state);

        $url = "https://github.com/login/oauth/authorize?" . http_build_query([
            'client_id'    => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope'        => 'user:email',
            'state'        => $state,
        ]);
        return redirect()->to($url);
    }

    public function callback()
    {
        // DEBUG: Loggear todo lo que llega para ver qué está pasando
        log_message('debug', '[GithubAuth] Callback recibido. Parámetros: ' . implode(',', array_keys($_GET)));
        
        $code = $this->request->getGet('code') ?? $_GET['code'] ?? null;
        $error = $this->request->getGet('error') ?? $_GET['error'] ?? null;

        if ($error) {
            log_message('error', '[GithubAuth] Error de GitHub: ' . $error);
            return redirect()->to(site_url('enter'))->with('error', 'GitHub reportó un error: ' . $error);
        }

        if (!$code) {
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_28'));
        }

        $state = (string) ($this->request->getGet('state') ?? '');
        $stateEsperado = (string) (session('github_state') ?? '');
        session()->remove('github_state');
        if ($stateEsperado === '' || !hash_equals($stateEsperado, $state)) {
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_28'));
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
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_29'));
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
            $host = $this->request->getServer('HTTP_HOST') ?? '';
            $lang = (strpos((string)$host, 'spaincompanyapi') !== false) ? 'en' : 'es';
            $intent = session()->get('signup_intent') ?: 'api';
            session()->remove('signup_intent');

            $userData = [
                'name'              => $name,
                'email'             => $email,
                'lang'              => $lang,
                'github_id'         => $githubId,
                'avatar'            => $avatar,
                'is_active'         => 1,
                'api_access'        => 1,
                'source_app'        => 'apiempresas',
                'signup_intent'     => $intent,
                'password_hash'     => password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT), // Password aleatorio
                // Sin esto quedaba 0000-00-00 y la cuenta no entraba en los correos por antigüedad
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ];

            $userId = $this->userModel->insert($userData);
            $user = $this->userModel->find($userId);

            // Asignar Plan Gratuito inicial
            $this->subsModel->insert([
                'user_id'   => $userId,
                'plan_id'   => 1, // Plan Básico/Gratis
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

            \App\Libraries\Embudo::alta((int) $userId, 'github', $intent, (string) (session('github_redirect') ?? ''));

            // Enviar email de bienvenida
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
                log_message('error', '[GithubAuth] Error enviando email bienvenida: ' . $e->getMessage());
            }
        }

        // Iniciar sesión con un identificador de sesión nuevo (evita fijación de sesión)
        $destino = (string) (session('github_redirect') ?? '');
        session()->remove('github_redirect');
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
