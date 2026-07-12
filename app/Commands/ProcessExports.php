<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use ZipArchive;

class ProcessExports extends BaseCommand
{
    protected $group       = 'Background Tasks';
    protected $name        = 'process:exports';
    protected $description = 'Process massive export jobs and generate ZIP files';

    public function run(array $params)
    {
        CLI::write("Buscando trabajos de exportación pendientes...", 'cyan');

        $db = \Config\Database::connect();
        
        $job = $db->table('export_jobs')
                  ->where('status', 'pending')
                  ->orderBy('created_at', 'ASC')
                  ->limit(1)
                  ->get()
                  ->getRow();

        if (!$job) {
            CLI::write("No hay trabajos pendientes.", 'green');
            return;
        }

        CLI::write("Procesando trabajo #{$job->id} ({$job->export_type}) para {$job->user_email}...", 'yellow');

        $db->table('export_jobs')->where('id', $job->id)->update([
            'status' => 'processing',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $filters = json_decode($job->filters, true) ?? [];
        $exportType = $job->export_type;

        $exportDir = WRITEPATH . 'uploads/exports';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $csvFilename = "export_{$job->id}.csv";
        $csvPath = $exportDir . '/' . $csvFilename;
        $zipFilename = "export_{$job->id}.zip";
        $zipPath = $exportDir . '/' . $zipFilename;

        try {
            $fp = fopen($csvPath, 'w');
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

            if ($exportType === 'subsidies_excel') {
                $this->processSubsidies($db, $filters, $fp);
            } elseif ($exportType === 'contracts_excel') {
                $this->processContracts($db, $filters, $fp);
            } else {
                $this->processCompanies($db, $filters, $fp);
            }

            fclose($fp);

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $niceName = $exportType === 'subsidies_excel' ? 'Subvenciones_APIEmpresas.csv' : 'Licitaciones_APIEmpresas.csv';
                if ($exportType === 'excel' || $exportType === 'directory_excel') {
                    $niceName = 'Empresas_APIEmpresas.csv';
                }
                $zip->addFile($csvPath, $niceName);
                $zip->close();
            }

            if (file_exists($csvPath)) {
                unlink($csvPath);
            }

            $downloadToken = bin2hex(random_bytes(32));
            $db->table('export_jobs')->where('id', $job->id)->update([
                'status' => 'completed',
                'download_token' => $downloadToken,
                'file_path' => $zipPath,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $emailService = new \App\Services\EmailService();
            $emailService->sendMassiveExportReady($job->user_email, $downloadToken, $exportType, (int)$job->total_records);

            CLI::write("Trabajo #{$job->id} completado. ZIP generado y correo enviado.", 'green');

        } catch (\Exception $e) {
            CLI::error("Error procesando trabajo #{$job->id}: " . $e->getMessage());
            $db->table('export_jobs')->where('id', $job->id)->update([
                'status' => 'failed',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private function processSubsidies($db, $filters, $fp)
    {
        $convocatoria = $filters['convocatoria'] ?? '';
        $year = $filters['year'] ?? '';

        fputcsv($fp, ['Empresa', 'CIF', 'Convocatoria', 'Instrumento / Detalle', 'Fecha Concesión', 'Importe', 'Teléfono', 'Sector CNAE', 'Provincia', 'Dirección']);

        $builder = $db->table('company_subsidies s');
        $builder->select('c.company_name, s.raw_beneficiario, s.company_cif, s.convocatoria, s.instrumento, s.fecha_concesion, s.importe, c.phone, c.cnae_label, c.registro_mercantil, c.address');
        $builder->join('companies c', 'c.cif = s.company_cif', 'left');

        if ($convocatoria !== '') {
            $billingService = new \App\Services\BillingService();
            $builder->where('s.convocatoria', $billingService->resolveSubsidiesConvocatoria($convocatoria));
        }
        if ($year !== '') {
            $builder->where('YEAR(s.fecha_concesion)', $year);
        }

        $query = $builder->get();
        while ($row = $query->getUnbufferedRow('array')) {
            $empresa = $row['company_name'] ?: $row['raw_beneficiario'] ?: $row['company_cif'];
            fputcsv($fp, [
                $empresa,
                $row['company_cif'],
                $row['convocatoria'],
                $row['instrumento'],
                $row['fecha_concesion'] ? date('d/m/Y', strtotime($row['fecha_concesion'])) : '',
                number_format((float)$row['importe'], 2, ',', ''),
                $row['phone'] ?? '',
                $row['cnae_label'] ?? '',
                $row['registro_mercantil'] ?? '',
                $row['address'] ?? ''
            ]);
        }
    }

    private function processContracts($db, $filters, $fp)
    {
        $year = $filters['year'] ?? '';
        $organo = $filters['organo'] ?? '';

        fputcsv($fp, ['Empresa Adjudicataria', 'CIF', 'Órgano de Contratación', 'Título del Contrato', 'Fecha Adjudicación', 'Importe Adjudicación', 'Teléfono', 'Sector CNAE', 'Provincia', 'Dirección']);

        $builder = $db->table('company_contracts c_contr');
        $builder->select('c_contr.company_name, c_contr.raw_adjudicatario, c_contr.company_cif, c_contr.organo_contratacion, c_contr.titulo_contrato, c_contr.fecha_adjudicacion, c_contr.importe_adjudicacion, c.phone, c.cnae_label, c.registro_mercantil, c.address');
        $builder->join('companies c', 'c.cif = c_contr.company_cif', 'left');

        if ($organo !== '') {
            $billingService = new \App\Services\BillingService();
            $builder->where('c_contr.organo_contratacion', $billingService->resolveContractsOrgano($organo));
        }
        if ($year !== '') {
            $builder->where('YEAR(c_contr.fecha_adjudicacion)', $year);
        }

        $query = $builder->get();
        while ($row = $query->getUnbufferedRow('array')) {
            $empresa = $row['company_name'] ?: $row['raw_adjudicatario'] ?: $row['company_cif'];
            fputcsv($fp, [
                $empresa,
                $row['company_cif'],
                $row['organo_contratacion'],
                $row['titulo_contrato'],
                $row['fecha_adjudicacion'] ? date('d/m/Y', strtotime($row['fecha_adjudicacion'])) : '',
                number_format((float)$row['importe_adjudicacion'], 2, ',', ''),
                $row['phone'] ?? '',
                $row['cnae_label'] ?? '',
                $row['registro_mercantil'] ?? '',
                $row['address'] ?? ''
            ]);
        }
    }

    private function processCompanies($db, $filters, $fp)
    {
        $province = $filters['provincia'] ?? '';
        $sector = $filters['sector'] ?? '';
        
        fputcsv($fp, ['CIF', 'Razón Social', 'Provincia', 'Sector', 'Teléfono', 'Email', 'Sitio Web']);

        $builder = $db->table('companies');
        $builder->select('cif, name, provincia, cnae_label, phone, email, website');

        if ($province !== '' && $province !== 'España') {
            $builder->where('provincia', $province);
        }
        if ($sector !== '' && $sector !== 'General') {
            $builder->like('cnae_label', $sector, 'both');
        }

        $query = $builder->get();
        while ($row = $query->getUnbufferedRow('array')) {
            fputcsv($fp, [
                $row['cif'],
                $row['name'],
                $row['provincia'],
                $row['cnae_label'],
                $row['phone'],
                $row['email'],
                $row['website']
            ]);
        }
    }
}
