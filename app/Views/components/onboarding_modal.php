<?php
/**
 * Onboarding Modal Component
 */
?>
<div id="onboarding-modal" style="display:none; position:fixed; inset:0; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px); z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; animation: fadeIn 0.3s ease;">
    <div id="onboarding-step-1" class="dash-card" style="width: 100%; max-width: 500px; padding: 40px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <div style="background: #eff6ff; color: #2152ff; width: 64px; height: 64px; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 2rem;">🚀</div>
        <h2 style="font-size: 1.75rem; font-weight: 950; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.02em;">Vamos a encontrar oportunidades para ti</h2>
        <p style="color: #64748b; font-weight: 600; margin-bottom: 32px;">Configura tus preferencias para ver empresas nuevas en tu sector hoy mismo.</p>
        
        <div style="text-align: left; display: grid; gap: 20px; margin-bottom: 32px;">
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Tu Sector</label>
                <select id="onb-sector" style="width:100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-weight: 600; font-family: inherit;">
                    <option value="Hostelería y Restauración">Hostelería y Restauración</option>
                    <option value="Construcción e Inmobiliaria">Construcción e Inmobiliaria</option>
                    <option value="Tecnología y Software">Tecnología y Software</option>
                    <option value="Marketing y Publicidad">Marketing y Publicidad</option>
                    <option value="Logística y Transporte">Logística y Transporte</option>
                    <option value="Servicios a Empresas">Servicios a Empresas</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Tu Provincia</label>
                <select id="onb-province" style="width:100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-weight: 600; font-family: inherit;">
                    <option value="Madrid">Madrid</option>
                    <option value="Barcelona">Barcelona</option>
                    <option value="Valencia">Valencia</option>
                    <option value="Sevilla">Sevilla</option>
                    <option value="Málaga">Málaga</option>
                    <option value="Vizcaya">Vizcaya</option>
                </select>
            </div>
        </div>

        <button id="onb-btn-next" class="btn primary" style="width: 100%; padding: 16px; border-radius: 12px; font-weight: 900; font-size: 1.1rem; background: #2152ff; border: none; color: white;">
            Buscar oportunidades &rarr;
        </button>
    </div>

    <div id="onboarding-step-2" class="dash-card" style="width: 100%; max-width: 500px; padding: 40px; text-align: center; display: none;">
        <div id="onb-loader" style="padding: 40px 0;">
            <div style="width: 40px; height: 40px; border: 4px solid #f1f5f9; border-top-color: #2152ff; border-radius: 50%; margin: 0 auto; animation: api-spin 0.8s linear infinite;"></div>
            <p style="margin-top: 16px; font-weight: 800; color: #0f172a;">Analizando mercado en <span id="onb-display-province">...</span></p>
        </div>

        <div id="onb-results" style="display: none;">
            <div style="background: #f0fdf4; color: #166534; width: 64px; height: 64px; border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 2rem;">🔥</div>
            <h2 style="font-size: 1.75rem; font-weight: 950; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.02em;"><span id="onb-res-count">23</span> empresas nuevas encontradas</h2>
            
            <div style="background: #fff7ed; border: 1px solid #ffedd5; padding: 20px; border-radius: 16px; margin-bottom: 32px;">
                <div style="color: #c2410c; font-weight: 900; margin-bottom: 8px;">⚠️ <span id="onb-res-contacted">17</span> ya han sido contactadas</div>
                <div style="color: #9a3412; font-weight: 800; font-size: 0.9rem;">Solo quedan <span id="onb-res-left">6</span> disponibles para contactar ahora.</div>
            </div>

            <p style="color: #64748b; font-weight: 700; margin-bottom: 24px;">Estas oportunidades se actualizan cada día en <span id="onb-display-sector">...</span>. Activa Radar para no perderlas.</p>

            <div style="display: grid; gap: 12px;">
                <button id="onb-btn-radar" class="btn primary" style="width: 100%; padding: 18px; border-radius: 12px; font-weight: 900; font-size: 1.1rem; background: #2152ff; border: none; color: white;">
                    🔥 Obtener acceso a estas <span id="onb-btn-res-count">...</span> empresas
                </button>
                <button id="onb-btn-close" style="background: none; border: none; color: #94a3b8; font-weight: 700; font-size: 0.9rem; cursor: pointer;">Ir al Dashboard</button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('onboarding-modal');
    if(!modal) return;

    if(localStorage.getItem('onboarding_completed')) {
        modal.style.display = 'none';
        return;
    } else {
        modal.style.display = 'flex';
    }

    const step1 = document.getElementById('onboarding-step-1');
    const step2 = document.getElementById('onboarding-step-2');
    const btnNext = document.getElementById('onb-btn-next');
    const btnRadar = document.getElementById('onb-btn-radar');
    const btnClose = document.getElementById('onb-btn-close');
    const loader = document.getElementById('onb-loader');
    const results = document.getElementById('onb-results');

    btnNext.addEventListener('click', function() {
        const sector = document.getElementById('onb-sector').value;
        const province = document.getElementById('onb-province').value;
        
        localStorage.setItem('user_sector', sector);
        localStorage.setItem('user_province', province);
        
        document.getElementById('onb-display-province').textContent = province;
        document.getElementById('onb-display-sector').textContent = sector;

        step1.style.display = 'none';
        step2.style.display = 'block';

        setTimeout(() => {
            loader.style.display = 'none';
            results.style.display = 'block';
            
            // Randomize a bit for realism
            const count = Math.floor(Math.random() * (25 - 12) + 12);
            const contacted = Math.floor(count * 0.7);
            const left = count - contacted;
            
            document.getElementById('onb-res-count').textContent = count;
            document.getElementById('onb-btn-res-count').textContent = count;
            document.getElementById('onb-res-contacted').textContent = contacted;
            document.getElementById('onb-res-left').textContent = left;
        }, 1500);
    });

    btnRadar.addEventListener('click', function() {
        localStorage.setItem('onboarding_completed', 'true');
        window.location.href = '<?= site_url('leads-empresas-nuevas') ?>';
    });

    btnClose.addEventListener('click', function() {
        localStorage.setItem('onboarding_completed', 'true');
        modal.style.display = 'none';
        location.reload(); // To refresh dashboard with new sector/province
    });
});
</script>
