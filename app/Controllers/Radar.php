<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\UsersuscriptionsModel;

class Radar extends BaseController
{
    protected $companyModel;
    protected $subscriptionModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->subscriptionModel = new UsersuscriptionsModel();
        helper('company');
    }

    /**
     * Dashboard principal del Radar Profesional
     */
    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        
        // Verificación de suscripción activa
        $activePlan = $this->subscriptionModel->getActivePlanByUserId($userId);
        
        // Si no tiene plan activo o es de tipo API, lo marcamos como 'free' para el Radar
        $isFree = (!$activePlan || !in_array($activePlan->product_type, ['radar', 'bundle']));

        $isTemporary = false;
        $expiryTime = null;
        $hoursLeft = null;

        if (!$isFree && $activePlan) {
            // Info para el banner de acceso temporal
            $isTemporary = (isset($activePlan->product_type) && $activePlan->product_type === 'radar_single') || (isset($activePlan->plan_name) && strpos(strtolower($activePlan->plan_name), 'single') !== false);
            $expiryTime = $isTemporary ? strtotime($activePlan->current_period_end) : null;
            $hoursLeft = $expiryTime ? ceil(($expiryTime - time()) / 3600) : null;
        }

        $db = \Config\Database::connect();

        // Obtener filtros desde la URL
        $province = $this->request->getGet('provincia');
        $cnae = $this->request->getGet('cnae');
        $timeRange = $this->request->getGet('rango') ?? '30'; // Default 30 días

        // 1. Estadísticas Rápidas (National)
        $stats = [
            'hoy' => $this->countNewCompanies('hoy'),
            'semana' => $this->countNewCompanies('semana'),
            'mes' => $this->countNewCompanies('mes')
        ];

        // 2. Query Principal del Listado
        $builder = $this->companyModel->builder();
        $builder->select('id, company_name, cif, fecha_constitucion, cnae_label, registro_mercantil');
        $builder->where('fecha_constitucion IS NOT NULL');

        if ($province) {
            $builder->where('registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }
        if ($cnae) {
            $builder->where('cnae_code', $cnae);
        }

        // Rango de tiempo
        $days = (int)$timeRange;
        $dateLimit = date('Y-m-d', strtotime("-$days days"));
        $builder->where('fecha_constitucion >=', $dateLimit);

        $builder->orderBy('fecha_constitucion', 'DESC');
        $builder->limit(100); // Límite generoso para el panel profesional

        $companies = $builder->get()->getResultArray();

        // 3. Datos para Filtros (Agregados rápidos)
        $provinces = $db->query("SELECT province as name FROM seo_stats ORDER BY total_companies DESC LIMIT 20")->getResultArray();
        $topSectors = $db->query("SELECT cnae_label as label, cnae_code as code FROM seo_stats_cnae ORDER BY total_companies DESC LIMIT 15")->getResultArray();

        $data = [
            'stats' => $stats,
            'companies' => $companies,
            'provinces' => $provinces,
            'topSectors' => $topSectors,
            'filters' => [
                'provincia' => $province,
                'cnae' => $cnae,
                'rango' => $timeRange
            ],
            'isFree' => $isFree,
            'userPlan' => [
                'isTemporary' => $isTemporary,
                'hoursLeft' => $hoursLeft,
                'planName' => $activePlan ? $activePlan->plan_name : 'Gratuito'
            ]
        ];

        return view('radar/dashboard', $data);
    }

    /**
     * Contador rápido de empresas nuevas por periodo
     */
    private function countNewCompanies($period)
    {
        $builder = $this->companyModel->builder();
        if ($period === 'hoy') {
            $builder->where('fecha_constitucion >=', date('Y-m-d'));
        } elseif ($period === 'semana') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')));
        } elseif ($period === 'mes') {
            $builder->where('fecha_constitucion >=', date('Y-m-01'));
        }
        return $builder->countAllResults();
    }
}
