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
            <div class="choice-icon">⚡</div>
            <p>¿Prefieres recibir empresas nuevas automáticamente cada día?</p>
            <span style="display: block; font-size: 0.9rem; color: #16a34a; margin-top: -15px; margin-bottom: 15px; font-weight: 800;">
                (antes que tu competencia)
            </span>
            <a href="<?= site_url('radar') ?>" class="btn-radar-strong">🔥 Automatizar con Radar</a>
        </div>
    </div>
</div>
