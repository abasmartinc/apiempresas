<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CompanyModel;
use App\Models\BormePostsModel;

class ProcessSeoQueue extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:process-queue';
    protected $description = 'Procesa la cola de empresas para generar su texto SEO con IA de forma asíncrona.';

    public function run(array $params)
    {
        $limit = $params[0] ?? 10; // Por defecto procesamos 10 por ejecución
        $db = \Config\Database::connect();

        CLI::write("Iniciando proceso de cola SEO (Límite: $limit)...", 'cyan');

        // 1. Obtener empresas pendientes
        $query = $db->table('seo_generation_queue')
            ->where('status', 'pending')
            ->orderBy('requested_at', 'ASC')
            ->limit((int)$limit)
            ->get();

        $queueItems = $query->getResultArray();

        if (empty($queueItems)) {
            CLI::write("La cola está vacía. No hay nada que procesar.", 'green');
            return;
        }

        $companyModel = new CompanyModel();
        helper('seo_dynamic_helper');

        foreach ($queueItems as $item) {
            $companyId = $item['company_id'];
            CLI::write("Procesando empresa ID: {$companyId}...", 'yellow');

            // 2. Marcar como procesando para evitar duplicidades si se solapan crons
            $db->table('seo_generation_queue')
                ->where('company_id', $companyId)
                ->update(['status' => 'processing']);

            // 3. Obtener datos completos de la empresa
            $company = $companyModel->getById($companyId);

            if (!$company) {
                CLI::write("Empresa ID {$companyId} no encontrada. Eliminando de la cola.", 'red');
                $db->table('seo_generation_queue')->where('company_id', $companyId)->delete();
                continue;
            }

            // Si por alguna razón ya se generó por otra vía, lo borramos
            if (!empty($company['ai_seo_text'])) {
                CLI::write("Empresa ID {$companyId} ya tiene texto. Eliminando de la cola.", 'green');
                $db->table('seo_generation_queue')->where('company_id', $companyId)->delete();
                continue;
            }

            // 4. Llamar a OpenAI
            try {
                // Obtenemos los actos del BORME para que la IA haga el resumen
                $bormeModel = new BormePostsModel();
                $bormePosts = $bormeModel->getByCompanyId((int)$companyId);
                $seoData = getOrGenerateAiSeoData($company, $bormePosts);
                
                if ($seoData && $seoData['status'] === 'generated') {
                    CLI::write("Texto generado con éxito para ID {$companyId}.", 'green');
                    // 5. Eliminar de la cola al terminar con éxito
                    $db->table('seo_generation_queue')->where('company_id', $companyId)->delete();
                } else {
                    CLI::write("Fallo al generar texto para ID {$companyId}. Devolviendo a pending.", 'red');
                    // Devolver a pending para que lo intente en la próxima ejecución
                    $db->table('seo_generation_queue')
                        ->where('company_id', $companyId)
                        ->update(['status' => 'pending']);
                }
            } catch (\Exception $e) {
                CLI::write("Excepción en ID {$companyId}: " . $e->getMessage(), 'red');
                $db->table('seo_generation_queue')
                    ->where('company_id', $companyId)
                    ->update(['status' => 'pending']);
            }
            
            // Pequeña pausa para no saturar los límites de rate limit de OpenAI
            sleep(3);
        }

        CLI::write("Proceso finalizado.", 'cyan');
    }
}
