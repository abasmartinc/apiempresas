<?php


namespace App\Controllers;

use App\Models\UsersuscriptionsModel;

class Webhooks extends BaseController
{
    public function stripe()
    {
        $secretKey = getenv('STRIPE_SECRET_KEY');
        $whSecret = getenv('STRIPE_WEBHOOK_SECRET');

        if (!$secretKey || !$whSecret) {
            return $this->response->setStatusCode(500)->setBody('Stripe not configured');
        }

        \Stripe\Stripe::setApiKey($secretKey);

        $payload = file_get_contents('php://input');
        $sig = $this->request->getHeaderLine('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $whSecret);
        } catch (\Throwable $e) {
            log_message('error', '[Stripe webhook] invalid signature: ' . $e->getMessage());
            return $this->response->setStatusCode(400)->setBody('Invalid signature');
        }

        // Nos interesa como mínimo checkout.session.completed
        if ($event->type === 'checkout.session.completed') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;

            $userId = (int)($session->metadata->user_id ?? 0);
            $planSlug = (string)($session->metadata->plan ?? '');
            $period = (string)($session->metadata->period ?? '');

            $subscriptionId = $session->subscription ?? null;

            if ($userId) {
                $db = \Config\Database::connect();
                
                // Determinamos preferencia según el plan comprado
                if (strpos($planSlug, 'radar') !== false) {
                    $userModel = new \App\Models\UserModel();
                    $userModel->update($userId, ['preferred_product' => 'radar']);
                } elseif (in_array($planSlug, ['pro', 'business'])) {
                    $userModel = new \App\Models\UserModel();
                    $userModel->update($userId, ['preferred_product' => 'api']);
                }

                if ($subscriptionId) {
                    try {
                        $sub = \Stripe\Subscription::retrieve($subscriptionId);

                        $start = isset($sub->current_period_start) ? date('Y-m-d H:i:s', (int)$sub->current_period_start) : date('Y-m-d H:i:s');
                        $end = isset($sub->current_period_end) ? date('Y-m-d H:i:s', (int)$sub->current_period_end) : date('Y-m-d H:i:s');

                        $planRow = $db->table('api_plans')->select('id')->where('slug', $planSlug)->get()->getRowArray();
                        $planId = (int)($planRow['id'] ?? 0);

                        if ($planId) {
                            $existing = $db->table('user_subscriptions')
                                ->where('user_id', $userId)
                                ->whereIn('status', ['trialing', 'active', 'past_due'])
                                ->orderBy('id', 'DESC')
                                ->get()
                                ->getRowArray();

                            $subData = [
                                'user_id' => $userId,
                                'plan_id' => $planId,
                                'status' => ($sub->status ?? 'active'),
                                'current_period_start' => $start,
                                'current_period_end' => $end,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];

                            if ($existing) {
                                $db->table('user_subscriptions')->where('id', (int)$existing['id'])->update($subData);
                            } else {
                                $subData['created_at'] = date('Y-m-d H:i:s');
                                $db->table('user_subscriptions')->insert($subData);
                            }
                        }
                    } catch (\Throwable $e) {
                        log_message('error', '[Stripe webhook Sub] failed: ' . $e->getMessage());
                    }
                } elseif ($session->mode === 'payment' && $session->payment_status === 'paid') {
                    // One-time payment (Single Download)
                    $planRow = $db->table('api_plans')->select('id')->where('slug', 'radar_single')->get()->getRowArray();
                    $planId = (int)($planRow['id'] ?? 0);

                    if ($planId) {
                        $db->table('user_subscriptions')->insert([
                            'user_id' => $userId,
                            'plan_id' => $planId,
                            'status' => 'active',
                            'current_period_start' => date('Y-m-d H:i:s'),
                            'current_period_end' => date('Y-m-d H:i:s', strtotime('+24 hours')), 
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    }
                }
            }
        }

        return $this->response->setStatusCode(200)->setBody('ok');
    }
}
