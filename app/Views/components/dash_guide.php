<?php
/**
 * Guía en PDF para empezar con la API (29-09-2026).
 *
 * variant = 'banner'  → aviso destacado arriba del panel, solo Free sin ninguna consulta.
 * variant = 'sidebar' → tarjeta fija en la columna derecha, para todos.
 *
 * La guía está solo en español: no se enseña con el panel en otro idioma.
 * El PDF se sirve desde /public (los estáticos del sitio cuelgan de ahí).
 */
if (service('request')->getLocale() !== 'es') {
    return;
}
$variant  = $variant ?? 'sidebar';
$guideUrl = site_url('public/docs/guia-api-apiempresas.pdf') . '?source=dashboard_' . $variant;
$track    = "if(window.trackEvent){trackEvent('guide_pdf_opened',{source:'dashboard_" . $variant . "'});}";
?>
<?php if ($variant === 'banner'): ?>
<section style="display: flex; align-items: center; gap: 18px; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 16px; padding: 18px 22px; margin-bottom: 24px; flex-wrap: wrap;">
    <div style="background: #ffffff; color: #2152ff; width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #c7d2fe;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4h6a4 4 0 0 1 4 4v12a3 3 0 0 0-3-3H2z"></path><path d="M22 4h-6a4 4 0 0 0-4 4v12a3 3 0 0 1 3-3h7z"></path></svg>
    </div>
    <div style="flex: 1; min-width: 220px;">
        <p style="margin: 0 0 2px; font-size: 1rem; font-weight: 900; color: #0f172a;"><?= lang('Dashboard.guide_banner_title') ?></p>
        <p style="margin: 0; font-size: 0.85rem; font-weight: 600; color: #475569; line-height: 1.45;"><?= lang('Dashboard.guide_banner_text') ?></p>
    </div>
    <a href="<?= esc($guideUrl, 'attr') ?>" target="_blank" rel="noopener" onclick="<?= $track ?>" style="display: inline-block; background: #ffffff; color: #2152ff; border: 2px solid #2152ff; padding: 10px 18px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; text-decoration: none; white-space: nowrap;">
        <?= lang('Dashboard.guide_button') ?>
    </a>
</section>
<?php else: ?>
<section class="dash-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-top: 24px;">
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
        <div style="background: #eff6ff; color: #2152ff; padding: 12px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4h6a4 4 0 0 1 4 4v12a3 3 0 0 0-3-3H2z"></path><path d="M22 4h-6a4 4 0 0 0-4 4v12a3 3 0 0 1 3-3h7z"></path></svg>
        </div>
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 900; color: #0f172a; margin: 0 0 4px !important;"><?= lang('Dashboard.guide_sidebar_title') ?></h3>
            <p style="font-size: 0.85rem; color: #64748b; font-weight: 600; margin: 0 !important; line-height: 1.4;"><?= lang('Dashboard.guide_sidebar_text') ?></p>
        </div>
    </div>
    <a href="<?= esc($guideUrl, 'attr') ?>" target="_blank" rel="noopener" onclick="<?= $track ?>" style="display: block; width: 100%; text-align: center; background: #2152ff; color: #ffffff; padding: 12px; border-radius: 10px; font-weight: 800; font-size: 0.95rem; text-decoration: none;">
        <?= lang('Dashboard.guide_button') ?>
    </a>
</section>
<?php endif; ?>
