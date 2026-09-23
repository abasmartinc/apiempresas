<?= $this->extend(($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app') ?>

<?= $this->section('content') ?>
<?php helper('company'); ?>
<div class="container" style="padding: 32px 0 64px 0; max-width: 780px;">

    <a href="<?= site_url('dashboard?view=risk') ?>" style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-size: 0.85rem; font-weight: 700; text-decoration: none; margin-bottom: 18px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Volver al panel
    </a>

    <h1 style="font-size: 1.9rem; font-weight: 900; color: #0f172a; margin: 0 0 8px 0; letter-spacing: -0.6px;">
        Sube tu cartera de clientes
    </h1>
    <p style="color: #64748b; font-size: 1rem; line-height: 1.55; margin: 0 0 26px 0; max-width: 60ch;">
        Súbenos un CSV con los CIF de tus clientes y te decimos, de una vez, cuáles tienen una
        incidencia en el Registro Mercantil. Los que pongas en vigilancia te avisan por correo
        cuando algo cambie.
    </p>

    <?php if (session('error')): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 13px 16px; font-size: 0.9rem; line-height: 1.5; margin-bottom: 20px;">
            <?= esc(session('error')) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('cartera/analizar') ?>" enctype="multipart/form-data"
          style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 26px; box-shadow: 0 4px 16px -6px rgba(15, 23, 42, 0.08);">
        <?= csrf_field() ?>

        <label for="cartera-file" style="display: block; border: 2px dashed #cbd5e1; border-radius: 14px; padding: 30px 20px; text-align: center; cursor: pointer; background: #f8fafc; transition: all 0.15s;"
               onmouseover="this.style.borderColor='#94a3b8'; this.style.background='#f1f5f9';"
               onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#f8fafc';">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8" style="margin-bottom: 10px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            <div style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">Elegir fichero CSV</div>
            <div id="cartera-nombre" style="font-size: 0.82rem; color: #64748b;">
                Hasta <?= (int) $maxFilas ?> empresas &bull; máximo 1 MB
            </div>
            <input type="file" name="cartera" id="cartera-file" accept=".csv,.txt,text/csv" required
                   style="position: absolute; width: 1px; height: 1px; opacity: 0;"
                   onchange="var n=this.files&&this.files[0]?this.files[0].name:''; if(n){document.getElementById('cartera-nombre').textContent=n;}">
        </label>

        <div style="margin-top: 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 13px 16px; font-size: 0.85rem; color: #1e3a8a; line-height: 1.5;">
            <strong>Subir la cartera no gasta consultas.</strong>
            <?php if (!empty($cupo['es_pro']) || session('is_admin')): ?>
                Verás la puntuación de cada empresa ordenada por riesgo y podrás descargarla en CSV; el
                dictamen completo de una empresa concreta sigue siendo una consulta aparte.
            <?php else: ?>
                Te decimos el nivel de riesgo de las <?= (int) solvencia('carteraNivelesGratis', 25) ?> empresas
                con más riesgo de tu lista, sin consumir nada. Con Solvencia Pro ves la puntuación de todas y
                la descargas en CSV.
            <?php endif; ?>
        </div>

        <div style="margin-top: 14px; font-size: 0.83rem; color: #64748b; line-height: 1.55;">
            Vale cualquier CSV que salga de tu Excel o tu programa de facturación: buscamos la columna
            del CIF sola, y nos da igual si el separador es punto y coma o coma. Si solo tienes los CIF,
            un fichero con una columna también sirve.
        </div>

        <button type="submit" style="width: 100%; margin-top: 20px; background: #2563eb; color: #fff; border: none; border-radius: 12px; padding: 14px; font-size: 1rem; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3); transition: all 0.2s;"
                onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
            Analizar mi cartera
        </button>

        <div style="margin-top: 12px; text-align: center; font-size: 0.8rem; color: #94a3b8;">
            No guardamos el fichero: lo leemos, te enseñamos el resultado y lo descartamos.
        </div>
    </form>

    <?php
    $usadas = (int) ($cupo['usadas'] ?? 0);
    $tope   = (int) ($cupo['tope'] ?? 0);
    $quedan = (int) ($cupo['quedan'] ?? 0);
    $esPro  = !empty($cupo['es_pro']);
    ?>
    <div style="margin-top: 22px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 15px 18px;">
        <div style="font-size: 0.88rem; color: #475569; line-height: 1.5;">
            Ahora mismo vigilas <strong style="color: #0f172a;"><?= $usadas ?> de <?= $tope ?></strong> empresas.
            <?php if ($quedan > 0): ?>
                Te caben <strong style="color: #0f172a;"><?= $quedan ?></strong> más.
            <?php else: ?>
                No te cabe ninguna más ahora mismo.
            <?php endif; ?>
        </div>
        <?php if (!$esPro): ?>
            <a href="<?= site_url('billing?view=risk&plan=risk_pro') ?>" style="white-space: nowrap; font-size: 0.85rem; font-weight: 800; color: #2563eb; text-decoration: none;">
                Con Pro son <?= (int) solvencia('vigilanciasPro', 25) ?> &rarr;
            </a>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
