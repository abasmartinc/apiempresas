<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title' => 'Cargos directivos de ' . esc($adminName) . ' | APIEmpresas.es',
        'excerptText' => 'Descubre todas las empresas y cargos de ' . esc($adminName) . '. Historial completo y estado de las empresas.',
        'robots' => isset($robots) ? $robots : 'index,follow',
    ]) ?>
</head>
<body style="margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <?= $this->include('partials/header') ?>

<div style="background-color: #f8fafc; min-height: 100vh; padding-top: 2.5rem; padding-bottom: 2.5rem;">
    <div class="container mx-auto px-4 max-w-5xl" style="max-width: 1024px; margin: 0 auto; padding: 0 16px;">
        
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <ol class="breadcrumb" style="display: flex; gap: 0.5rem; list-style: none; padding: 0; margin: 0; font-size: 0.85rem; color: #64748b;">
                <li><a href="<?= site_url() ?>" style="color: #3b82f6; text-decoration: none;">Inicio</a></li>
                <li>/</li>
                <li><span style="color: #94a3b8;">Administradores</span></li>
                <li>/</li>
                <li aria-current="page" style="color: #1e293b; font-weight: 500;"><?= esc($adminName) ?></li>
            </ol>
        </nav>

        <!-- Admin Profile Header -->
        <div style="background-color: #ffffff; border-radius: 1rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; padding: 2rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem;">
            <div style="flex-shrink: 0; width: 6rem; height: 6rem; background: linear-gradient(to bottom right, #3b82f6, #4f46e5); border-radius: 9999px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 1.875rem; font-weight: 700; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                <?php
                // Generate initials
                $words = explode(' ', trim($adminName));
                $initials = '';
                foreach ($words as $i => $w) {
                    if ($i < 2 && !empty($w)) $initials .= strtoupper(substr($w, 0, 1));
                }
                echo esc($initials);
                ?>
            </div>
            <div>
                <h1 style="font-size: 1.875rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0;"><?= esc($adminName) ?></h1>
                <p style="color: #64748b; font-size: 1.125rem; margin: 0;">
                    Cargos directivos y vinculaciones empresariales
                </p>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0;">
                Empresas vinculadas a <?= esc($adminName) ?> (<?= count($companies) ?>)
            </h2>
        </div>

        <!-- Companies Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
            <?php foreach ($companies as $company): 
                // Determine status badge
                $statusBg = '#f1f5f9';
                $statusColor = '#475569';
                if (stripos($company['status'] ?? '', 'ACTIVA') !== false) {
                    $statusBg = '#d1fae5';
                    $statusColor = '#047857';
                } elseif (stripos($company['status'] ?? '', 'EXTINGUIDA') !== false || stripos($company['status'] ?? '', 'DISUELTA') !== false) {
                    $statusBg = '#ffe4e6';
                    $statusColor = '#be123c';
                }
                
                // Helper to get company url
                helper('company');
                $companyUrl = company_url($company);
            ?>
                <div style="background-color: #ffffff; border-radius: 0.75rem; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; padding: 1.5rem; display: flex; flex-direction: column; transition: box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)'" onmouseout="this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)'">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                        <a href="<?= esc($companyUrl) ?>" style="font-size: 1.125rem; font-weight: 700; color: #1d4ed8; text-decoration: none; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" onmouseover="this.style.color='#1e40af'; this.style.textDecoration='underline'" onmouseout="this.style.color='#1d4ed8'; this.style.textDecoration='none'">
                            <?= esc($company['company_name']) ?>
                        </a>
                        <span style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px; white-space: nowrap; background-color: <?= $statusBg ?>; color: <?= $statusColor ?>;">
                            <?= esc($company['status'] ?? 'Desconocido') ?>
                        </span>
                    </div>
                    
                    <div style="font-size: 0.875rem; color: #475569; margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.25rem; flex-grow: 1;">
                        <div><strong style="color: #334155;">CIF:</strong> <?= esc($company['cif'] ?? '-') ?></div>
                        <div><strong style="color: #334155;">Provincia:</strong> <?= esc($company['province'] ?? '-') ?></div>
                        <div style="overflow: hidden; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical;" title="<?= esc($company['cnae_label'] ?? '') ?>">
                            <strong style="color: #334155;">Sector:</strong> <?= esc($company['cnae_label'] ?? '-') ?>
                        </div>
                    </div>

                    <div style="border-top: 1px solid #f1f5f9; padding-top: 1rem; margin-top: auto;">
                        <h4 style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.5rem 0;">Cargos en esta empresa</h4>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <?php foreach ($company['positions'] as $pos): ?>
                                <div style="background-color: #f8fafc; border-radius: 0.25rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid #f1f5f9;">
                                    <div style="font-weight: 600; color: #334155; margin-bottom: 0.25rem;"><?= esc($pos['position'] ?? 'Cargo') ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; display: flex; justify-content: space-between;">
                                        <span>Última acción: <strong style="color: #475569;"><?= esc($pos['action']) ?></strong></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Privacy Disclaimer & Right to be Forgotten -->
        <div style="margin-top: 4rem; margin-bottom: 2rem; padding: 1.5rem; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; text-align: center;">
            <p style="font-size: 0.875rem; color: #64748b; margin: 0 0 0.5rem 0; line-height: 1.5;">
                Los datos mostrados en esta página provienen de fuentes públicas oficiales (BORME, Registro Mercantil) y se procesan bajo la base legal de interés legítimo para dotar de transparencia al tráfico mercantil, conforme a lo establecido por la AEPD.
            </p>
            <p style="font-size: 0.875rem; color: #64748b; margin: 0; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                ¿Eres tú el titular de estos datos? 
                <button onclick="document.getElementById('privacyModal').style.display='flex'" style="background: none; border: none; color: #3b82f6; font-weight: 600; font-size: 0.875rem; cursor: pointer; text-decoration: underline; padding: 0;">
                    Ejerce tu derecho de oposición (Derecho al Olvido)
                </button>
            </p>
        </div>

        <!-- Privacy Modal -->
        <div id="privacyModal" style="display: none; position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.75); z-index: 100000; align-items: center; justify-content: center; padding: 1rem; backdrop-filter: blur(4px);">
            <div style="background-color: #ffffff; width: 100%; max-width: 500px; border-radius: 1rem; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); position: relative;">
                <button onclick="document.getElementById('privacyModal').style.display='none'" style="position: absolute; top: 1rem; right: 1rem; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; font-size: 1.25rem; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#e2e8f0'; this.style.color='#0f172a'" onmouseout="this.style.backgroundColor='#f1f5f9'; this.style.color='#64748b'">&times;</button>
                
                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="width: 48px; height: 48px; background-color: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0;">Derecho al Olvido (RGPD)</h3>
                </div>

                <p style="font-size: 0.9375rem; color: #475569; margin-bottom: 1rem; line-height: 1.6;">
                    En cumplimiento de la normativa europea de protección de datos (RGPD), ponemos a tu disposición este canal directo para ejercer tu derecho de supresión/oposición y desindexar tu perfil de los motores de búsqueda.
                </p>

                <div style="background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 700; color: #334155; margin: 0 0 0.5rem 0;">Pasos a seguir:</h4>
                    <ol style="font-size: 0.875rem; color: #475569; margin: 0; padding-left: 1.25rem; line-height: 1.6;">
                        <li style="margin-bottom: 0.25rem;">Envíanos un email a <strong style="color: #0f172a;">soporte@apiempresas.es</strong></li>
                        <li style="margin-bottom: 0.25rem;">Adjunta una copia de tu DNI / NIE (puedes pixelar la foto). Esto es un <strong>requisito legal indispensable</strong> para asegurar que eres el titular y prevenir fraudes.</li>
                        <li>Incluye en el mensaje el enlace a esta página web.</li>
                    </ol>
                </div>

                <a href="mailto:soporte@apiempresas.es?subject=Ejercicio%20de%20Derecho%20al%20Olvido&body=Hola,%0A%0ASoy%20el%20titular%20de%20la%20siguiente%20página%20y%20deseo%20ejercer%20mi%20derecho%20al%20olvido%20para%20desindexar%20mi%20perfil%20de%20los%20motores%20de%20búsqueda:%0A%0AURL:%20<?= current_url() ?>%0A%0A[Adjunto%20mi%20DNI/NIE%20como%20prueba%20de%20identidad]." 
                   style="display: flex; align-items: center; justify-content: center; width: 100%; background-color: #2563eb; color: #ffffff; padding: 0.875rem; border-radius: 0.5rem; font-weight: 600; text-decoration: none; font-size: 0.9375rem; transition: background-color 0.2s;"
                   onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#2563eb'">
                    Enviar solicitud por Email
                </a>
            </div>
        </div>

    </div>
</div>

<?= $this->include('partials/footer') ?>
</body>
</html>
