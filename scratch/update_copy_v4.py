import os
import re

files_to_process = [
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_period.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_province.php",
    r"d:\laragon\www\apiempresas\app\Views\seo\radar_new_companies_sector.php"
]

for filepath in files_to_process:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # 2. Hero 
    content = content.replace("están buscando proveedores ahora mismo", "listas para convertirse en clientes ahora mismo")
    content = content.replace("Cada una es una oportunidad real de venta — si no actúas hoy, otro proveedor cerrará estos clientes", "Si no actúas hoy, otro proveedor cerrará estas ventas antes que tú")

    # 3. CTA Principal
    content = content.replace("Acceder antes que otros proveedores", "Conseguir estos clientes ahora")

    # 4. Bloque Negro
    content = content.replace("en ventas reales</h3>", "en ventas reales en los próximos días</h3>")

    # 5. Alerta
    content = content.replace("Algunas dejarán de estar disponibles en las próximas horas.", "Algunas ya están siendo asignadas a otros proveedores")

    # 7. CTA Final
    content = content.replace("Accede ahora o perderás estas oportunidades en las próximas horas", "Si no accedes ahora, estas oportunidades desaparecerán en horas")
    
    # 8. Micro copy
    content = content.replace("estas oportunidades", "estos clientes")
    # careful with "estas oportunidades" -> "estos clientes" above:
    content = content.replace("cerrará estos clientes", "cerrará estas ventas") # rollback hero if affected
    content = content.replace("perderás estos clientes", "perderás estos clientes") # makes sense
    content = content.replace("desaparecerán en horas", "desaparecerán en horas")

    # Convert the inline overlay into a simple gradient, and move the big card into a hidden JS modal
    inline_overlay_pattern = re.compile(r'<div class="ae-radar-page__lead-overlay-cta" style="height: 100%.*?</div>\s*</div>', re.DOTALL)
    
    simple_inline_overlay = """<div class="ae-radar-page__lead-overlay-cta" style="position: absolute; bottom: 0; left: 0; right: 0; height: 100%; background: linear-gradient(to top, #ffffff 60%, rgba(255,255,255,0.7) 80%, transparent); display: flex; align-items: flex-end; justify-content: center; padding-bottom: 2rem; pointer-events: none; z-index: 10;">
        <p style="font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 1.1rem; text-align: center; pointer-events: auto;">Estas empresas están activas ahora mismo — accede antes que otros proveedores</p>
    </div>"""

    if inline_overlay_pattern.search(content):
        content = inline_overlay_pattern.sub(simple_inline_overlay, content, count=1)

    # Append the hidden JS Modal before the closing </main> or </body> tag
    modal_html = """
    <!-- Modal Conversion -->
    <div id="radarConversionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); z-index: 99999; backdrop-filter: blur(8px); align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
        <div style="background: white; border-radius: 1.25rem; padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-width: 600px; width: 90%; position: relative; text-align: center; transform: translateY(20px); transition: transform 0.3s ease;" class="radar-modal-content">
            <button onclick="closeRadarModal()" style="position: absolute; top: 1rem; right: 1rem; background: transparent; border: none; font-size: 1.75rem; cursor: pointer; color: #94a3b8; line-height: 1;">&times;</button>
            
            <div style="width: 56px; height: 56px; background: #fef2f2; color: #dc2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="10" rx="2"></rect><path d="M7 11V8a5 5 0 0 1 10 0v3"></path></svg>
            </div>
            
            <p style="font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; font-size: 1.5rem; letter-spacing: -0.02em;">Estas empresas están activas ahora mismo</p>
            <p style="color: #475569; margin-bottom: 1rem; font-size: 1.1rem; font-weight: 500;">Otros proveedores ya están cerrando estos clientes</p>
            <p style="color: #dc2626; margin-bottom: 2rem; font-size: 1.1rem; font-weight: 700;">Si no actúas ahora, perderás estos clientes frente a tu competencia</p>
            
            <a href="<?= site_url('radar/preview') ?>" class="ae-radar-page__btn ae-radar-page__btn--primary" style="width: 100%; justify-content: center; padding: 1.15rem; font-size: 1.2rem; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4); border-radius: 0.75rem;">
                Acceder ahora y contactar primero
            </a>
            
            <p style="font-size: 0.95rem; color: #64748b; margin-top: 1.25rem; font-weight: 500;">La mayoría de usuarios consigue su primer cliente en días</p>
        </div>
    </div>

    <script>
    let modalTriggered = false;
    function showRadarModal() {
        if (modalTriggered) return;
        modalTriggered = true;
        const modal = document.getElementById('radarConversionModal');
        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.querySelector('.radar-modal-content').style.transform = 'translateY(0)';
            }, 10);
        }
    }

    function closeRadarModal() {
        const modal = document.getElementById('radarConversionModal');
        if (modal) {
            modal.style.opacity = '0';
            modal.querySelector('.radar-modal-content').style.transform = 'translateY(20px)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Intercept clicks on blurred cards
        const blurredCards = document.querySelectorAll('.ae-radar-page__lead-card.is-blurred a');
        blurredCards.forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault();
                setTimeout(showRadarModal, 1500);
            });
        });

        // Intercept clicks on primary CTAs to show modal instead of direct redirect?
        // Wait, the user said: "Mostrar modal SOLO cuando: el usuario hace clic en CTA principal".
        // If they click the primary CTA, it should show the modal instead of navigating immediately?
        // Yes, that adds friction but increases the "pressure".
        const mainCtas = document.querySelectorAll('.ae-radar-page__btn--primary:not(#radarConversionModal .ae-radar-page__btn--primary)');
        mainCtas.forEach(cta => {
            cta.addEventListener('click', (e) => {
                if(!modalTriggered) {
                    e.preventDefault();
                    setTimeout(showRadarModal, 1500);
                }
            });
        });

        // Scroll > 60%
        window.addEventListener('scroll', () => {
            const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            if (scrollPercent > 60) {
                setTimeout(showRadarModal, 1500);
            }
        });
    });
    </script>
    """
    
    # Insert modal right before </main>
    if "<!-- Modal Conversion -->" not in content:
        content = content.replace("</main>", modal_html + "\n</main>")

    with open(filepath, 'w', encoding='utf-8') as f:
        f.write(content)

    print(f"Processed {filepath}")
