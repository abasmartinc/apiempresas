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
        $this->googleClient->setRedirectUri($this->uriDeRetorno());
        $this->googleClient->addScope("email");
        $this->googleClient->addScope("profile");
    }

    /**
     * A dónde tiene que devolver Google al usuario.
     *
     * `google.redirect_uri` del .env apunta a producción
     * (https://apiempresas.es/auth/google/callback). Trabajando en local eso
     * significa que Google devuelve al usuario a PRODUCCIÓN: otro dominio, otra
     * sesión y un servidor que no sabe nada de lo que estaba haciendo. De ahí
     * salían las dos cosas raras a la vez — acabar en apiempresas.es Y ver el
     * wizard de la API, porque allí el callback no tenía ni intención ni destino.
     *
     * Así que la del .env solo se usa cuando su host es el mismo que está
     * sirviendo esta petición. Si no, se construye con site_url() y el usuario
     * vuelve al sitio del que salió.
     *
     * OJO: la URL que se acabe usando TIENE que estar dada de alta en Google
     * Cloud Console como «URI de redireccionamiento autorizado», o Google
     * contesta redirect_uri_mismatch. Para local hay que añadir:
     *
     *     http://localhost/apiempresas/auth/google/callback
     */
    private function uriDeRetorno(): string
    {
        helper('url');   // el constructor corre antes de initController()

        $actual = site_url('auth/google/callback');
        $config = trim((string) env('google.redirect_uri'));

        if ($config === '') {
            return $actual;
        }

        $hostConfig = strtolower((string) parse_url($config, PHP_URL_HOST));
        $hostActual = strtolower((string) parse_url($actual, PHP_URL_HOST));

        if ($hostConfig !== '' && $hostConfig === $hostActual) {
            return $config;
        }

        log_message('info', '[GoogleAuth] redirect_uri del .env (' . $hostConfig
            . ') no es el host de esta petición (' . $hostActual . '); se usa ' . $actual);

        return $actual;
    }

    /**
     * Redirige al usuario a la página de login de Google
     */
    public function login()
    {
        $estado = [
            'i' => trim((string) ($this->request->getGet('intent') ?? '')),
            'c' => trim((string) ($this->request->getGet('cif') ?? '')),
            'r' => trim((string) ($this->request->getGet('redirect') ?? '')),
            'n' => bin2hex(random_bytes(8)),
        ];

        // Se sigue guardando en sesión como respaldo (y porque otras partes del
        // código leen estas claves), pero YA NO es de donde vive la verdad.
        if ($estado['i'] !== '') {
            session()->set('signup_intent', $estado['i']);
        }
        if ($estado['r'] !== '') {
            session()->set('auth_redirect', $estado['r']);
        }
        // CIF de la empresa que originó el registro (ver Register::logSignupOrigin)
        if ($estado['c'] !== '') {
            session()->set('signup_cif', $estado['c']);
        }

        // La intención viaja en el parámetro `state` de OAuth, que Google nos
        // devuelve tal cual. Antes solo iba en la sesión, y si la sesión no
        // sobrevivía al salto a accounts.google.com (cookie no reenviada, sesión
        // caducada, otro host en el redirect_uri) el callback se encontraba sin
        // intención ni destino: daba de alta al usuario como 'api' y lo soltaba
        // en el wizard de la API, viniendo de una ficha de Solvencia.
        $this->googleClient->setState($this->empaquetarEstado($estado));

        return redirect()->to($this->googleClient->createAuthUrl());
    }

    /**
     * Firma y empaqueta el estado para el viaje de ida y vuelta por Google.
     *
     * Va firmado porque `state` vuelve del exterior: sin HMAC, cualquiera podría
     * fabricar una URL de callback con el `redirect` que quisiera.
     */
    private function empaquetarEstado(array $estado): string
    {
        $cuerpo = rtrim(strtr(base64_encode(json_encode($estado)), '+/', '-_'), '=');

        return $cuerpo . '.' . substr(hash_hmac('sha256', $cuerpo, $this->claveEstado()), 0, 24);
    }

    /**
     * Devuelve el estado del `state` recibido, o un array vacío si no cuadra.
     *
     * @return array{i:string,c:string,r:string}
     */
    private function leerEstado(?string $state): array
    {
        $vacio = ['i' => '', 'c' => '', 'r' => ''];

        if (empty($state) || strpos($state, '.') === false) {
            return $vacio;
        }

        [$cuerpo, $firma] = explode('.', $state, 2);
        $esperada = substr(hash_hmac('sha256', $cuerpo, $this->claveEstado()), 0, 24);

        if (!hash_equals($esperada, $firma)) {
            log_message('warning', '[GoogleAuth] state con firma inválida; se ignora.');
            return $vacio;
        }

        $datos = json_decode((string) base64_decode(strtr($cuerpo, '-_', '+/')), true);
        if (!is_array($datos)) {
            return $vacio;
        }

        return [
            'i' => trim((string) ($datos['i'] ?? '')),
            'c' => trim((string) ($datos['c'] ?? '')),
            'r' => trim((string) ($datos['r'] ?? '')),
        ];
    }

    /** Secreto con el que se firma el `state`. Nunca sale del servidor. */
    private function claveEstado(): string
    {
        $clave = (string) (env('encryption.key') ?: '');

        return $clave !== '' ? $clave : (string) env('google.client_secret');
    }

    /**
     * ¿Es un destino interno seguro?
     *
     * `state` viene de fuera, así que un valor como `//evil.com` o
     * `https://evil.com` convertiría el login en un redirector abierto.
     */
    private function destinoSeguro(string $ruta): bool
    {
        $ruta = trim($ruta);

        if ($ruta === '' || $ruta[0] === '/' || strpos($ruta, '\\') !== false) {
            return false;   // absoluto o con barra invertida: fuera
        }

        // Cualquier cosa con esquema (http:, javascript:, data:…) antes de la
        // primera barra es externa.
        $primeraBarra = strpos($ruta, '/');
        $cabeza = $primeraBarra === false ? $ruta : substr($ruta, 0, $primeraBarra);

        return strpos($cabeza, ':') === false;
    }

    /**
     * Maneja la respuesta de Google
     */
    public function callback()
    {
        $code = $this->request->getGet('code');

        if (!$code) {
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_30'));
        }

        // Intención, CIF y destino: primero del `state` firmado (sobrevive a todo),
        // y solo si no viene, de la sesión.
        $estado     = $this->leerEstado($this->request->getGet('state'));
        $intent     = $estado['i'] !== '' ? $estado['i'] : trim((string) (session()->get('signup_intent') ?? ''));
        $cifOrigen  = $estado['c'] !== '' ? $estado['c'] : trim((string) (session()->get('signup_cif') ?? ''));
        $destinoPed = $estado['r'] !== '' ? $estado['r'] : trim((string) (session()->get('auth_redirect') ?? ''));

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
                    $intent  = $this->deducirIntent($intent, $destinoPed);
                    $user_id = $this->createNewGoogleUser($email, $name, $googleId, $picture, $intent, $destinoPed, $cifOrigen);
                    session()->remove('signup_intent');
                    $this->logSignupOrigin((int) $user_id, $intent, $cifOrigen);
                    \App\Libraries\Embudo::alta((int) $user_id, 'google', $intent);
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

            session()->remove('auth_redirect');
            session()->remove('signup_cif');

            if ($destinoPed !== '' && $this->destinoSeguro($destinoPed)) {
                return redirect()->to(site_url($destinoPed))
                    ->with('success', '¡Bienvenido, ' . $user->name . '!');
            }

            // Sin destino: al menos que no acabe en el sitio equivocado. Quien
            // viene de una ficha de Solvencia no debe aterrizar en el panel de la
            // API — y si sabemos de qué empresa venía, se le devuelve a ella.
            $intentFinal = $intent !== '' ? $intent : (string) ($user->signup_intent ?? '');

            if ($intentFinal === 'view_risk_profile') {
                $destino = $cifOrigen !== ''
                    ? site_url('perfil-de-riesgo?cif=' . urlencode($cifOrigen))
                    : site_url('dashboard?view=risk');

                return redirect()->to($destino)->with('success', '¡Bienvenido, ' . $user->name . '!');
            }

            return redirect()->to(site_url('dashboard'))->with('success', '¡Bienvenido de nuevo, ' . $user->name . '!');

        } catch (\Throwable $e) {
            log_message('error', '[GoogleAuth] Error en callback: ' . $e->getMessage());
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_31'));
        }
    }

    /**
     * Crea un nuevo usuario desde el flujo de Google
     */
    /**
     * Deja constancia de la empresa que originó el registro (tracking_events).
     */
    protected function logSignupOrigin(int $userId, ?string $intent = null, string $cif = ''): void
    {
        $cif = trim($cif !== '' ? $cif : (string) (session()->get('signup_cif') ?? ''));

        if ($userId <= 0 || $cif === '') {
            return;
        }

        try {
            (new \App\Models\TrackingEventModel())->insert([
                'event_name'   => 'risk_signup_origin',
                'page'         => 'auth/google',
                'user_id'      => $userId,
                'session_id'   => substr((string)session_id(), 0, 100),
                'anonymous_id' => '',
                'element'      => substr($cif, 0, 255),
                'metadata'     => json_encode(['intent' => $intent, 'cif' => $cif]),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'logSignupOrigin: ' . $e->getMessage());
        }
    }

    /**
     * Deduce la intención cuando no vino explícita.
     *
     * Vive aparte porque el destino ya no se puede releer de la petición ni de
     * la sesión dentro del alta: en el callback ambas pueden estar vacías, y ahí
     * es donde se decidía silenciosamente que todo el mundo era 'api'.
     */
    private function deducirIntent(string $intent, string $destino): string
    {
        if ($intent !== '') {
            return $intent;
        }

        if (strpos($destino, 'radar') !== false) {
            return 'radar';
        }
        if (strpos($destino, 'database') !== false || strpos($destino, 'listado') !== false) {
            return 'database';
        }
        if (strpos($destino, 'risk') !== false || strpos($destino, 'riesgo') !== false || strpos($destino, 'score') !== false) {
            return 'view_risk_profile';
        }

        return 'api';
    }

    private function createNewGoogleUser($email, $name, $googleId, $picture, $intent = null, string $destino = '', string $cifOrigen = '')
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $host = $this->request->getServer('HTTP_HOST') ?? '';
        $lang = (strpos((string)$host, 'spaincompanyapi') !== false) ? 'en' : 'es';

        try {
            $intent = $this->deducirIntent((string) $intent, $destino);

            // Datos del usuario
            $userData = [
                'name'          => $name,
                'email'         => $email,
                'lang'          => $lang,
                'google_id'     => $googleId,
                'avatar'        => $picture,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), // Pass aleatoria
                'is_active'     => 1,
                'api_access'    => 1,
                'source_app'    => 'apiempresas',
                // Aquí iba un 'preferred_product' que no llegaba a ningún sitio: la
                // columna no existe en la base de datos y el campo tampoco estaba en
                // UserModel::$allowedFields. Manda 'signup_intent'.
                'signup_intent' => $intent,
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

            // Notificaciones, aisladas.
            // Esto va DENTRO de la transacción: sin el try, una excepción mandando
            // un correo deja la transacción sin completar y el alta entera se
            // deshace. El correo es secundario; la cuenta no.
            try {
                $this->emailService->sendRegistrationAdminNotification([
                    'user_id' => $user_id,
                    'name'    => $name,
                    'email'   => $email,
                    'company' => 'Google Signup'
                ]);

                if ($intent === 'view_risk_profile') {
                    // El destino y el CIF llegan por parámetro: en el callback la
                    // petición no trae `redirect` y la sesión puede venir vacía,
                    // así que el correo salía sin la empresa de origen.
                    $this->emailService->sendRiskWelcomeEmail([
                        'user_id'       => $user_id,
                        'name'          => $name,
                        'email'         => $email,
                        'signup_intent' => $intent
                    ], $destino, $cifOrigen);
                } elseif ($intent === 'api') {
                    $this->emailService->sendWelcomeEmail([
                        'user_id'       => $user_id,
                        'name'          => $name,
                        'email'         => $email,
                        'signup_intent' => $intent
                    ]);
                }
            } catch (\Throwable $e) {
                log_message('error', 'GoogleAuth: fallo enviando correos al usuario ' . $user_id . ': ' . $e->getMessage());
            }

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
