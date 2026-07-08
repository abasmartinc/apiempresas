<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class DownloadController extends BaseController
{
    public function secure($token)
    {
        $db = \Config\Database::connect();
        
        $job = $db->table('export_jobs')
                  ->where('download_token', $token)
                  ->where('status', 'completed')
                  ->get()
                  ->getRow();

        if (!$job) {
            return view('errors/html/error_404', ['message' => 'El enlace de descarga es inválido o ha expirado.']);
        }

        $filePath = $job->file_path;

        if (!file_exists($filePath)) {
            return view('errors/html/error_404', ['message' => 'El archivo ya no está disponible en el servidor.']);
        }

        // Determinar el nombre con el que se descargará
        $typeLabel = 'Base_de_Datos';
        if ($job->export_type === 'subsidies_excel') $typeLabel = 'Subvenciones';
        if ($job->export_type === 'contracts_excel') $typeLabel = 'Licitaciones';
        if ($job->export_type === 'excel') $typeLabel = 'Empresas';

        $filename = "APIEmpresas_{$typeLabel}_" . date('Ymd_His', strtotime($job->created_at)) . ".zip";

        // Hacer streaming del archivo
        return $this->response->download($filePath, null)->setFileName($filename);
    }
}
