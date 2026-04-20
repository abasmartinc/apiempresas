<?php
/**
 * Radar Strong CTA Component (Dynamic Preferences Support)
 * @var object|array|null $user
 */
$get_val = function($src, $key, $default) {
    if (is_array($src)) return $src[$key] ?? $default;
    if (is_object($src)) return $src->$key ?? $default;
    return $default;
};

$user_province = $get_val($user, 'province', 'tu provincia');
$user_sector = $get_val($user, 'sector', 'tu sector');

// Mock data
$num_provincia = 14; 
$num_sector = 8;
$contactadas = 12;
$disponibles = 2;
$usuarios_activos = 127;
$horas = 2;
?>
<div class="radar-cta-refined" id="radar-cta-main" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 32px; margin: 24px 0; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); border-top: 4px solid #2152ff;">
    <div style="display: flex; flex-direction: column; align-items: center; text-align: center;">
        <div style="background: #eff6ff; color: #2152ff; padding: 6px 16px; border-radius: 999px; font-size: 0.85rem; font-weight: 800; text-transform: uppercase; margin-bottom: 16px;">
            🔥 Oportunidades exclusivas
        </div>
        
        <h2 style="font-size: 1.6rem; font-weight: 950; color: #0f172a; margin-bottom: 8px; letter-spacing: -0.02em;">
            <span id="dyn-num-prov"><?= $num_provincia ?></span> empresas nuevas detectadas en <span id="dyn-province"><?= htmlspecialchars($user_province) ?></span>
        </h2>
        
        <div style="background: #fff7ed; color: #c2410c; padding: 4px 12px; border-radius: 6px; font-size: 0.95rem; font-weight: 900; margin-bottom: 20px; border: 1px solid #ffedd5;">
            ⚠️ Solo quedan <span style="font-size: 1.1rem;" id="dyn-disponibles"><?= $disponibles ?></span> disponibles en <span id="dyn-sector"><?= htmlspecialchars($user_sector) ?></span>
        </div>

        <div style="display: flex; gap: 24px; margin-bottom: 24px; align-items: center;">
            <div style="display: flex; align-items: center; gap: 8px; color: #ef4444; font-weight: 700; font-size: 0.95rem;">
                <span style="display: inline-block; width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                <span id="dyn-contactadas"><?= $contactadas ?></span> ya contactadas
            </div>
            <div style="color: #94a3b8; font-weight: 600; font-size: 0.9rem;">
                ⏱️ Actualizado hace <?= $horas ?>h
            </div>
        </div>

        <a href="<?= site_url('empresas-nuevas-hoy') ?>" class="btn primary" style="background: #2152ff; color: white !important; font-weight: 900; padding: 18px 40px; border-radius: 14px; text-decoration: none; font-size: 1.15rem; transition: transform 0.2s; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3); width: 100%; max-width: 500px;">
            🔥 Ver las <span id="dyn-btn-count"><?= $num_provincia ?></span> empresas disponibles ahora
        </a>
        
        <div style="margin-top: 16px; font-size: 0.85rem; color: #64748b; font-weight: 700;">
            👥 +<?= $usuarios_activos ?> usuarios activos esta semana
        </div>

        <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #f1f5f9; font-size: 1rem; color: #0f172a; font-weight: 800;">
            ¿Quieres esperar… o llegar antes que tu competencia?
        </div>
    </div>
</div>

<script>
(function(){
    const sector = localStorage.getItem('user_sector');
    const province = localStorage.getItem('user_province');
    if(sector) document.getElementById('dyn-sector').textContent = sector;
    if(province) {
        document.getElementById('dyn-province').textContent = province;
        // Adjust numbers slightly based on province for variety
        const hash = province.length;
        const base = 10 + (hash % 20);
        document.getElementById('dyn-num-prov').textContent = base;
        document.getElementById('dyn-btn-count').textContent = base;
        document.getElementById('dyn-contactadas').textContent = Math.floor(base * 0.8);
        document.getElementById('dyn-disponibles').textContent = Math.max(1, base - Math.floor(base * 0.8));
    }
})();
</script>
