<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/blog.css?v=' . (file_exists(FCPATH . 'public/css/blog.css') ? filemtime(FCPATH . 'public/css/blog.css') : time())) ?>" />
    <style>
        /* COLORFUL PREMIUM BLOG STYLES */
        :root {
            --blog-primary: #2563eb;
            --blog-accent: #7c3aed;
            --blog-text-main: #0f172a;
            --blog-text-muted: #64748b;
            --blog-bg-page: #f8fafc;
        }
        body { background: var(--blog-bg-page); }
        .blog-main { padding: 0 0 8rem; }
        
        /* Hero Section - Colorful & Deep */
        .blog-hero { 
            background: linear-gradient(135deg, #1e293b 0%, #111827 100%); 
            padding: 8rem 0 10rem; 
            margin-bottom: -6rem; 
            position: relative;
            overflow: hidden;
            color: #fff;
        }
        .blog-hero::before {
            content: ''; position: absolute; top: -10%; right: -5%; width: 45%; height: 70%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 70%);
            filter: blur(80px);
            z-index: 1;
        }
        .blog-hero::after {
            content: ''; position: absolute; bottom: -10%; left: -5%; width: 35%; height: 50%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
            filter: blur(60px);
            z-index: 1;
        }
        .blog-hero__grid { display: grid; grid-template-columns: 1fr 400px; gap: 6rem; align-items: start; position: relative; z-index: 2; }
        .blog-hero__title { font-size: 4rem; line-height: 1.1; font-weight: 850; letter-spacing: -0.04em; color: #fff; margin-bottom: 2rem; }
        .blog-hero__title-grad { background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .blog-hero__subtitle { font-size: 1.35rem; line-height: 1.6; color: #94a3b8; max-width: 600px; }
        
        .blog-summary { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 32px; padding: 2.5rem; color: #fff; box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.4); }
        
        .blog-grid-redesign { display: flex; flex-direction: column; gap: 4rem; position: relative; z-index: 3; }
        
        /* Featured Card */
        .blog-card--featured {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 32px;
            padding: 3.5rem;
            transition: all 0.4s ease;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05);
        }
        .blog-card--featured .blog-card__title { font-size: 2.8rem; margin-bottom: 1.5rem; color: var(--blog-text-main); }
        .blog-card--featured .blog-card__excerpt { font-size: 1.15rem; color: var(--blog-text-muted); line-height: 1.7; margin-bottom: 2.5rem; }

        /* Standard Cards Grid */
        .blog-grid__others { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); 
            gap: 2.5rem; 
        }
        
        .blog-card { 
            background: #fff; 
            border: 1px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 1rem; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            display: flex; 
            flex-direction: column;
            cursor: pointer;
        }
        .blog-card__inner { padding: 0 !important; display: flex; flex-direction: column; height: 100%; }
        .blog-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 30px 60px -12px rgba(15, 23, 42, 0.15); 
            border-color: var(--blog-primary); 
        }
        
        .blog-card__badge { 
            display: inline-block; 
            padding: 0.4rem 1rem; 
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(124, 58, 237, 0.1) 100%);
            color: var(--blog-primary); 
            border-radius: 12px; 
            font-size: 0.75rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem; 
        }
        
        .blog-card__title { font-size: 1.5rem; font-weight: 800; line-height: 1.3; color: var(--blog-text-main); margin-bottom: 1.25rem; transition: color 0.3s ease; }
        .blog-card:hover .blog-card__title { color: var(--blog-primary); }
        
        .blog-card__excerpt { font-size: 1rem; line-height: 1.6; color: var(--blog-text-muted); margin-bottom: 2rem; }
        
        .blog-card__footer { 
            margin-top: auto; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding-top: 1.5rem; 
            border-top: 1px solid #f1f5f9; 
        }
        .blog-card__meta { font-size: 0.85rem; font-weight: 600; color: #94a3b8; }
        .blog-card__cta { font-size: 0.95rem; font-weight: 700; color: var(--blog-primary); display: flex; align-items: center; gap: 4px; }
        
        /* Pagination */
        .blog-pagination { margin-top: 6rem; display: flex; justify-content: center; gap: 0.75rem; }
        .blog-pagination__btn, .blog-pagination__page { 
            background: #fff; 
            border: 1px solid #e2e8f0; 
            color: var(--blog-text-main); 
            padding: 0.75rem 1.25rem; 
            border-radius: 14px; 
            font-weight: 700; 
            transition: 0.2s; 
            cursor: pointer;
        }
        .blog-pagination__page.active { background: var(--blog-primary); color: #fff; border-color: var(--blog-primary); }
        .blog-pagination__btn:hover, .blog-pagination__page:not(.active):hover { border-color: var(--blog-primary); color: var(--blog-primary); background: #f8fafc; }

        @media (max-width: 1024px) { 
            .blog-hero__grid { grid-template-columns: 1fr; gap: 3rem; }
            .blog-hero__title { font-size: 3rem; }
            .blog-card--featured { padding: 2rem; }
            .blog-card--featured .blog-card__title { font-size: 2.2rem; }
        }
    </style>
</head>
</head>
<body style="background: var(--blog-bg-page);">
    <?= view('partials/header') ?>

<main class="blog-main">
    <!-- HERO BLOG -->
    <section class="container blog-hero" style="margin-top: 0;">
        <div class="blog-hero__grid">
            <div class="blog-hero__content">
                <span class="blog-hero__eyebrow">Blog de APIEmpresas</span>

                <h1 class="blog-hero__title">
                    Información y análisis sobre el
                    <span class="blog-hero__title-grad">tejido empresarial español</span>
                </h1>

                <p class="blog-hero__subtitle">
                    Artículos sobre actualidad empresarial, análisis de sectores, finanzas y soluciones 
                    para optimizar la gestión de datos en tu negocio.
                </p>

                <div class="blog-hero__meta-row">
                    <div class="blog-hero__meta-pill">
                        <span class="blog-hero__meta-icon">★</span>
                        <span>Contenido curado por el equipo técnico de APIEmpresas.</span>
                    </div>

                </div>
            </div>

            <aside class="blog-hero__side" aria-label="Resumen del contenido del blog">
                <div class="blog-summary">
                    <div class="blog-summary__badge">Qué encontrarás aquí</div>

                    <ul class="blog-summary__list">
                        <li>Análisis detallados de sectores y tendencias de mercado.</li>
                        <li>Guías sobre finanzas corporativas y gestión de riesgos.</li>
                        <li>Novedades sobre normativas y cumplimiento legal (KYC/KYB).</li>
                        <li>Cómo potenciar tu empresa con datos mercantiles precisos.</li>
                    </ul>

                    <div class="blog-summary__footer">
                        <p class="blog-summary__hint-title">¿Buscas algo específico?</p>
                        <p class="blog-summary__hint-text">
                            Explora nuestras categorías para encontrar análisis sobre <strong>autónomos</strong>, 
                            <strong>PYMES</strong> o grandes <strong>corporaciones</strong> españolas.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <section class="container">
        <div class="blog-list" >
            <div id="blog-list-error" class="blog-list__error" style="display:none;">
                No se han podido cargar los artículos en este momento.
            </div>

            <div id="blog-list-shell" class="blog-list__shell">
                <!-- Skeleton loading -->
                <div class="blog-skeleton"></div>
            </div>
        </div>
    </section>
</main>

<?= view('partials/footer') ?>
<?= view('scripts') ?>

<script>
    const shell    = document.getElementById('blog-list-shell');
    const errorElt = document.getElementById('blog-list-error');

    function loadPosts(page = 1) {
        if (!shell) return;
        
        // Show loading state
        shell.style.opacity = '0.5';
        shell.style.pointerEvents = 'none';

        const baseUrl = '<?= site_url('get-posts-grid') ?>';
        const fetchUrl = baseUrl + (baseUrl.includes('?') ? '&' : '?') + 'page=' + page + '&_nocache=' + Date.now();

        fetch(fetchUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            shell.style.opacity = '1';
            shell.style.pointerEvents = 'auto';
            
            if (!data || !data.ok || !data.html) {
                throw new Error(data && data.err ? data.err : 'Error al cargar posts');
            }
            shell.innerHTML = data.html;
            
            // Scroll to list top if not first page
            if (page > 1) {
                shell.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        })
        .catch(err => {
            console.error(err);
            shell.style.opacity = '1';
            shell.style.pointerEvents = 'auto';
            if (errorElt) errorElt.style.display = 'block';
        });
    }

    // Event Delegation for Pagination
    if (shell) {
        shell.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-page]');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                const page = btn.getAttribute('data-page');
                if (page) {
                    loadPosts(parseInt(page));
                }
            }
        });
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', () => {
        loadPosts(1);
    });
</script>

</body>
</html>
