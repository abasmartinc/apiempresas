<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Dompdf\Dompdf;
use Dompdf\Options;

class GenerateSamplePdfs extends BaseCommand
{
    protected $group = 'App';
    protected $name = 'samples:generate';
    protected $description = 'Genera los PDFs de muestra estáticos para el Dictamen de Riesgo y el Dossier 360.';

    public function run(array $params)
    {
        CLI::write("Generando PDFs de muestra...", 'yellow');

        $sampleCompany = [
            'id' => 999999,
            'name' => 'IBÉRICA DE CONSTRUCCIONES Y SERVICIOS S.L.',
            'cif' => 'B87654321',
            'status' => 'ACTIVA',
            'actividad' => 'Construcción de edificios residenciales y obra civil',
            'cnae' => '4121',
            'cnae_label' => 'Construcción de edificios residenciales',
            'capital' => '60.000,00 €',
            'address' => 'Paseo de la Castellana 140, Planta 6',
            'municipio' => 'Madrid',
            'provincia' => 'Madrid',
            'postal_code' => '28046',
            'created_at' => '2015-03-12 10:00:00',
            'fecha_constitucion' => '12/03/2015',
            'num_empleados' => '24',
            'ventas_raw' => 2450000,
            'total_activos' => 1850000,
            'telefono' => '91 000 00 00',
            'email' => 'contacto@ejemplo-iberica.es',
            'web' => 'https://www.ejemplo-iberica.es',
            'ai_borme_summary' => 'Sociedad constituida en 2015 con capital suscrito de 60.000 €. Muestra un historial registral ordenado, con depósito puntual de cuentas anuales y estabilidad en su órgano de administración, sin incidencias concursales.'
        ];

        $sampleRiskProfile = [
            'risk_score' => 28, // Semáforo Verde / Favorable
            'risk_level' => 'BAJO',
            'confidence' => 92,
            'last_updated' => date('Y-m-d H:i:s'),
            'data' => [
                'financial_health' => [
                    'score' => 85,
                    'summary' => 'Equilibrio financiero sólido, sin tensiones de tesorería registradas.'
                ],
                'borme_events' => [
                    'total' => 14,
                    'high_severity' => 0,
                    'summary' => 'Historial registral ordenado sin incidencias ni ejecuciones.'
                ],
                'contract_history' => [
                    'total_contracts' => 6,
                    'total_amount' => '450.200 €'
                ]
            ]
        ];

        $sampleBormePosts = [
            [
                'borme_date' => '2025-11-20',
                'act_types' => 'Cuentas Anuales',
                'description' => 'Depósito de cuentas anuales debidamente calificadas y aprobadas en Registro Mercantil de Madrid.'
            ],
            [
                'borme_date' => '2025-06-15',
                'act_types' => 'Nombramientos',
                'description' => 'Reelección de Gómez Serrano, Fernando como Administrador Único por un periodo de 5 años.'
            ],
            [
                'borme_date' => '2023-09-10',
                'act_types' => 'Modific. de Capital',
                'description' => 'Ampliación de capital social en 30.000,00 euros, resultando un capital suscrito de 60.000,00 euros.'
            ],
            [
                'borme_date' => '2015-03-12',
                'act_types' => 'Constitución',
                'description' => 'Comienzo de operaciones: 12.03.15. Objeto social: Construcción de edificios residenciales. Domicilio: Paseo de la Castellana 140 (Madrid).'
            ]
        ];

        $sampleContracts = [
            [
                'organo_contratacion' => 'Ayuntamiento de Madrid - Área de Obras',
                'titulo_contrato' => 'Mantenimiento y rehabilitación de pavimentos distrito Chamartín',
                'objeto' => 'Mantenimiento y rehabilitación de pavimentos distrito Chamartín',
                'importe_adjudicacion' => 185450.00,
                'importe' => '185.450,00 €',
                'importe_raw' => 185450,
                'fecha_adjudicacion' => '2025-04-18',
                'estado' => 'Adjudicado y formalizado'
            ],
            [
                'organo_contratacion' => 'Comunidad de Madrid - Consejería de Vivienda',
                'titulo_contrato' => 'Adecuación de envolventes térmicas en inmuebles públicos',
                'objeto' => 'Adecuación de envolventes térmicas en inmuebles públicos',
                'importe_adjudicacion' => 142300.00,
                'importe' => '142.300,00 €',
                'importe_raw' => 142300,
                'fecha_adjudicacion' => '2024-09-05',
                'estado' => 'Ejecutado'
            ]
        ];

        $sampleSubsidies = [
            [
                'organo_concedente' => 'Ministerio de Industria - CDTI',
                'convocatoria' => 'Programa de digitalización y eficiencia energética',
                'instrumento' => 'Subvención a fondo perdido',
                'finalidad' => 'Programa de digitalización y eficiencia energética',
                'importe' => 35000.00,
                'importe_raw' => 35000,
                'fecha_concesion' => '2024-02-14'
            ]
        ];

        $sampleAdmins = [
            [
                'name' => 'GÓMEZ SERRANO, FERNANDO',
                'position' => 'ADMINISTRADOR ÚNICO',
                'date' => '2025-06-15'
            ],
            [
                'name' => 'NAVARRO LÓPEZ, BEATRIZ',
                'position' => 'APODERADO SOLIDARIO',
                'date' => '2020-01-20'
            ]
        ];

        $sampleRiskTrend = [
            'has_trend' => true,
            'points' => [
                ['date' => 'Oct 2025', 'score' => 34, 'label' => 'Bajo'],
                ['date' => 'Dic 2025', 'score' => 30, 'label' => 'Bajo'],
                ['date' => 'Hoy', 'score' => 28, 'label' => 'Bajo (Mejora)']
            ],
            'variation' => -6,
            'trend_text' => 'Estabilidad favorable con tendencia de mejora continua en los últimos 6 meses.'
        ];

        // Opciones Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $outDirs = [
            ROOTPATH . 'public' . DIRECTORY_SEPARATOR . 'ejemplos' . DIRECTORY_SEPARATOR,
            ROOTPATH . 'ejemplos' . DIRECTORY_SEPARATOR,
        ];
        foreach ($outDirs as $d) {
            if (!is_dir($d)) {
                mkdir($d, 0755, true);
            }
        }

        // =========================================================================
        // A) GENERAR PDF 1: DICTAMEN DE RIESGO Y SOLVENCIA (3,90 €)
        // =========================================================================
        $htmlRisk = view('reports/risk_pdf_report', [
            'company'         => $sampleCompany,
            'riskProfile'     => $sampleRiskProfile,
            'contracts'       => $sampleContracts,
            'subsidies'       => $sampleSubsidies,
            'bormePosts'      => $sampleBormePosts,
            'riskTrend'       => $sampleRiskTrend,
            'brandName'       => 'APIEmpresas',
            'brandColor'      => '#0f172a',
            'brandFooterText' => 'DOCUMENTO DE MUESTRA | Generado por APIEmpresas'
        ]);

        $watermark = '<div style="position: fixed; top: 40%; left: 5%; width: 90%; text-align: center; opacity: 0.10; transform: rotate(-30deg); font-size: 50pt; font-weight: 900; color: #0f172a; pointer-events: none; z-index: 9999;">DOCUMENTO DE MUESTRA</div>';
        $htmlRisk = str_replace('</body>', $watermark . '</body>', $htmlRisk);

        $dompdfRisk = new Dompdf($options);
        $dompdfRisk->loadHtml($htmlRisk);
        $dompdfRisk->setPaper('A4', 'portrait');
        $dompdfRisk->render();
        $riskPdfBytes = $dompdfRisk->output();

        foreach ($outDirs as $d) {
            file_put_contents($d . 'ejemplo-informe-riesgo-solvencia.pdf', $riskPdfBytes);
            file_put_contents($d . 'informe-riesgo-muestra.pdf', $riskPdfBytes);
            CLI::write("SUCCESS: Dictamen de Riesgo generado en: " . $d, 'green');
        }

        // =========================================================================
        // B) GENERAR PDF 2: DOSSIER INTEGRAL 360º (5,90 €)
        // =========================================================================
        $htmlDossier = view('reports/company_pdf_premium', [
            'company'         => $sampleCompany,
            'administrators'  => $sampleAdmins,
            'bormePosts'      => $sampleBormePosts,
            'radarScore'      => [
                'final_score' => 85,
                'explanation' => 'Excelente dinamismo comercial, cumplimiento registral impecable y solvencia demostrada.',
                'visuals' => [
                    'bg' => '#f0fdf4',
                    'color' => '#16a34a',
                    'label' => 'Oportunidad Alta',
                    'icon' => '🟢'
                ]
            ],
            'contracts'       => $sampleContracts,
            'subsidies'       => $sampleSubsidies,
            'riskProfile'     => $sampleRiskProfile,
            'brandColor'      => '#1e293b',
            'brandName'       => 'APIEmpresas',
            'brandFooterText' => 'DOCUMENTO DE MUESTRA - DOSSIER INTEGRAL 360º | APIEmpresas',
            'brandLogoBase64' => '',
            'qrBase64'        => ''
        ]);

        $htmlDossier = str_replace('</body>', $watermark . '</body>', $htmlDossier);

        $dompdfDossier = new Dompdf($options);
        $dompdfDossier->loadHtml($htmlDossier);
        $dompdfDossier->setPaper('A4', 'portrait');
        $dompdfDossier->render();

        $dossierPdfBytes = $dompdfDossier->output();
        foreach ($outDirs as $d) {
            file_put_contents($d . 'ejemplo-dossier-integral-360.pdf', $dossierPdfBytes);
            file_put_contents($d . 'dossier-360-muestra.pdf', $dossierPdfBytes);
            CLI::write("SUCCESS: Dossier 360 generado en: " . $d, 'green');
        }

        CLI::write("¡Todos los PDFs de muestra generados correctamente!", 'green');
    }
}
