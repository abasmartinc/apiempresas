<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {

        // Review Modal Logic
        $showReviewModal = false;
        $db = \Config\Database::connect();
        $ip = $this->request->getIPAddress();

        // Check if user already submitted a review
        $alreadyReviewed = $db->table('user_reviews')
            ->where('ip_address', $ip)
            ->countAllResults();

        if ($alreadyReviewed == 0) {
            // Check if user has >= 3 searches
            $searchCount = $db->table('company_search_logs')
                ->where('ip', $ip)
                ->countAllResults();

            if ($searchCount >= 3) {
                $showReviewModal = true;
            }
        }

        // Dynamic Social Proof Counter (with short cache)
        $cache = \Config\Services::cache();
        $cacheKey = 'home_social_proof_text_v3';
        $socialProofText = $cache->get($cacheKey);

        if ($socialProofText === null) {
            $apiRequestsModel = new \App\Models\ApiRequestsModel();
            $searchLogModel = new \App\Models\SearchLogModel();
            $today = date('Y-m-d');

            $apiValidationsToday = $apiRequestsModel->countRequestsForDay($today);
            $webValidationsToday = $searchLogModel->countLogsForDay($today);
            $totalReal = $apiValidationsToday + $webValidationsToday;

            if ($totalReal <= 0) {
                $socialProofText = ''; // No data, hide block
            } elseif ($totalReal < 50) {
                $socialProofText = "Más de 100 empresas validadas hoy automáticamente";
            } elseif ($totalReal < 200) {
                $roundedTotal = ceil($totalReal / 50) * 50;
                $socialProofText = "Más de " . number_format($roundedTotal, 0, ',', '.') . " empresas validadas hoy automáticamente";
            } else {
                $socialProofText = "Hoy se han validado " . number_format($totalReal, 0, ',', '.') . " empresas automáticamente";
            }

            // Save to cache for 5 minutes (short cache)
            $cache->save($cacheKey, $socialProofText, 300);
        }

        // Fetch Free Plan Limit
        $freeLimit = get_free_plan_limit();

        return view('home', [
            'showReviewModal' => $showReviewModal,
            'socialProofText' => $socialProofText,
            'freeLimit' => $freeLimit
        ]);
    }

    public function englishStandalone()
    {


        // Dynamic Social Proof Counter (English)
        $cache = \Config\Services::cache();
        $cacheKey = 'home_social_proof_text_en_v1';
        $socialProofText = $cache->get($cacheKey);

        if ($socialProofText === null) {
            $apiRequestsModel = new \App\Models\ApiRequestsModel();
            $searchLogModel = new \App\Models\SearchLogModel();
            $today = date('Y-m-d');

            $apiValidationsToday = $apiRequestsModel->countRequestsForDay($today);
            $webValidationsToday = $searchLogModel->countLogsForDay($today);
            $totalReal = $apiValidationsToday + $webValidationsToday;

            if ($totalReal <= 0) {
                $socialProofText = '';
            } elseif ($totalReal < 50) {
                $socialProofText = "Over 100 companies validated today automatically";
            } elseif ($totalReal < 200) {
                $roundedTotal = ceil($totalReal / 50) * 50;
                $socialProofText = "Over " . number_format($roundedTotal, 0, '.', ',') . " companies validated today automatically";
            } else {
                $socialProofText = "Today " . number_format($totalReal, 0, '.', ',') . " companies were validated automatically";
            }

            $cache->save($cacheKey, $socialProofText, 300);
        }

        $freeLimit = get_free_plan_limit();

        return view('home_en_standalone', [
            'socialProofText' => $socialProofText,
            'freeLimit' => $freeLimit
        ]);
    }

    public function submitReview()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $rating = (int) $this->request->getPost('rating');
        $comment = (string) $this->request->getPost('comment');

        if ($rating < 1 || $rating > 5) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid rating']);
        }

        $ip = $this->request->getIPAddress();
        $db = \Config\Database::connect();

        // Check if already reviewed
        $alreadyReviewed = $db->table('user_reviews')
            ->where('ip_address', $ip)
            ->countAllResults();

        if ($alreadyReviewed > 0) {
            return $this->response->setJSON(['success' => true]);
        }

        // Insert review
        $data = [
            'ip_address' => $ip,
            'rating' => $rating,
            'comment' => $comment ? esc($comment) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->table('user_reviews')->insert($data);

        // Send email notification
        try {
            $emailService = \Config\Services::email();
            $emailService->setTo('papelo.amh@gmail.com');
            $emailService->setSubject('Nueva reseña en APIEmpresas');
            $emailService->setMessage("¡Se ha recibido una nueva reseña en la web!\n\nEstrellas: {$rating}/5\nComentario: " . ($comment ?: 'Sin comentarios') . "\nIP: {$ip}");
            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'Error sending review email: ' . $e->getMessage());
        }

        return $this->response->setJSON(['success' => true]);
    }
}
