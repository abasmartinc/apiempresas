<?php if (($walletBalance ?? 0) > 0 || ($walletTotal ?? 0) > 0): ?>
<section class="dash-card" style="border-top: 4px solid #10b981; background: #f0fdf4; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
        <div class="kicker" style="background: #d1fae5; color: #065f46; font-weight: 800; padding: 4px 10px; display: inline-block; border-radius: 6px; font-size: 0.7rem; letter-spacing: 0.05em; margin-bottom: 0;">
            💎 MONEDERO PREPAGO
        </div>
        <?php if ($walletLowBalance ?? false): ?>
            <a href="<?= site_url('billing/checkout_bonus') ?>" style="background: #10b981; color: white; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.7rem; text-decoration: none; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);">Recargar &rarr;</a>
        <?php endif; ?>
    </div>
    
    <div style="margin-bottom: 16px;">
        <span style="font-size: 0.8rem; color: #064e3b; font-weight: 700; text-transform: uppercase;">Saldo Disponible</span>
        <h2 style="margin-top: 2px !important; color: #064e3b; font-size: 2.2rem; font-weight: 900; display: flex; align-items: baseline; gap: 6px; margin-bottom: 0 !important;">
            <?= number_format($walletBalance ?? 0, 0, ',', '.') ?>
            <span style="font-size: 0.9rem; color: #059669; font-weight: 800;">créditos</span>
        </h2>
    </div>

    <div class="progress-container" style="margin-bottom: 10px;">
        <div class="progress-bar-bg" style="height: 10px; background: #d1fae5; border-radius: 6px; overflow: hidden;">
            <?php $spentPct = (($walletTotal ?? 0) > 0) ? ((($walletSpent ?? 0) / $walletTotal) * 100) : 0; ?>
            <div style="width: <?= $spentPct ?>%; background: <?= ($walletLowBalance ?? false) ? '#ef4444' : '#10b981' ?>; height: 100%; border-radius: 6px; transition: width 0.5s ease-in-out;"></div>
        </div>
    </div>
    
    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 800; color: #047857;">
        <span>Comprado: <?= number_format($walletTotal ?? 0, 0, ',', '.') ?></span>
        <span>Gastado: <?= number_format($walletSpent ?? 0, 0, ',', '.') ?></span>
    </div>
</section>
<?php endif; ?>

<?php if (!isset($isBonusUser) || !$isBonusUser): ?>
<?php if (!$isPaid): ?>
<section class="dash-card" style="border-top: 4px solid #94a3b8; background: #ffffff; margin-bottom: 24px;">
    <div class="kicker" style="background: #f1f5f9; color: #475569; display: inline-block; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 0.7rem; letter-spacing: 0.05em; margin-bottom: 8px;">
        ⚠️ ESTÁS EN PLAN FREE
    </div>
    <h2 style="margin-top: 12px !important;">Ideal para probar la API</h2>
    <p style="color: #0f172a; font-size: 0.95rem; margin-bottom: 12px; font-weight: 800; border-left: 3px solid #2152ff; padding-left: 12px;">
        Te quedan <?= $remainingRequests ?> consultas gratuitas
    </p>
    <p style="color: #64748b; font-size: 0.8rem; font-weight: 600; margin-bottom: 20px;">
        <?= $remainingRequests <= 0 ? 'Has alcanzado el límite gratuito. Activa Pro para seguir validando empresas automáticamente.' : 'Cuando se acaben, necesitarás activar Pro para seguir validando empresas.' ?>
    </p>
    <a href="<?= site_url('billing') ?>" class="btn primary" style="width: 100%; display: block; text-align: center; text-decoration: none; padding: 14px; font-weight: 800; background: #10b981; border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
        Activar Pro
    </a>
</section>

<?php if ($requestsUsedThisMonth >= 3): ?>
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; margin-bottom: 32px; border-left: 4px solid #2152ff; margin-top: 32px;">
        <p style="margin: 0; font-size: 0.85rem; color: #0f172a; font-weight: 900;">Ya estás viendo el valor real</p>
        <p style="margin: 0; font-size: 0.75rem; color: #64748b; font-weight: 700;">Activa Pro y automatiza validaciones sin límite</p>
    </div>
<?php endif; ?>

<?= view('components/recommended_plan', ['currentPlanSlug' => $currentPlanSlug, 'isPaid' => $isPaid]) ?>

<!-- Bono Prepago CTA para Free -->
<section class="dash-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
    <div style="display: flex; align-items: flex-start; gap: 16px;">
        <div style="background: #fffbeb; color: #d97706; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #fde68a;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
        </div>
        <div>
            <div class="kicker" style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 900; letter-spacing: 0.05em; display: inline-block; margin-bottom: 6px;">PAGO POR USO</div>
            <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; margin: 0 0 4px !important;">Bonos Prepago</h3>
            <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin: 0 0 12px !important; line-height: 1.4;">¿Prefieres ir a tu ritmo? Compra saldo de consultas sin suscripción.</p>
            <a href="<?= base_url('crear-bono-api') ?>" style="display: inline-block; color: #d97706; font-weight: 800; font-size: 0.85rem; text-decoration: none; border-bottom: 2px solid rgba(217, 119, 6, 0.2); transition: all 0.2s;" onmouseover="this.style.borderColor='#d97706'" onmouseout="this.style.borderColor='rgba(217, 119, 6, 0.2)'">
                Ver Bonos &rarr;
            </a>
        </div>
    </div>
</section>

<?php else: ?>
<?php
    $isBusiness = (stripos($planNameRaw ?? '', 'business') !== false);
    $bgColor = $isBusiness ? '#059669' : '#0284c7'; // Verde o Azul Pro
    $planName = esc($planNameRaw ?? 'Pro');
?>
<section style="background: <?= $bgColor ?>; border-radius: 16px; padding: 24px; color: #ffffff; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);">
    <div style="background: rgba(255,255,255,0.2); color: #ffffff; display: inline-block; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 0.7rem; letter-spacing: 0.05em; margin-bottom: 12px; text-transform: uppercase;">
        PLAN ACTUAL <?= $planName ?>
    </div>
    
    <h2 style="color: #ffffff; font-size: 2.2rem; font-weight: 900; margin-bottom: 16px; margin-top: 0 !important;">
        <?= $planName ?>
    </h2>
    
    <div style="border-left: 2px solid rgba(255,255,255,0.4); padding-left: 12px; margin-bottom: 16px;">
        <p style="color: #ffffff; font-weight: 800; margin: 0; font-size: 0.95rem;">Límite: <?= number_format($maxLimit ?? 3000, 0, ',', '.') ?> consultas/mes</p>
    </div>
    
    <p style="color: rgba(255,255,255,0.9); font-size: 0.85rem; line-height: 1.5; font-weight: 500; margin-bottom: 24px;">
        Disfrutas de todas las ventajas del Plan <?= $planName ?>, incluyendo métricas avanzadas, soporte prioritario y SLA garantizado.
    </p>
    
    <a href="<?= site_url('billing') ?>" style="display: block; text-align: center; background: #ffffff; color: #0f172a; padding: 14px; border-radius: 12px; font-weight: 900; font-size: 0.95rem; text-decoration: none; transition: transform 0.2s;">
        Gestionar suscripción
    </a>
</section>

<?php if ($currentPlanSlug === 'pro'): ?>
    <?= view('components/recommended_plan', ['currentPlanSlug' => $currentPlanSlug, 'isPaid' => $isPaid]) ?>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

