<?php

namespace App\Services;

/**
 * Abono de los créditos del Pack de consultas de Solvencia. ÚNICO punto que suma
 * `users.risk_credits` por una compra.
 *
 * Hasta el 23-09 el pack se abonaba en tres sitios, cada uno por su cuenta:
 *
 *  - el webhook de Stripe (checkout.session.completed), sin comprobar el pago
 *    ni si ese aviso ya se había procesado (Stripe reintenta los webhooks);
 *  - la página de éxito (/billing/success), que abonaba con solo tener en la
 *    sesión el contexto del checkout, guardado ANTES de ir a Stripe: bastaba con
 *    cancelar el pago y abrir /billing/success para llevarse 5 créditos, y
 *    repetirlo cada hora;
 *  - el simulador de pagos.
 *
 * Resultado: una compra real abonaba 10 créditos (webhook + página de éxito) y
 * una compra cancelada, 5.
 *
 * Ahora todos llaman aquí con la referencia del pago (el id de la sesión de
 * Checkout de Stripe) y la tabla `risk_pack_purchases` tiene esa referencia como
 * UNIQUE: el primero que llega abona, los demás no hacen nada. Quién llegue
 * primero —el webhook o el usuario volviendo de Stripe— da igual.
 *
 * Comprobar que el pago está cobrado es responsabilidad de quien llama
 * (`payment_status === 'paid'`), porque solo él tiene el objeto de Stripe.
 */
class RiskPackService
{
    /**
     * Abona el pack una sola vez por referencia.
     *
     * @param string $referencia Id de la sesión de Checkout (cs_...) o 'sim_...' en el simulador.
     * @return bool true si este llamada ha abonado; false si ya estaba abonado o falló.
     */
    public function abonar(string $referencia, int $userId, int $creditos = 5, string $cif = '', ?int $importeCentimos = null): bool
    {
        $referencia = trim($referencia);
        if ($referencia === '' || $userId <= 0) {
            log_message('error', "[RiskPack] Abono sin referencia o sin usuario (ref='{$referencia}', user={$userId}).");
            return false;
        }
        if ($creditos <= 0 || $creditos > 100) {
            $creditos = 5;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // La UNIQUE de stripe_session_id es la que garantiza el "una sola vez", también
        // con dos peticiones a la vez: solo una consigue insertar.
        $db->query(
            'INSERT IGNORE INTO risk_pack_purchases (stripe_session_id, user_id, credits, target_cif, amount_cents, created_at) VALUES (?, ?, ?, ?, ?, ?)',
            [$referencia, $userId, $creditos, $cif !== '' ? strtoupper($cif) : null, $importeCentimos, date('Y-m-d H:i:s')]
        );

        if ($db->affectedRows() !== 1) {
            $db->transComplete();
            log_message('info', "[RiskPack] {$referencia} ya estaba abonado; no se suma nada.");
            return false;
        }

        $db->table('users')
            ->where('id', $userId)
            ->set('risk_credits', 'COALESCE(risk_credits, 0) + ' . (int) $creditos, false)
            ->update();

        $db->transComplete();

        if (!$db->transStatus()) {
            log_message('error', "[RiskPack] Falló la transacción al abonar {$referencia} al usuario {$userId}.");
            return false;
        }

        try {
            (new \App\Models\UserEventsModel())->logEvent($userId, 'purchase_risk_pack', (string) $creditos);
        } catch (\Throwable $e) {
            log_message('error', '[RiskPack] No se pudo registrar el evento: ' . $e->getMessage());
        }

        // El correo va aparte: un fallo de SMTP no puede deshacer un abono ya pagado.
        try {
            $user = (new \App\Models\UserModel())->find($userId);
            if ($user) {
                (new EmailService())->sendRiskPackWelcome([
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'user_id' => $user->id,
                ], $creditos, $cif);
            }
        } catch (\Throwable $e) {
            log_message('error', '[RiskPack] Abonado, pero falló el correo de bienvenida: ' . $e->getMessage());
        }

        log_message('info', "[RiskPack] Abonados {$creditos} créditos al usuario {$userId} ({$referencia}).");
        return true;
    }
}
