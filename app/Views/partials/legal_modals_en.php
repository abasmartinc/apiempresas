<!-- =========================
     MODAL · PRIVACY POLICY
     ========================= -->
<div class="modal-overlay" id="modalPrivacy" aria-hidden="true">
    <div class="modal modal-wow" role="dialog" aria-modal="true" aria-labelledby="privacyTitle" tabindex="-1">
        <div class="modal-header">
            <div>
                <div class="modal-kicker">Legal</div>
                <h2 class="modal-title" id="privacyTitle">Privacy Policy</h2>
                <p class="modal-sub">How we collect, use, and protect your information.</p>
            </div>
            <button class="modal-close" type="button" aria-label="Close" data-close-modal>✕</button>
        </div>

        <div class="modal-body modal-legal">
            <h3>1. Identity of the Data Controller</h3>
            <p>The data controller of the personal data collected on this platform is <strong>Ariel Martinez Hernandez</strong> (hereinafter, "SpainCompanyAPI"), with NIF 54994158P and address at República Dominicana 40, Bajo E, 28983, Parla (Madrid). You can contact us at <strong>soporte@apiempresas.es</strong>.</p>

            <h3>2. Purposes and Legal Basis</h3>
            <p>We process your personal data for the following purposes and legal bases:</p>
            <ul>
                <li><strong>Provision of the service and account management:</strong> Necessary for the performance of the contract (Terms of Use) upon registration.</li>
                <li><strong>Billing and collections:</strong> Compliance with legal and tax obligations.</li>
                <li><strong>Fraud prevention and security:</strong> Based on our legitimate interest to keep the platform secure.</li>
                <li><strong>Commercial communications and marketing:</strong> We rely on our <strong>legitimate interest</strong> to send you information about products or services similar to those contracted. You can object to this processing by checking the corresponding box during registration or unsubscribing at any time.</li>
            </ul>

            <h3>3. Data Retention</h3>
            <p>The data will be kept as long as you maintain your account active and it is necessary to provide the service. Once canceled, they will be kept duly blocked for the period required by applicable legislation (generally up to 5 years for tax and legal reasons).</p>

            <h3>4. Recipients and International Transfers</h3>
            <p>We do not sell your data to third parties. We may transfer them to strictly necessary providers (hosting, analytics, payment gateway such as Stripe). Some of these providers (e.g., Google, GitHub) may transfer data outside the EEA. We ensure they comply with the GDPR through standard contractual clauses or equivalent agreements.</p>
            <p><strong>Cloudflare Turnstile:</strong> We use Cloudflare Turnstile to protect our forms against spam and automated abuse. The use of Turnstile is subject to the <a href="https://www.cloudflare.com/website-terms/" target="_blank" rel="noopener noreferrer">Cloudflare Privacy Policy and Terms</a>.</p>

            <h3>5. Your Rights (ARCO-POL)</h3>
            <p>You have the right to obtain confirmation as to whether we are processing your data. You also have the right to:</p>
            <ul>
                <li>Access your personal data.</li>
                <li>Request its rectification if it is inaccurate.</li>
                <li>Request its deletion when it is no longer necessary.</li>
                <li>Request the limitation or object to its processing.</li>
                <li>Request data portability.</li>
            </ul>
            <p>You can exercise them by sending an email to <strong>soporte@apiempresas.es</strong>. Likewise, if you consider that your data has not been properly processed, you have the right to file a claim with the Spanish Data Protection Agency (AEPD).</p>

            <div class="modal-note">
                <strong>Last updated:</strong> <?= date('m/d/Y') ?>
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn" type="button" data-close-modal>Close</button>
            <button class="modal-btn primary" type="button" data-close-modal>Understood</button>
        </div>
    </div>
</div>

<!-- =========================
     MODAL · LEGAL NOTICE & TERMS OF USE
     ========================= -->
<div class="modal-overlay" id="modalTerms" aria-hidden="true">
    <div class="modal modal-wow" role="dialog" aria-modal="true" aria-labelledby="termsTitle" tabindex="-1">
        <div class="modal-header">
            <div>
                <div class="modal-kicker">Legal</div>
                <h2 class="modal-title" id="termsTitle">Legal Notice & Terms of Use</h2>
                <p class="modal-sub">Legal conditions for accessing and using the platform.</p>
            </div>
            <button class="modal-close" type="button" aria-label="Close" data-close-modal>✕</button>
        </div>

        <div class="modal-body modal-legal">
            <h3>1. General Information (LSSI-CE)</h3>
            <p>In compliance with Law 34/2002 on Information Society Services and Electronic Commerce (LSSI-CE), it is reported that the owner of the SpainCompanyAPI web platform is:</p>
            <ul>
                <li><strong>Owner:</strong> Ariel Martinez Hernandez</li>
                <li><strong>NIF:</strong> 54994158P</li>
                <li><strong>Address:</strong> República Dominicana 40, Bajo E, 28983, Parla (Madrid)</li>
                <li><strong>Contact:</strong> soporte@apiempresas.es</li>
            </ul>

            <h3>2. Acceptance</h3>
            <p>By accessing or registering on the service, you acquire the status of User and accept these terms of use in their entirety. If you do not agree, do not use the platform.</p>

            <h3>3. Permitted Use Conditions</h3>
            <p>You agree to make appropriate and lawful use of the service. It is strictly prohibited to:</p>
            <ul>
                <li>Attempt to access systems or data without authorization.</li>
                <li>Interfere with operation by abusing rate limits, malicious scraping, or cyber attacks.</li>
                <li>Use the service for illegal purposes or those that harm the rights of third parties.</li>
            </ul>

            <h3>4. Account and Security</h3>
            <p>You are solely responsible for maintaining the confidentiality of your credentials (password, API keys) and for all activity that occurs under your account.</p>

            <h3>5. Intellectual Property</h3>
            <p>All content on the platform (design, code, structure, and databases) is the exclusive property of Ariel Martinez Hernandez or has the corresponding licenses. Reproduction, distribution, or modification is not allowed without express authorization.</p>

            <h3>6. Limitation of Liability</h3>
            <p>The platform is provided "as is." Within the applicable legal framework, SpainCompanyAPI is not liable for direct or indirect damages derived from the use of information obtained through the API, nor for potential service interruptions due to external causes.</p>

            <h3>7. Jurisdiction and Applicable Law</h3>
            <p>These conditions are governed by Spanish law. For the resolution of any dispute derived from the use of the service, the parties submit to the courts and tribunals of the city of Madrid, expressly waiving any other jurisdiction that may apply.</p>

            <div class="modal-note">
                <strong>Last updated:</strong> <?= date('m/d/Y') ?>
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn" type="button" data-close-modal>Close</button>
            <button class="modal-btn primary" type="button" data-close-modal>Accept</button>
        </div>
    </div>
</div>
