<?php
/**
 * Professional Help & Support Section
 */
?>
<section class="help-center-section" style="margin-top: 80px; border-top: 1px solid #e2e8f0; padding: 80px 40px; margin-bottom: 80px; background: #ffffff; border-radius: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 64px;">
            <h2 style="font-size: 2.5rem; font-weight: 950; margin-bottom: 16px; letter-spacing: -0.04em; background: linear-gradient(135deg, #2152ff 0%, #12b48a 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: inline-block;"><?= lang('Dashboard.help_center') ?></h2>
            <p style="font-size: 1.15rem; color: #64748b; font-weight: 600; max-width: 700px; margin: 0 auto; line-height: 1.6;"><?= lang('Dashboard.help_subtitle') ?></p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: start;">
        <!-- LEFT: FAQS -->
        <div class="faq-container">
            <h3 style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <div style="background: #eff6ff; color: #2152ff; width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <?= lang('Dashboard.faqs') ?>
            </h3>
            
            <div class="help-accordion">
                <div class="help-faq-item active">
                    <div class="help-faq-header" style="padding: 16px; background: #f8fafc; border-radius: 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; transition: all 0.2s;">
                        <span style="font-weight: 800; color: #0f172a; font-size: 0.95rem;"><?= lang('Dashboard.faq_1_q') ?></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="acc-icon" style="transition: transform 0.3s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="help-faq-content" style="padding: 16px; font-size: 0.9rem; color: #64748b; font-weight: 600; line-height: 1.5; display: block;">
                        <?= lang('Dashboard.faq_1_a', [site_url('documentation')]) ?>
                    </div>
                </div>

                <div class="help-faq-item" style="margin-top: 12px;">
                    <div class="help-faq-header" style="padding: 16px; background: #ffffff; border-radius: 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; transition: all 0.2s;">
                        <span style="font-weight: 800; color: #0f172a; font-size: 0.95rem;"><?= lang('Dashboard.faq_2_q') ?></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="acc-icon" style="transition: transform 0.3s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="help-faq-content" style="padding: 16px; font-size: 0.9rem; color: #64748b; font-weight: 600; line-height: 1.5; display: none;">
                        <?= lang('Dashboard.faq_2_a', [site_url('consumption')]) ?>
                    </div>
                </div>

                <div class="help-faq-item" style="margin-top: 12px;">
                    <div class="help-faq-header" style="padding: 16px; background: #ffffff; border-radius: 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; transition: all 0.2s;">
                        <span style="font-weight: 800; color: #0f172a; font-size: 0.95rem;"><?= lang('Dashboard.faq_3_q') ?></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="acc-icon" style="transition: transform 0.3s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="help-faq-content" style="padding: 16px; font-size: 0.9rem; color: #64748b; font-weight: 600; line-height: 1.5; display: none;">
                        <?= lang('Dashboard.faq_3_a') ?>
                    </div>
                </div>

                <div class="help-faq-item" style="margin-top: 12px;">
                    <div class="help-faq-header" style="padding: 16px; background: #ffffff; border-radius: 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e2e8f0; transition: all 0.2s;">
                        <span style="font-weight: 800; color: #0f172a; font-size: 0.95rem;"><?= lang('Dashboard.faq_4_q') ?></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="acc-icon" style="transition: transform 0.3s;"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="help-faq-content" style="padding: 16px; font-size: 0.9rem; color: #64748b; font-weight: 600; line-height: 1.5; display: none;">
                        <?= lang('Dashboard.faq_4_a') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: CONTACT FORM -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 32px; padding: 56px; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05);">
            
            <?php if (session('contact_success')): ?>
                <div style="background: #dcfce7; color: #166534; padding: 20px; border-radius: 14px; margin-bottom: 32px; font-weight: 800; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?= session('contact_success') ?>
                </div>
            <?php endif; ?>

            <?php if (session('contact_error')): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 20px; border-radius: 14px; margin-bottom: 32px; font-weight: 800; border: 1px solid #fecaca; display: flex; align-items: center; gap: 12px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <?= session('contact_error') ?>
                </div>
            <?php endif; ?>

            <h3 style="font-size: 1.4rem; font-weight: 950; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.02em;"><?= lang('Dashboard.direct_contact') ?></h3>
            <p style="font-size: 0.95rem; color: #64748b; font-weight: 600; margin-bottom: 32px; line-height: 1.5;"><?= lang('Dashboard.direct_contact_desc') ?></p>

            <form action="<?= site_url('contact/send') ?>" method="POST" id="helpContactForm">
                <div style="display: grid; gap: 24px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 900; color: #0f172a; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.05em;"><?= lang('Dashboard.name_label') ?></label>
                            <input type="text" name="name" placeholder="<?= lang('Dashboard.name_placeholder') ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 0.95rem; font-weight: 600; background: #f8fafc; transition: all 0.2s;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.75rem; font-weight: 900; color: #0f172a; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.05em;"><?= lang('Dashboard.phone_label') ?> <span style="color: #64748b; font-weight: 600; font-size: 0.65rem;"><?= lang('Dashboard.optional') ?></span></label>
                            <input type="tel" name="phone" placeholder="+34 ..." style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 0.95rem; font-weight: 600; background: #f8fafc; transition: all 0.2s;">
                        </div>
                    </div>
                    
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 900; color: #0f172a; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.05em;"><?= lang('Dashboard.email_contact_label') ?></label>
                        <input type="email" name="email" placeholder="<?= lang('Dashboard.email_placeholder') ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 0.95rem; font-weight: 600; background: #f8fafc; transition: all 0.2s;">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 900; color: #0f172a; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.05em;"><?= lang('Dashboard.how_can_we_help') ?></label>
                        <textarea name="message" rows="4" placeholder="<?= lang('Dashboard.message_placeholder') ?>" required style="width: 100%; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-size: 0.95rem; font-weight: 600; background: #f8fafc; resize: none; transition: all 0.2s;"></textarea>
                    </div>

                    <button type="submit" style="width: 100%; background: #2152ff; color: white; border: none; padding: 20px; border-radius: 14px; font-weight: 900; font-size: 1.1rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 10px 20px -5px rgba(33, 82, 255, 0.3);">
                        <span><?= lang('Dashboard.send_message') ?></span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </div>
                
                <div style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 700;"><?= lang('Dashboard.or_write_directly') ?></span>
                    <a href="mailto:soporte@apiempresas.es" style="font-size: 0.85rem; color: #2152ff; font-weight: 900; text-decoration: none;">soporte@apiempresas.es</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Accordion Logic
    const faqHeaders = document.querySelectorAll('.help-faq-header');
    faqHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const item = this.parentElement;
            const content = item.querySelector('.help-faq-content');
            const icon = this.querySelector('.acc-icon');
            const isActive = item.classList.contains('active');
            
            document.querySelectorAll('.help-faq-item').forEach(otherItem => {
                otherItem.classList.remove('active');
                otherItem.querySelector('.help-faq-content').style.display = 'none';
                otherItem.querySelector('.help-faq-header').style.background = '#ffffff';
                otherItem.querySelector('.acc-icon').style.transform = 'rotate(0deg)';
            });
            
            if (!isActive) {
                item.classList.add('active');
                content.style.display = 'block';
                this.style.background = '#f8fafc';
                icon.style.transform = 'rotate(180deg)';
            }
        });
    });
    
    const firstIcon = document.querySelector('.help-faq-item.active .acc-icon');
    if(firstIcon) firstIcon.style.transform = 'rotate(180deg)';

    // AJAX Form Logic
    const contactForm = document.getElementById('helpContactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalContent = btn.innerHTML;
            
            // UI Feedback
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.innerHTML = '<span>Enviando mensaje...</span>';
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: data.message,
                        confirmButtonColor: '#2152ff',
                        customClass: {
                            popup: 'premium-swal-popup'
                        }
                    });
                    contactForm.reset();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#2152ff'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Hubo un problema al conectar con el servidor. Por favor, inténtalo de nuevo.',
                    confirmButtonColor: '#2152ff'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.innerHTML = originalContent;
            });
        });
    }
});
</script>

<style>
    .premium-swal-popup {
        border-radius: 24px !important;
        padding: 40px !important;
    }
    .help-faq-header:hover {
        border-color: #cbd5e1 !important;
        background: #f8fafc !important;
    }
</style>
