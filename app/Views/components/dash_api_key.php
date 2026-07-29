<!-- API KEY SECTION -->
<section class="activation-main-card" id="section-api-key" data-track-section="api_key_block" style="margin-top: 32px; position: relative; <?= $requestsUsedThisMonth > 0 ? 'border-color: #2152ff; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.1);' : 'opacity: 0.8;' ?>">
    <div style="position: absolute; top: -14px; left: 32px; background: <?= $requestsUsedThisMonth > 0 ? '#2152ff' : '#94a3b8' ?>; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);"><?= lang('Dashboard.step_2') ?></div>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; margin-top: 10px;">
        <div>
            <h2 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0 0 8px !important;"><?= lang('Dashboard.copy_api_key') ?></h2>
            <p style="font-size: 0.95rem; color: #64748b; font-weight: 600; margin: 0;">
                <?= $requestsUsedThisMonth > 0 ? lang('Dashboard.use_key_connect') : lang('Dashboard.make_first_search') ?>
            </p>
        </div>
        <button type="button" class="btn-small" id="btnRotateKey" style="color: #64748b; border-color: #e2e8f0; font-size: 0.75rem; padding: 6px 12px;"><?= lang('Dashboard.regenerate_key') ?></button>
    </div>
    
    <div class="apikey-row" style="margin-top: 24px;">
        <div class="apikey-box" id="apiKeyBox" data-api-key="<?=htmlspecialchars($api_key->api_key ?? '') ?>">
            <div>
                <div class="apikey-label">API KEY</div>
                <div class="apikey-value" id="apiKeyMasked"><?=htmlspecialchars($api_key->api_key ?? '') ?></div>
            </div>
        </div>
        <div class="apikey-actions">
            <button type="button" class="btn-small" id="btnToggleKey"><?= lang('Dashboard.show') ?></button>
            <button type="button" class="btn-small primary" id="btnCopyKey" <?= $requestsUsedThisMonth == 0 ? 'style="background: #94a3b8; border-color: #94a3b8;"' : '' ?>><?= lang('Dashboard.copy') ?></button>
        </div>
    </div>
</section>
