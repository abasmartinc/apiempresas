<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CompanyModel;
use OpenAI;
use Exception;

class AiSeoController extends ResourceController
{
    public function generate()
    {
        $cif = $this->request->getPost('cif') ?? $this->request->getGet('cif');
        if (empty($cif)) {
            return $this->fail('CIF es requerido', 400);
        }

        $companyModel = new CompanyModel();
        $company = $companyModel->getByCif($cif);

        if (!$company) {
            return $this->failNotFound('Empresa no encontrada');
        }

        // Si ya tiene texto, devolverlo
        if (!empty($company['ai_seo_text'])) {
            $faqs = null;
            if (!empty($company['ai_faqs'])) {
                $faqs = json_decode($company['ai_faqs'], true);
            }
            return $this->respond([
                'status' => 'cached', 
                'text' => $company['ai_seo_text'],
                'faqs' => $faqs
            ]);
        }

        // Configurar OpenAI
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return $this->failServerError('API key de OpenAI no configurada');
        }

        // Generar a través del helper
        helper('seo_dynamic_helper');
        $seoData = getOrGenerateAiSeoData($company);

        if (!$seoData) {
            return $this->failServerError('No se pudo generar el resumen.');
        }

        return $this->respond([
            'status' => $seoData['status'], 
            'text'   => $seoData['text'],
            'faqs'   => $seoData['faqs']
        ]);
    }
}
