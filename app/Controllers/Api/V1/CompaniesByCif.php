<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CompanyModel;

class CompaniesByCif extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    /** @var CompanyModel */
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        helper('api');
    }

    public function index()
    {
        $cif = trim((string) $this->request->getGet('cif'));

        if ($cif === '') {
            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'VALIDATION_ERROR',
                    'message' => 'El parámetro "cif" es obligatorio.'
                ],
                ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        // Cache interno por CIF (24h)
        $cacheKey = 'company_by_cif_' . md5(mb_strtolower($cif, 'UTF-8'));
        $cached = cache($cacheKey);

        if (is_array($cached) && !empty($cached)) {
            // Apply masking if Free plan
            $planId = $this->request->api_meta['plan_id'] ?? 1;
            if ((int)$planId === 1) {
                $cached = mask_company_data($cached);
            }

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $cached,
                ],
                ResponseInterface::HTTP_OK
            );
        }

        try {
            $company = $this->companyModel->getByCif($cif);

            if (!$company) {
                return $this->respond(
                    [
                        'success' => false,
                        'error'   => 'COMPANY_NOT_FOUND',
                        'message' => 'No se encontró ninguna empresa con ese CIF.'
                    ],
                    ResponseInterface::HTTP_NOT_FOUND
                );
            }

            // Guardar SOLO data en cache (completa)
            cache()->save($cacheKey, $company, 86400); // 24h

            // Apply masking if Free plan
            $planId = $this->request->api_meta['plan_id'] ?? 1;
            if ((int)$planId === 1) {
                $company = mask_company_data($company);
            }

            return $this->respond(
                [
                    'success' => true,
                    'data'    => $company,
                ],
                ResponseInterface::HTTP_OK
            );
        } catch (\Throwable $e) {
            log_message('error', '[CompaniesByCif::index] ' . $e->getMessage());

            return $this->respond(
                [
                    'success' => false,
                    'error'   => 'SERVER_ERROR',
                    'message' => 'Se ha producido un error interno al consultar la empresa.'
                ],
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
            );
        } finally {
            // Trigger: Milestone 1st Request Email
            try {
                if (isset($company) && $company) {
                    $userId = $this->request->api_meta['user_id'] ?? null;
                    if ($userId) {
                        $automationModel = new \App\Models\EmailAutomationModel();
                        if (!$automationModel->wasSent($userId, 'first_request')) {
                            $userModel = new \App\Models\UserModel();
                            $user = $userModel->asArray()->find($userId);
                            if ($user && (int)$user['is_admin'] === 0) {
                                $this->emailService->sendFirstRequestMilestone($user);
                                $automationModel->markAsSent($userId, 'first_request');
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                log_message('error', '[CompaniesByCif::Milestone] ' . $e->getMessage());
            }
        }
    }
}
