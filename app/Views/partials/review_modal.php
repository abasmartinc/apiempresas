<?php
/**
 * app/Views/partials/review_modal.php
 * Versión Ultra-Premium Light (Corregida: Interacción y clics)
 */
?>
<div class="modal-overlay" id="reviewModal" aria-hidden="true" style="display: none; opacity: 0; pointer-events: none; backdrop-filter: blur(16px) saturate(1.8); transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); z-index: 99999;">
    <div class="modal review-modal-ultra-light" style="max-width: 650px; padding: 0; border: 1px solid rgba(255,255,255,0.7); background: rgba(255, 255, 255, 0.85); position: relative; pointer-events: auto;">
        
        <!-- Efectos de Luces Suaves -->
        <div class="modal-light-glows">
            <div class="light-blob blob-1"></div>
            <div class="light-blob blob-2"></div>
        </div>

        <button class="review-modal-close-light" id="closeReviewModal" data-close-modal aria-label="Cerrar">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>

        <div class="modal-content-pro" style="position: relative; z-index: 2; padding: 70px 50px 40px; text-align: center;">
            
            <div class="kicker-light">FEEDBACK PRIORITARIO</div>
            <h2 class="title-light-gradient">¿Cómo puntuarías tu experiencia?</h2>
            <p class="subtitle-light">Tu opinión nos ayuda a construir la herramienta de inteligencia comercial más potente del mercado.</p>

            <!-- Sistema de Estrellas Gigantes (72px) -->
            <div class="stars-light-wrapper">
                <div class="star-rating-light">
                    <?php for($i=1; $i<=5; $i++): ?>
                    <button type="button" class="star-btn-light" data-value="<?= $i ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                    </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" id="review_rating" value="0">
                <div id="rating_label_light" class="rating-label-light">Toca una estrella para valorar</div>
            </div>

            <!-- Área de Comentario y Acción -->
            <div id="action_area_light" class="action-area-light-hidden">
                <div class="textarea-container-light">
                    <textarea id="review_comment" placeholder="¿Qué podemos hacer para que tu experiencia sea de 10?" class="textarea-light"></textarea>
                </div>
                
                <div class="footer-actions-light">
                    <button type="button" class="btn-skip-light" data-close-modal id="dismissReviewModal">Ahora no, gracias</button>
                    <button type="button" class="btn-send-light" id="submitReviewBtn" disabled>
                        <span>Enviar mi valoración</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* --- Ultra Premium Light CSS (Fix Interaction) --- */
    .modal-overlay.active {
        pointer-events: auto !important; 
        display: flex !important;
        align-items: center;
        justify-content: center;
    }

    .review-modal-ultra-light {
        border-radius: 48px !important;
        box-shadow: 0 40px 100px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(255,255,255,0.8) inset !important;
        transform: scale(0.9) translateY(40px);
        transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        max-height: 92vh; 
        overflow-y: auto; 
        overflow-x: hidden;
        pointer-events: auto !important; 
    }

    .review-modal-ultra-light::-webkit-scrollbar {
        width: 0;
        background: transparent;
    }

    .modal-overlay.active .review-modal-ultra-light {
        transform: scale(1) translateY(0);
    }

    .modal-light-glows {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
        overflow: hidden; 
        border-radius: 48px;
    }
    .light-blob {
        position: absolute;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        filter: blur(80px);
        opacity: 0.12;
    }
    .blob-1 { background: #2152ff; top: -150px; left: -150px; }
    .blob-2 { background: #12b48a; bottom: -150px; right: -150px; }

    .review-modal-close-light {
        position: absolute;
        top: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        z-index: 10;
    }
    .review-modal-close-light:hover {
        background: #f1f5f9;
        color: #1e293b;
        transform: rotate(90deg) scale(1.1);
    }

    .kicker-light {
        font-size: 12px;
        font-weight: 950;
        color: #2563eb;
        letter-spacing: 0.3em;
        margin-bottom: 24px;
    }

    .title-light-gradient {
        font-size: 42px;
        font-weight: 950;
        margin-bottom: 18px;
        line-height: 1.05;
        letter-spacing: -0.05em;
        background: linear-gradient(90deg, #2152ff, #12b48a);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        color: transparent;
        display: inline-block;
    }

    .subtitle-light {
        font-size: 18px;
        color: #64748b;
        max-width: 480px;
        margin: 0 auto 40px; 
        line-height: 1.6;
    }

    .star-rating-light {
        display: flex;
        justify-content: center;
        gap: 16px;
    }
    .star-btn-light {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #94a3b8;
        width: 72px;
        height: 72px;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .star-btn-light:hover { transform: scale(1.3); color: #fbbf24; }
    .star-btn-light.active { color: #fbbf24; filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.4)); }
    .star-btn-light.active svg { fill: #fbbf24; }

    .rating-label-light {
        margin-top: 24px; 
        font-size: 18px;
        font-weight: 800;
        color: #2563eb;
        min-height: 28px;
        letter-spacing: -0.01em;
    }

    .action-area-light-hidden {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .action-area-light-visible {
        max-height: 500px;
        opacity: 1;
        margin-top: 30px; 
    }

    .textarea-container-light {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 6px;
        margin-bottom: 24px; 
        transition: all 0.3s;
    }
    .textarea-container-light:focus-within {
        background: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .textarea-light {
        width: 100%;
        min-height: 120px; 
        background: transparent;
        border: none;
        padding: 20px;
        color: #0f172a;
        font-size: 16px;
        outline: none;
        resize: none;
        line-height: 1.5;
    }

    .footer-actions-light {
        display: flex;
        align-items: center;
        gap: 24px;
        padding-bottom: 10px; 
    }

    .btn-skip-light {
        background: none;
        border: none;
        color: #94a3b8;
        font-weight: 700;
        font-size: 15px;
        cursor: pointer;
        padding: 15px;
        transition: all 0.3s;
    }
    .btn-skip-light:hover { color: #64748b; transform: translateX(-5px); }

    .btn-send-light {
        flex: 1;
        height: 64px; 
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        border: none;
        border-radius: 22px;
        color: white;
        font-weight: 800;
        font-size: 17px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        cursor: pointer;
        transition: all 0.4s;
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
    }
    .btn-send-light:disabled {
        opacity: 0.3;
        cursor: not-allowed;
        box-shadow: none;
    }
    .btn-send-light:not(:disabled):hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(37, 99, 235, 0.4);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const showByController = <?= (isset($showReviewModal) && $showReviewModal) ? 'true' : 'false' ?>;
    const hasBeenDismissed = localStorage.getItem('ae_review_dismissed');
    const hasBeenCompleted = localStorage.getItem('ae_review_completed');

    if (showByController && !hasBeenDismissed && !hasBeenCompleted) {
        setTimeout(() => {
            const modal = document.getElementById('reviewModal');
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modal.classList.add('active');
            }, 100);
        }, 5000);
    }

    const starBtns = document.querySelectorAll('.star-btn-light');
    const ratingInput = document.getElementById('review_rating');
    const ratingLabel = document.getElementById('rating_label_light');
    const actionArea = document.getElementById('action_area_light');
    const submitBtn = document.getElementById('submitReviewBtn');

    const labels = {
        1: 'Mejorable 😕',
        2: 'Aceptable 🙂',
        3: '¡Muy buena! 😊',
        4: '¡Excelente herramienta! 🚀',
        5: '¡Increíble, me encanta! 😍'
    };

    starBtns.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            highlightStars(this.dataset.value);
            ratingLabel.innerText = labels[this.dataset.value];
        });

        btn.addEventListener('mouseleave', function() {
            if (ratingInput.value == 0) {
                resetStars();
                ratingLabel.innerText = 'Toca una estrella para valorar';
            } else {
                highlightStars(ratingInput.value);
                ratingLabel.innerText = labels[ratingInput.value];
            }
        });

        btn.addEventListener('click', function() {
            const val = this.dataset.value;
            ratingInput.value = val;
            highlightStars(val);
            actionArea.classList.add('action-area-light-visible');
            submitBtn.disabled = false;
            
            setTimeout(() => {
                const modal = document.querySelector('.review-modal-ultra-light');
                modal.scrollTo({ top: modal.scrollHeight, behavior: 'smooth' });
                document.getElementById('review_comment').focus();
            }, 800);
        });
    });

    function highlightStars(val) {
        starBtns.forEach(btn => {
            if (btn.dataset.value <= val) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    function resetStars() {
        starBtns.forEach(btn => btn.classList.remove('active'));
    }

    function closeModal() {
        const modal = document.getElementById('reviewModal');
        modal.style.opacity = '0';
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 700);
    }

    document.querySelectorAll('[data-close-modal]').forEach(el => {
        el.addEventListener('click', () => {
            if(el.id === 'dismissReviewModal') localStorage.setItem('ae_review_dismissed', 'true');
            closeModal();
        });
    });

    document.getElementById('submitReviewBtn').addEventListener('click', async function() {
        const rating = ratingInput.value;
        const comment = document.getElementById('review_comment').value;
        
        this.disabled = true;
        this.innerHTML = '<span>Enviando...</span>';

        try {
            const formData = new FormData();
            formData.append('rating', rating);
            formData.append('comment', comment);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            const response = await fetch('<?= site_url("submit-review") ?>', {
                method: 'POST',
                body: formData,
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });

            if (response.ok) {
                localStorage.setItem('ae_review_completed', 'true');
                closeModal();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Muchas gracias!',
                        text: 'Gracias por ayudarnos a mejorar.',
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false,
                        background: 'rgba(255, 255, 255, 0.95)',
                        backdrop: `rgba(15, 23, 42, 0.2)`
                    });
                }
            } else { throw new Error(); }
        } catch (err) {
            alert('Error al enviar. Inténtalo de nuevo.');
            this.disabled = false;
            this.innerHTML = '<span>Enviar mi valoración</span>';
        }
    });
});
</script>
