<footer>
    <div class="container">
        <!-- BOTTOM ROW: Brand Info & Trust -->
        <div class="foot-bottom-brand">
            <div class="foot-brand-content">
                <div class="brand">
                    <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ve-g-en" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2152FF"/>
                                <stop offset=".65" stop-color="#5C7CFF"/>
                                <stop offset="1" stop-color="#12B48A"/>
                            </linearGradient>
                            <filter id="ve-cardShadow-en" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                            </filter>
                            <filter id="ve-checkShadow-en" x="-30%" y="-30%" width="160%" height="160%">
                                <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                            </filter>
                            <radialGradient id="ve-gloss-en" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                            gradientTransform="translate(20 16) rotate(45) scale(28)">
                                <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                            </radialGradient>
                            <linearGradient id="ve-rim-en" x1="12" y1="52" x2="52" y2="12">
                                <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                            </linearGradient>
                        </defs>
                        <g filter="url(#ve-cardShadow-en)">
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g-en)"/>
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss-en)"/>
                            <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim-en)"/>
                        </g>
                        <path d="M18 33 L28 43 L46 22"
                               stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                               fill="none" filter="url(#ve-checkShadow-en)"/>
                    </svg>
                    <div class="brand-text">
                        <span class="brand-name">SpainCompany<span class="grad">API</span></span>
                        <span class="brand-tag">Spanish Company Data API</span>
                    </div>
                </div>
                <p class="foot-desc">
                    Official data from BORME, AEAT, INE and VIES. Regulatory compliance and traceability for KYB/KYC processes and B2B billing.
                </p>
                <div class="foot-legal-row">
                    <a href="#" class="minor" data-modal-target="modalPrivacy">Privacy Policy</a> · 
                    <a href="#" class="minor" data-modal-target="modalTerms">Terms of Service</a>
                </div>
            </div>

            <!-- Trust Signals -->
            <div class="foot-trust foot-trust--minimal">
                <div class="trust-item">
                    <span class="trust-label">Secure payment</span>

                    <div class="trust-panel">
                        <div class="trust-panel__logos">
                            <img src="<?= base_url('public/images/stripe.png') ?>" alt="Stripe" loading="lazy" width="80" height="auto">
                            <span class="trust-panel__divider"></span>
                            <img src="<?= base_url('public/images/ssl.png') ?>" alt="SSL Secure" loading="lazy" width="80" height="auto">
                        </div>
                    </div>
                </div>

                <div class="trust-badges">
                    <div class="badge-item premium">
                        <div class="badge-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <span>Official Sources (BORME)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- AI CHAT BUBBLE -->
<?= view('partials/ai_chat_bubble') ?>

<?= view('partials/legal_modals') ?>

<?=view('scripts') ?>

<!-- Global Tracking System -->
<script>
    window.ae_base_url = '<?= site_url() ?>';
    window.ae_user_id = <?= session()->get('user_id') ? session()->get('user_id') : 'null' ?>;
</script>
<script src="<?= base_url('public/js/tracking.js') ?>?v=<?= time() ?>"></script>
