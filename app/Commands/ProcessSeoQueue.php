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
        // 1. Validación defensiva del argumento limit
        $rawLimit = $params[0] ?? 10;
        if (!is_numeric($rawLimit) || (int)$rawLimit < 0) {
            $limit = 10;
        } else {
            $limit = (int)$rawLimit;
        }

        if ($limit === 0) {
            CLI::write("Límite establecido en 0. No hay nada que procesar.", 'green');
            return;
        }

        if ($limit > 100) {
            $limit = 100; // Cap de seguridad
        }

        // 2. Control de concurrencia a nivel de proceso mediante flock no bloqueante
        $lockFile = WRITEPATH . 'seo_process_queue.lock';
        $lockFp = @fopen($lockFile, 'c+');

        if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
            CLI::write("Otra instancia de seo:process-queue está en ejecución. Saliendo limpiamente.", 'yellow');
            if ($lockFp) {
                fclose($lockFp);
            }
            return;
        }

        try {
            $db = \Config\Database::connect();
            CLI::write("Iniciando proceso de cola SEO (Límite: $limit)...", 'cyan');

            // 3. Normalizar registros pending que ya alcancen 3 o más intentos
            $db->query("
                UPDATE seo_generation_queue 
                SET status = 'failed', 
                    last_error = 'Límite de intentos alcanzado en estado pendiente', 
                    processing_started_at = NULL 
                WHERE status = 'pending' AND attempts >= 3
            ");

            // 4. Reaper de tareas processing abandonadas (stale > 30 minutos)
            $db->query("
                UPDATE seo_generation_queue 
                SET status = 'pending', 
                    processing_started_at = NULL 
                WHERE status = 'processing' 
                  AND processing_started_at IS NOT NULL 
                  AND processing_started_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE) 
                  AND attempts < 3
            ");

            $db->query("
                UPDATE seo_generation_queue 
                SET status = 'failed', 
                    processing_started_at = NULL, 
                    last_error = 'Timeout de procesamiento / worker abandonado' 
                WHERE status = 'processing' 
                  AND processing_started_at IS NOT NULL 
                  AND processing_started_at < DATE_SUB(NOW(), INTERVAL 30 MINUTE) 
                  AND attempts >= 3
            ");

            // 5. Obtener candidatos pendientes elegibles (attempts < 3)
            $query = $db->table('seo_generation_queue')
                ->select('company_id, attempts')
                ->where('status', 'pending')
                ->where('attempts <', 3)
                ->orderBy('requested_at', 'ASC')
                ->limit($limit)
                ->get();

            $queueItems = $query->getResultArray();

            if (empty($queueItems)) {
                CLI::write("La cola está vacía. No hay nada que procesar.", 'green');
                return;
            }

            $companyModel = new CompanyModel();
            helper('seo_dynamic_helper');

            foreach ($queueItems as $item) {
                $companyId = (int)$item['company_id'];
                $now = date('Y-m-d H:i:s');

                // 6. Claim atómico condicional
                $claimSql = "
                    UPDATE seo_generation_queue 
                    SET status = 'processing', 
                        attempts = attempts + 1, 
                        processing_started_at = ? 
                    WHERE company_id = ? 
                      AND status = 'pending' 
                      AND attempts < 3
                ";
                $db->query($claimSql, [$now, $companyId]);

                if ($db->affectedRows() !== 1) {
                    CLI::write("Empresa ID {$companyId} ya reclamada por otro hilo. Omitiendo.", 'yellow');
                    continue;
                }

                $currentAttempts = (int)($item['attempts'] ?? 0) + 1;
                CLI::write("Procesando empresa ID: {$companyId} (Intento {$currentAttempts}/3)...", 'yellow');

                // 7. Obtener datos completos de la empresa
                $company = $companyModel->getById($companyId);

                if (!$company) {
                    CLI::write("Empresa ID {$companyId} no encontrada. Eliminando de la cola.", 'red');
                    $db->table('seo_generation_queue')->where('company_id', $companyId)->delete();
                    continue;
                }

                // Si ya tiene texto por otra vía, lo borramos de la cola
                if (!empty($company['ai_seo_text'])) {
                    CLI::write("Empresa ID {$companyId} ya tiene texto. Eliminando de la cola.", 'green');
                    $db->table('seo_generation_queue')->where('company_id', $companyId)->delete();
                    continue;
                }

                // 8. Llamada a OpenAI y persistencia
                try {
                    $bormeModel = new BormePostsModel();
                    $bormePosts = $bormeModel->getByCompanyId($companyId);
                    $seoData = getOrGenerateAiSeoData($company, $bormePosts);

                    if ($seoData && $seoData['status'] === 'generated') {
                        CLI::write("Texto generado con éxito para ID {$companyId}.", 'green');
                        $db->table('seo_generation_queue')->where('company_id', $companyId)->delete();
                    } else {
                        $errorMsg = 'Fallo al generar texto: respuesta no válida de IA';
                        CLI::write("Fallo en ID {$companyId}. " . ($currentAttempts >= 3 ? "Marcando como failed." : "Devolviendo a pending."), 'red');
                        $this->handleFailure($db, $companyId, $currentAttempts, $errorMsg);
                    }
                } catch (\Throwable $e) {
                    $cleanError = mb_substr(trim(preg_replace('/[\r\n\t]+/', ' ', strip_tags($e->getMessage()))), 0, 250);
                    $errorMsg = !empty($cleanError) ? $cleanError : 'Excepción desconocida en generación IA';
                    CLI::write("Excepción en ID {$companyId}: {$errorMsg}. " . ($currentAttempts >= 3 ? "Marcando como failed." : "Devolviendo a pending."), 'red');
                    $this->handleFailure($db, $companyId, $currentAttempts, $errorMsg);
                }

                // Pausa para respetar rate limits
                sleep(3);
            }

            CLI::write("Proceso finalizado.", 'cyan');
        } finally {
            if ($lockFp) {
                flock($lockFp, LOCK_UN);
                fclose($lockFp);
            }
        }
    }

    /**
     * Gestiona la actualización de estado ante fallos respetando el umbral de 3 intentos.
     */
    private function handleFailure($db, int $companyId, int $attempts, string $errorMsg): void
    {
        $newStatus = ($attempts >= 3) ? 'failed' : 'pending';

        $db->table('seo_generation_queue')
            ->where('company_id', $companyId)
            ->update([
                'status'                => $newStatus,
                'last_error'            => $errorMsg,
                'processing_started_at' => null,
            ]);
    }
}
