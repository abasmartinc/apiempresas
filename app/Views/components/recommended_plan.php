<?php
/**
 * Recommended Plan Component (Max Conversion)
 * This component adapts based on the user's current plan.
 */

// Safety check for variables if not passed explicitly
if (!isset($currentPlanSlug)) {
    $currentPlanSlug = 'free'; // Fallback
}
if (!isset($isPaid)) {
    $isPaid = ($currentPlanSlug !== 'free' && !empty($currentPlanSlug));
}

// Logic for what to recommend
$recoPlan = 'Pro';
$recoPrice = '19€';
$recoQueries = '3.000';
$recoFeatures = ['API completa + Radar B2B', 'Validación automática', 'Soporte prioritario'];
$recoDesc = 'Ideal para automatizar validaciones en tu CRM o sistema.';
$recoCta = 'Activar Pro ahora';
$recoValue = 'Ahorra +10h de validación manual';

if ($isPaid && strpos($currentPlanSlug, 'pro') !== false) {
    // Already on Pro, recommend Business
    $recoPlan = 'Business';
    $recoPrice = '49€';
    $recoQueries = '10.000+';
    $recoFeatures = ['SLA Avanzado (99.9%)', 'Gestión de equipos', 'Consultoría técnica'];
    $recoDesc = 'Para volumen alto, roles avanzados y soporte dedicado.';
    $recoCta = 'Mejorar a Business';
    $recoValue = 'Escalabilidad sin límites';
}
?>

<section class="dash-card plan-card--recommended" style="border: 1px solid #e2e8f0; position: relative; background: #ffffff; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02); overflow: hidden; border-radius: 16px;">
    <div style="position: absolute; top: -1px; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, #2152ff, #12b48a);"></div>
    
    <div style="padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <div style="color: #2152ff; font-weight: 900; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 1.1rem;">🔥</span> Escala hoy
            </div>
            <div style="background: #2152ff; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em;">
                Recomendado
            </div>
        </div>

        <h2 style="font-size: 1.75rem; font-weight: 950; margin: 0 0 4px !important; letter-spacing: -0.03em; color: #0f172a;">Plan <?= $recoPlan ?></h2>
        <p style="margin-bottom: 20px; color: #64748b; font-weight: 600; font-size: 0.95rem; line-height: 1.4;"><?= $recoDesc ?></p>

        <div style="background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 24px; border: 1px solid #f1f5f9;">
            <ul style="list-style: none; padding: 0; margin: 0; display: grid; gap: 12px;">
                <li style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; font-weight: 700; color: #334155;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    <?= $recoQueries ?> consultas / mes
                </li>
                <?php foreach($recoFeatures as $feat): ?>
                <li style="display: flex; align-items: center; gap: 10px; font-size: 0.9rem; font-weight: 700; color: #334155;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    <?= $feat ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div style="margin-bottom: 24px;">
            <div style="font-size: 2.8rem; font-weight: 950; color: #0f172a; margin-bottom: 0; line-height: 1; letter-spacing: -0.04em;">
                <?= $recoPrice ?> <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 700; letter-spacing: 0;">/mes</span>
            </div>
            <div style="font-size: 0.85rem; color: #6366f1; font-weight: 800; margin-top: 8px; display: flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?= $recoValue ?>
            </div>
        </div>

        <a href="<?= site_url('billing') ?>" class="btn primary" style="background: #10b981; color: white !important; width: 100%; display: block; text-align: center; text-decoration: none; padding: 18px; border-radius: 12px; font-weight: 900; font-size: 1.15rem; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3); transition: all 0.2s transform;">
            <?= $recoCta ?>
        </a>

        <div style="margin-top: 20px; text-align: center; font-size: 0.75rem; color: #94a3b8; font-weight: 700; display: flex; flex-direction: column; gap: 6px;">
            <span style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                Sin permanencia · Cancela cuando quieras
            </span>
            <span style="display: flex; align-items: center; justify-content: center; gap: 4px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                Activación instantánea
            </span>
        </div>
    </div>
</section>
