<?php
/**
 * Binary Decision Component (Competition focus)
 */
?>
<div class="binary-choice">
    <div class="choice-title">¿Cómo quieres trabajar hoy?</div>
    <div class="choice-container">
        <div class="choice-item manual">
            <div class="choice-icon">🖱️</div>
            <p>¿Quieres seguir haciendo búsquedas manuales?</p>
            <span style="display: block; font-size: 0.9rem; color: #ef4444; margin-top: -15px; margin-bottom: 15px; font-weight: 800;">
                (perdiendo tiempo y oportunidades)
            </span>
            <a href="#intro" class="btn-secondary">Seguir manual</a>
        </div>
        
        <div class="choice-divider"><span>o</span></div>
        
        <div class="choice-item auto">
            <div class="choice-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
            </div>
            <p>¿Prefieres recibir empresas nuevas automáticamente cada día?</p>
            <span style="display: block; font-size: 0.9rem; color: #16a34a; margin-top: -15px; margin-bottom: 15px; font-weight: 800;">
                (antes que tu competencia)
            </span>
            <a href="<?= site_url('radar') ?>" class="btn-radar-strong">Automatizar con Radar</a>
        </div>
    </div>
</div>
