<!-- Usage Trigger Banner -->
<div id="usage-trigger-container" style="display: none; margin-bottom: 32px; margin-top: 24px;">
    <div id="usage-trigger-banner" style="position: relative; padding: 16px 24px; border-radius: 20px; border: 1.5px solid; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div id="usage-trigger-icon" style="flex-shrink: 0; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; background: rgba(255,255,255,0.5); border: 1px solid rgba(255,255,255,0.8);">
            </div>
            <div>
                <h4 id="usage-trigger-title" style="margin: 0; font-size: 1rem; font-weight: 800; letter-spacing: -0.01em;"></h4>
                <p id="usage-trigger-desc" style="margin: 2px 0 0; font-size: 0.85rem; opacity: 0.85; font-weight: 600;"></p>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 16px;">
            <a id="usage-trigger-cta" href="<?= site_url('billing') ?>" class="btn" style="white-space: nowrap; font-weight: 800; padding: 10px 24px; border-radius: 12px; font-size: 0.9rem; transition: all 0.2s ease; border: none;">
                👉 Activar Pro para producción
            </a>
            <button id="usage-trigger-close" style="background: transparent; border: none; cursor: pointer; padding: 4px; opacity: 0.6; transition: opacity 0.2s ease;" title="Cerrar">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('usage-trigger-container');
    const banner = document.getElementById('usage-trigger-banner');
    const icon = document.getElementById('usage-trigger-icon');
    const title = document.getElementById('usage-trigger-title');
    const desc = document.getElementById('usage-trigger-desc');
    const cta = document.getElementById('usage-trigger-cta');
    const closeBtn = document.getElementById('usage-trigger-close');

    // Mapeo de triggers
    const triggers = {
        'first_use': {
            icon: '⚡',
            title: 'Ya estás usando la API',
            desc: 'Activa Pro antes de pasar a producción',
            bg: '#eff6ff',
            border: '#bfdbfe',
            text: '#1e40af',
            ctaBg: '#2563eb',
            ctaText: '#ffffff'
        },
        '20_percent': {
            icon: '📊',
            title: 'Has usado el 20% del plan Free',
            desc: 'Evita quedarte sin servicio en producción',
            bg: '#f0f9ff',
            border: '#bae6fd',
            text: '#0369a1',
            ctaBg: '#0284c7',
            ctaText: '#ffffff'
        },
        '50_percent': {
            icon: '⚠️',
            title: 'Estás cerca del límite',
            desc: 'Activa Pro para evitar bloqueos',
            bg: '#fff7ed',
            border: '#fed7aa',
            text: '#9a3412',
            ctaBg: '#ea580c',
            ctaText: '#ffffff'
        },
        '80_percent': {
            icon: '🚨',
            title: 'Estás a punto de quedarte sin servicio',
            desc: 'Activa Pro ahora',
            bg: '#fef2f2',
            border: '#fecaca',
            text: '#991b1b',
            ctaBg: '#dc2626',
            ctaText: '#ffffff'
        }
    };

    // 1. Consultar estado de uso
    fetch('<?= site_url('api/user/usage-status') ?>')
        .then(response => response.json())
        .then(data => {
            if (data.trigger && triggers[data.trigger]) {
                const config = triggers[data.trigger];
                
                // Verificar si el usuario lo cerró en esta sesión
                if (localStorage.getItem('hide_trigger_' + data.trigger) === 'true') {
                    return;
                }

                // Aplicar estilos y textos
                icon.textContent = config.icon;
                title.textContent = config.title;
                desc.textContent = config.desc;
                banner.style.backgroundColor = config.bg;
                banner.style.borderColor = config.border;
                banner.style.color = config.text;
                cta.style.backgroundColor = config.ctaBg;
                cta.style.color = config.ctaText;
                
                // Mostrar banner
                container.style.display = 'block';

                // Registrar evento 'mostrar'
                logTriggerEvent('trigger_shown', data.trigger);
                
                // Eventos de interacción
                cta.addEventListener('click', () => {
                    logTriggerEvent('trigger_clicked', data.trigger);
                    logTriggerEvent('upgrade_clicked', data.trigger);
                });

                closeBtn.addEventListener('click', () => {
                    container.style.display = 'none';
                    // Guardar en localStorage para no molestar en la sesión actual
                    localStorage.setItem('hide_trigger_' + data.trigger, 'true');
                });
            }
        })
        .catch(err => console.error('Error fetching usage status:', err));

    function logTriggerEvent(eventType, triggerType) {
        const formData = new FormData();
        formData.append('event_type', eventType);
        formData.append('trigger_type', triggerType);

        fetch('<?= site_url('api/user/log-event') ?>', {
            method: 'POST',
            body: formData
        }).catch(err => console.error('Error logging event:', err));
    }
});
</script>

<style>
#usage-trigger-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
#usage-trigger-close:hover {
    opacity: 1;
    color: inherit;
}
</style>
