<!-- WORDPRESS PLUGIN -->
<section class="dash-card featured-card-wp" id="plugin-wp" style="border-left: 5px solid #2271b1; background: linear-gradient(to right, #ffffff, #f0f7ff);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <div class="kicker" style="color: #2271b1;">Integración No-Code</div>
            <h2>Plugin Oficial para WordPress</h2>
            <p>Instala nuestro buscador oficial en tu sitio WordPress y muestra datos del Registro Mercantil sin escribir una sola línea de código.</p>
        </div>
        <div style="background: #2271b1; color: #fff; padding: 10px; border-radius: 12px; font-size: 24px;">🔌</div>
    </div>

    <div class="quick-grid" style="margin-top: 24px;">
        <div class="quick-item" style="border-color: #d0e4f7;">
            <strong>1. Descarga e Instala</strong>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">Bájate el archivo .zip y súbelo a tu WordPress (Plugins > Añadir nuevo).</p>
            <a href="#" class="btn-small primary js-track-wp-download" data-modal-target="modalPluginWP" style="background: #2271b1; border-color: #2271b1;">Descargar Plugin v1.0.0</a>
        </div>
        <div class="quick-item" style="border-color: #d0e4f7;">
            <strong>2. Activa con tu API Key</strong>
            <p style="font-size: 13px; color: #64748b; margin-bottom: 12px;">Copia tu clave principal y pégala en los ajustes del plugin dentro de tu WordPress.</p>
            <button type="button" class="btn-small ghost" onclick="document.getElementById('section-api-key').scrollIntoView({behavior:'smooth', block:'center'});" style="color: #2271b1; border-color: #2271b1;">Ver mi API Key</button>
        </div>
    </div>
</section>

<!-- MODAL COMING SOON -->
<div class="modal-overlay" id="modalPluginWP" aria-hidden="true">
    <div class="modal modal-wow" role="dialog" aria-modal="true" aria-labelledby="pluginTitle" tabindex="-1">
        <div class="modal-header">
            <div>
                <div class="modal-kicker">Próximamente</div>
                <h2 class="modal-title" id="pluginTitle">¡Plugin en desarrollo!</h2>
                <p class="modal-sub">Estamos ultimando los detalles para que puedas instalarlo muy pronto.</p>
            </div>
            <button class="modal-close" type="button" aria-label="Cerrar" data-close-modal>✕</button>
        </div>

        <div class="modal-body">
            <p style="margin-bottom: 12px;">Gracias por tu interés en nuestro plugin oficial de WordPress. Estamos realizando las últimas pruebas de <strong>rendimiento y seguridad</strong> para garantizar la mejor experiencia.</p>
            <p style="margin-bottom: 12px;">En cuanto el archivo esté verificado y listo para producción, habilitaremos el enlace de descarga aquí mismo y te notificaremos por email.</p>
            <div class="modal-note" style="margin-top: 20px; padding: 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px;">
                 ⚡ <strong>Integración manual:</strong> Mientras tanto, ya puedes conectar tus sistemas mediante nuestra API oficial usando la clave disponible en tu panel.
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn" type="button" data-close-modal>Cerrar</button>
            <button class="modal-btn primary" type="button" data-close-modal style="background: #2271b1; border-color: #2271b1;">Entendido, esperaré</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trackBtn = document.querySelector('.js-track-wp-download');
    if (trackBtn) {
        trackBtn.addEventListener('click', function(e) {
            // Send tracking event
            fetch('<?= site_url('tracking/radar-demo-event') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    event_type: 'click_cta',
                    source: 'dashboard_wp_plugin',
                    page: 'dashboard',
                    cta_label: 'Descargar Plugin v1.0.0',
                    url: '/descargar/plugin-wp'
                })
            })
            .then(res => res.json())
            .then(data => console.log('Interest tracked:', data))
            .catch(err => console.error('Error tracking interest:', err));
        });
    }
});
</script>
