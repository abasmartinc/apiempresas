<?php


namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Login extends BaseController
{
    /** Config de rate limit */
    private int $maxAttempts = 5;      // intentos permitidos por ventana
    private int $windowSecs = 600;    // ventana (10 min) para conteo

    public function __construct()
    {
        helper(['form', 'url']);
    }

    /**
     * Muestra el formulario de login o intenta autologin por URL (?auth_token=&user_id=).
     */
    public function index(): string|RedirectResponse
    {
        // Si ya hay sesión activa, comportamiento original
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/wizard');
        }
        return view('auth/login');
    }

    public function attempt(): \CodeIgniter\HTTP\RedirectResponse
    {
        // 1) Validación de campos
        if ($resp = $this->validateLogin()) {
            return $resp; // vuelve con errores + old input
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');
        $ip       = (string) $this->request->getIPAddress();

        // 2) Throttle
        if ($this->tooManyAttempts($username, $ip)) {
            // Puedes mostrar una página o volver con error
            return $this->fail('Too many attempts. Please try again later.');
        }

        // 3) Usuario
        $user = $this->getUserByEmail($username);
        if (!$user) {
            return $this->fail('User or password incorrect');
        }

        if ((int) $user->active !== 1) {
            $this->registerAttemptFailure($username, $ip);
            return $this->fail('User account is inactive');
        }

        // 4) Password
        if (!$this->passwordIsValid($password, (string) $user->password)) {
            $this->registerAttemptFailure($username, $ip);
            return $this->fail('User or password incorrect');
        }

        // 5) Éxito
        $this->clearAttempts($username, $ip);
        $this->storeUserSession($user);
        $intended = session()->get('intended') ?? site_url('wizard');
        session()->remove('intended');
        return redirect()->to($intended);
    }

    /**
     * Valida inputs y, si falla, vuelve con errores y old input.
     * Devuelve RedirectResponse | null
     */
    protected function validateLogin(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'username' => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        $messages = [
            'username' => [
                'required'    => 'Email is required.',
                'valid_email' => 'Please enter a valid email address.',
            ],
            'password' => [
                'required'    => 'Password is required.',
                'min_length'  => 'Password must be at least 6 characters.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            // Enviar array de errores + old input
            return redirect()
                ->back()
                ->with('errors', $this->validator->getErrors())
                ->withInput();
        }

        return null;
    }

    /** Helper para errores de autenticación genéricos */
    protected function fail(string $message): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()
            ->back()
            ->with('error', $message)         // mensaje general (tu alerta roja)
            ->with('errors', ['auth' => $message]) // por si quieres listarla también
            ->withInput();
    }

    private function getUserByEmail(string $username): ?object
    {
        return db_connect()
            ->table('users')
            ->select('users.*, employee_data.id as data_id, companies.Com_DB_Group')
            ->join('employee_data', 'employee_data.user_id = users.id', 'left')
            ->join('companies', 'companies.id = users.com_id')
            ->where('users.email', $username)
            ->get()
            ->getRow();
    }


    /**
     * Verifica la contraseña contra un hash moderno (argon2id/bcrypt).
     * Si usaste "pepper" al generar el hash, debe ser el MISMO aquí.
     */
    private function passwordIsValid(string $inputPassword, string $storedHash): bool
    {
        $pepper = env('PASSWORD_PEPPER'); // string|null
        $candidate = $pepper
            ? hash_hmac('sha256', $inputPassword, $pepper, true) // binario
            : $inputPassword;

        return password_verify($candidate, $storedHash);
    }

    private function storeUserSession(object $user): void
    {
        session()->set([
            'isLoggedIn' => true,
            'logged_document_generator' => true,
            'id'       => (int)$user->id,
            'applicant_id'       => (int)$user->applicant_id,
            'data_id'  => (int)$user->data_id,
            'com_id'  => (int)$user->com_id,
            'db_group'  => $user->Com_DB_Group,
            'username' => $user->username ?? $user->email,
        ]);
    }

    /** --------------------
     *  Rate limiting seguro
     *  -------------------- */

    /** Genera claves válidas para cache: <prefijo>_<hash> */
    private function cacheKey(string $prefix, string $value): string
    {
        // Normaliza y hashea → sin caracteres reservados
        $norm = mb_strtolower(trim($value));
        return $prefix . '_' . substr(sha1($norm), 0, 20);
    }

    private function tooManyAttempts(string $username, string $ip): bool
    {
        $keyUser = $this->cacheKey('login_user', $username);
        $keyIp = $this->cacheKey('login_ip', $ip);

        $u = (int)(cache()->get($keyUser) ?? 0);
        $i = (int)(cache()->get($keyIp) ?? 0);

        return ($u >= $this->maxAttempts) || ($i >= $this->maxAttempts);
    }

    private function registerAttemptFailure(string $username, string $ip): void
    {
        $keyUser = $this->cacheKey('login_user', $username);
        $keyIp = $this->cacheKey('login_ip', $ip);

        $u = (int)(cache()->get($keyUser) ?? 0) + 1;
        $i = (int)(cache()->get($keyIp) ?? 0) + 1;

        cache()->save($keyUser, $u, $this->windowSecs);
        cache()->save($keyIp, $i, $this->windowSecs);
    }

    private function clearAttempts(string $username, string $ip): void
    {
        cache()->delete($this->cacheKey('login_user', $username));
        cache()->delete($this->cacheKey('login_ip', $ip));
    }

    /**
     * Logout
     */
    public function logout(): string
    {
        session()->destroy();
        return view('auth/logout');
    }

    public function forgot_password(): string|RedirectResponse
    {
        return view('auth/forgot_password');
    }

}
