<?php

namespace App\Libraries;

/**
 * Alta o actualización de una suscripción de Stripe en `user_subscriptions`.
 *
 * Un solo sitio para el webhook (checkout.session.completed, invoice.paid) y para
 * la página de éxito del pago: los tres pueden llegar a la vez y cada uno creaba
 * su fila (04-10-2026: cuatro suscripciones con dos filas en producción).
 */
class SuscripcionStripe
{
    /**
     * Inicio y fin del periodo en curso. En las versiones nuevas de la API de
     * Stripe ya no vienen en la suscripción sino en sus items: leyéndolos solo
     * arriba salían vacíos y se guardaba "hoy" y "+1 mes", también en el anual.
     *
     * @return array{0:string,1:string}
     */
    public static function periodoDe($stripeSub): array
    {
        $ini = $stripeSub->current_period_start ?? ($stripeSub->items->data[0]->current_period_start ?? null);
        $fin = $stripeSub->current_period_end ?? ($stripeSub->items->data[0]->current_period_end ?? null);

        if (empty($ini) || empty($fin)) {
            log_message('error', '[SuscripcionStripe] Stripe no trae el periodo de ' . ($stripeSub->id ?? '?') . ': se usa +1 mes.');
        }
        $start = !empty($ini) ? date('Y-m-d H:i:s', (int) $ini) : date('Y-m-d H:i:s');
        $end   = !empty($fin) ? date('Y-m-d H:i:s', (int) $fin) : date('Y-m-d H:i:s', strtotime('+1 month'));
        if ($start === $end) {
            $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));
        }

        return [$start, $end];
    }

    /**
     * Crea o actualiza LA fila de una suscripción (una por suscripción de Stripe).
     * El bloqueo por id de suscripción hace que quien llegue segundo actualice la
     * fila del primero. Devuelve el id de la fila.
     */
    public static function guardar(int $userId, int $planId, string $stripeSubscriptionId, string $start, string $end): int
    {
        $db      = \Config\Database::connect();
        $candado = 'sub_' . sha1($stripeSubscriptionId);
        try {
            $db->query('SELECT GET_LOCK(?, 10)', [$candado]);
        } catch (\Throwable $e) {
            // Sin bloqueo se sigue: peor que antes no queda
        }

        try {
            $fila = $db->table('user_subscriptions')
                ->where('stripe_subscription_id', $stripeSubscriptionId)
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();

            $ahora = date('Y-m-d H:i:s');
            if ($fila) {
                $db->table('user_subscriptions')->where('id', (int) $fila['id'])->update([
                    'plan_id'              => $planId,
                    'status'               => 'active',
                    'current_period_start' => $start,
                    'current_period_end'   => $end,
                    'updated_at'           => $ahora,
                ]);
                return (int) $fila['id'];
            }

            $db->table('user_subscriptions')->insert([
                'user_id'                => $userId,
                'plan_id'                => $planId,
                'stripe_subscription_id' => $stripeSubscriptionId,
                'status'                 => 'active',
                'current_period_start'   => $start,
                'current_period_end'     => $end,
                'created_at'             => $ahora,
                'updated_at'             => $ahora,
            ]);
            return (int) $db->insertID();
        } finally {
            try {
                $db->query('SELECT RELEASE_LOCK(?)', [$candado]);
            } catch (\Throwable $e) {
            }
        }
    }

    /**
     * La suscripción nueva sustituye a las anteriores del MISMO producto: se cancelan
     * en Stripe las que sean otra suscripción y se desactivan en local (status '').
     * Las de otro producto no se tocan (se puede pagar la API y Solvencia a la vez).
     * Con la API también se desactiva la fila del plan Free (plan_id 1).
     * Se puede llamar varias veces: la segunda no encuentra nada que hacer.
     */
    public static function sustituirAnteriores(int $userId, string $productType, string $stripeSubscriptionId, int $filaNuevaId): void
    {
        $productType = strtolower(trim($productType));
        if ($userId <= 0 || $productType === '' || $filaNuevaId <= 0) {
            return;
        }

        $db = \Config\Database::connect();
        $b  = $db->table('user_subscriptions us')
            ->select('us.id, us.stripe_subscription_id')
            ->join('api_plans p', 'p.id = us.plan_id')
            ->where('us.user_id', $userId)
            ->where('us.status', 'active')
            ->where('us.id !=', $filaNuevaId);
        if ($productType === 'api') {
            $b->groupStart()->where('p.product_type', 'api')->orWhere('us.plan_id', 1)->groupEnd();
        } else {
            $b->where('p.product_type', $productType);
        }

        $ids = [];
        foreach ($b->get()->getResultArray() as $ant) {
            $subAnt = (string) ($ant['stripe_subscription_id'] ?? '');
            if ($subAnt === $stripeSubscriptionId) {
                continue;   // otra fila de la misma suscripción: no es "anterior"
            }
            if ($subAnt !== '') {
                try {
                    (new \Stripe\StripeClient(env('STRIPE_SECRET_KEY')))->subscriptions->cancel($subAnt);
                    log_message('info', "[SuscripcionStripe] Cancelada en Stripe la suscripción anterior de {$productType}: {$subAnt}");
                } catch (\Throwable $e) {
                    log_message('error', '[SuscripcionStripe] Error al cancelar la suscripción anterior en Stripe: ' . $e->getMessage());
                }
            }
            $ids[] = (int) $ant['id'];
        }

        if (!empty($ids)) {
            $db->table('user_subscriptions')->whereIn('id', $ids)->update(['status' => '', 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }
}
