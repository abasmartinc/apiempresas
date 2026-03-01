<!DOCTYPE html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title, 'excerptText' => $metaDescription]) ?>
</head>
<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header') ?>

    <main>
        <!-- HERO -->
        <section class="hero container" id="inicio">
            <div class="grid" style="grid-template-columns: 1fr 1fr; align-items: center;">
                <div>
                    <span class="pill top" style="background:#eef2ff; border:1px solid #dfe6ff; color:#1e2a55;">
                        <span class="dot" style="background:var(--primary-2)"></span> APIEmpresas Radar
                    </span>

                    <h1 class="title">
                        Empresas nuevas constituidas en España <span class="grad">cada día</span>
                    </h1>

                    <p class="subtitle">
                        Accede a las sociedades recién creadas en tu provincia y sector.
                        Filtra, analiza y exporta <strong>antes que tu competencia</strong>.
                    </p>

                    <div class="cta-row">
                        <a class="btn btn_start" href="<?= site_url() ?>register">Ver empresas nuevas ahora</a>
                        <a class="btn ghost" href="#como-funciona">Saber cómo funciona</a>
                    </div>
                </div>

                <div class="code-card" style="padding:0;">
                    <div class="code-top">
                        <span style="font-weight:700;">📡 Detección en tiempo real</span>
                        <span class="muted">Live BORME Sync</span>
                    </div>
                    <div style="padding:20px;">
                        <?php if (!empty($latestCompanies)): ?>
                            <div class="listado">
                                <?php foreach (array_slice($latestCompanies, 0, 5) as $comp): ?>
                                    <div class="item" style="grid-template-columns: 1fr auto; padding: 10px 14px;">
                                        <div>
                                            <strong style="color:var(--primary);"><?= esc($comp['name']) ?></strong>
                                            <div style="font-size:12px; color:var(--muted);"><?= esc($comp['province']) ?> • <?= esc($comp['founded']) ?></div>
                                        </div>
                                        <span class="pill estado--activa" style="font-size:10px;">NUEVA</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="muted" style="text-align:center;">Sincronizando últimas incorporaciones...</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- PERSUASIVE BLOCK -->
        <section class="container" style="padding: 40px 0;">
            <div class="band" style="background: #ffffff; border: 1px solid var(--border); border-radius: var(--radius); padding: 50px; box-shadow: var(--shadow);">
                <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                    <span class="eyebrow">Oportunidad Real</span>
                    <h2>Cada día se crean nuevas empresas en España</h2>
                    <p class="muted" style="font-size: 1.1rem; line-height: 1.6;">
                        Muchas de ellas necesitan asesoría, servicios, proveedores y soluciones desde el primer momento.
                        Con <strong>APIEmpresas Radar</strong> puedes detectar esas oportunidades en tiempo real.
                    </p>
                </div>
            </div>
        </section>

        <!-- TARGET AUDIENCE -->
        <section id="audiencia" class="audiences container">
            <div class="band">
                <span class="eyebrow">¿Para quién es?</span>
                <h2>Herramienta clave para captación B2B</h2>
                <br />

                <div class="audience-lines">
                    <div class="aud-line dev">
                        <h3>Asesorías y gestorías</h3>
                        <p>Capta nuevos clientes justo cuando más necesitan tramitar escrituras, certificados y contabilidad inicial.</p>
                    </div>

                    <div class="aud-line gest">
                        <h3>Agencias de Marketing B2B</h3>
                        <p>Identifica marcas que necesitan identidad corporativa, presencia web y campañas de lanzamiento inmediato.</p>
                    </div>

                    <div class="aud-line fin">
                        <h3>Comerciales y Proveedores</h3>
                        <p>Vende software, mobiliario o suministros industriales a negocios que están equipándose ahora mismo.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CARACTERISTICAS -->
        <section id="caracteristicas" class="container">
            <div class="band">
                <span class="eyebrow">Qué incluye</span>
                <h2>Todo lo que necesitas para tu prospección</h2>

                <div class="grid" style="grid-template-columns: repeat(2, 1fr); margin-top: 30px;">
                    <div class="card" style="padding: 30px;">
                        <ul class="list">
                            <li><span class="ok"></span> Actualización diaria desde el BORME</li>
                            <li><span class="ok"></span> Filtros por provincia y CNAE</li>
                            <li><span class="ok"></span> Fecha de constitución exacta</li>
                        </ul>
                    </div>
                    <div class="card" style="padding: 30px;">
                        <ul class="list">
                            <li><span class="ok"></span> Administradores y Capital Social</li>
                            <li><span class="ok"></span> Exportación masiva en CSV</li>
                            <li><span class="ok"></span> Acceso vía Buscador o API</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- PROVINCES -->
        <section class="container" style="padding: 60px 0;">
            <div style="text-align:center; margin-bottom: 30px;">
                <h2 class="title">Empresas recién constituidas por provincia</h2>
                <p class="muted">Explora oportunidades locales antes que nadie.</p>
            </div>
            <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;">
                <?php 
                $provinces = ['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Málaga', 'Zaragoza', 'Alicante', 'Bilbao', 'Murcia', 'Palma'];
                foreach ($provinces as $p): ?>
                    <a href="#" class="pill top" style="text-decoration:none; background:#ffffff; border-color:var(--border); color:var(--primary);"><?= $p ?></a>
                <?php endforeach; ?>
                <a href="<?= site_url('directorio') ?>" class="pill top" style="text-decoration:none; background:var(--primary); color:white; border:none;">Ver todas las provincias &rarr;</a>
            </div>
        </section>

        <!-- STEPS -->
        <section id="como-funciona" class="how container">
            <div style="text-align:center; margin-bottom: 40px;">
                <span class="eyebrow">Cómo funciona</span>
                <h2>Directo a tu flujo de ventas</h2>
            </div>

            <div class="steps">
                <div class="card">
                    <span class="pill mini-pill">1</span>
                    <h3>Detecta</h3>
                    <p>Sincronizamos diariamente los nuevos asientos del Registro Mercantil.</p>
                </div>

                <div class="card">
                    <span class="pill mini-pill">2</span>
                    <h3>Filtra</h3>
                    <p>Segmenta por actividad o zona para encontrar tu cliente ideal.</p>
                </div>

                <div class="card">
                    <span class="pill mini-pill">3</span>
                    <h3>Contacta</h3>
                    <p>Obtén el listado y ponte en marcha antes que la competencia.</p>
                </div>
            </div>
        </section>

        <!-- PRICING -->
        <section id="precios" class="pricing container">
            <div class="band" style="text-align: center;">
                <h2>Acceso completo al Radar</h2>
                <p class="muted">Sin compromiso, sin permanencia.</p>
                
                <div class="tiers" style="justify-content: center; display: flex;">
                    <div class="tier" style="max-width: 450px; width: 100%; border: 2px solid var(--primary);">
                        <span class="badge" style="background:var(--primary); color:white;">Empresas Nuevas PRO</span>
                        <h3>Plan Radar</h3>
                        <p class="muted">Acceso ilimitado a nuevas constituciones.</p>
                        <div class="price">99 €<span style="font-size:16px; color:var(--muted);">/mes</span></div>

                        <ul class="tier__list" style="text-align:left; margin-top:20px;">
                            <li style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
                                <div class="ok"></div> Acceso a todas las empresas diarias
                            </li>
                            <li style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
                                <div class="ok"></div> Filtros avanzados (Provincia, CNAE)
                            </li>
                            <li style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
                                <div class="ok"></div> Exportación CSV ilimitada
                            </li>
                            <li style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
                                <div class="ok"></div> Administradores incluidos
                            </li>
                        </ul>

                        <a class="btn secondary" style="width:100%; margin-top:30px; justify-content:center;" href="<?= site_url() ?>register?interest=radar">Activar Radar PRO</a>
                    </div>
                </div>
                
                <p class="muted" style="margin-top:20px; font-size:14px;">
                    ¿Prefieres probarlo? <a href="<?= site_url() ?>register" style="color:var(--primary); text-decoration:underline; font-weight:700;">Regístrate gratis</a> y consulta las últimas 10 empresas de hoy.
                </p>
            </div>
        </section>

        <!-- FAQS -->
        <section id="faqs" class="container">
            <div class="band">
                <span class="eyebrow">Preguntas Frecuentes</span>
                <h2>Consultas habituales</h2>
                <br>
                <div class="faq-grid" style="display: grid; gap: 14px;">
                    <details class="faq-item">
                        <summary>¿Qué incluye el acceso completo?</summary>
                        <div class="muted" style="margin-top: 10px;">
                            Incluye el listado diario de todas las nuevas SL y SA constituidas en España con NIF, Razón Social, Provincia, CNAE, Capital Social y Administradores.
                        </div>
                    </details>
                    <details class="faq-item">
                        <summary>¿Cuándo se actualizan los datos?</summary>
                        <div class="muted" style="margin-top: 10px;">
                            Nuestros sistemas se sincronizan diariamente con el BORME para que tengas la información apenas sea oficial.
                        </div>
                    </details>
                    <details class="faq-item">
                        <summary>¿Puedo exportar a CSV?</summary>
                        <div class="muted" style="margin-top: 10px;">
                            Sí, los usuarios con Plan Radar pueden exportar cualquier búsqueda de empresas nuevas a CSV para integrarlo en su CRM.
                        </div>
                    </details>
                </div>
            </div>
        </section>

        <!-- CTA FINAL -->
        <section class="cta-final container">
            <div class="cta-box">
                <div class="cta-layout" style="display: grid; grid-template-columns: 1fr auto; gap: 40px; align-items: center;">
                    <div class="cta-copy">
                        <h2>¿Listo para captar más clientes?</h2>
                        <p class="muted">Únete a cientos de empresas que ya utilizan el Radar para hacer crecer su negocio.</p>
                    </div>
                    <div class="cta-actions">
                        <a class="btn btn_start" href="<?= site_url() ?>register">Probar Radar Gratis</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>

    <!-- FAQ Schema -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "¿Qué incluye el acceso completo?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Incluye el listado diario de todas las nuevas empresas constituidas con NIF, Razón Social, Provincia, CNAE y Administradores."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cuándo se actualizan los datos?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Nuestros sistemas se sincronizan diariamente con las fuentes oficiales para ofrecer datos en tiempo real."
          }
        }
      ]
    }
    </script>
</body>
</html>
