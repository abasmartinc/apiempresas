<?php
/**
 * Enlace de compra de un pedido a medida (App\Controllers\PedidoMedida).
 *
 * @var array       $pedido
 * @var object|null $usuario
 * @var array|null  $pago
 * @var string|null $descarga
 * @var bool        $caducado
 * @var int         $total
 * @var array       $columnas
 * @var bool        $es_admin
 * @var array|null  $fichero   CSV ya generado (PedidoMedidaService::fichero)
 */
$eur = static fn (float $v): string => number_format($v, 2, ',', '.') . ' €';
$recienPagado = (bool) session()->getFlashdata('pagado');
$error = session()->getFlashdata('error');
$nombre = trim((string) ($usuario->name ?? ''));
$primerNombre = $nombre !== '' ? explode(' ', $nombre)[0] : '';
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Tu pedido ' . esc($pedido['referencia']) . ' | APIEmpresas',
        'excerptText' => $pedido['titulo'],
        'robots'      => 'noindex, nofollow',
    ]) ?>
    <style>
        header .nav .desktop-only,
        header .nav .mobile-nav-btn,
        header .nav .btn-enter,
        header .nav .login-btn { display: none !important; }
        footer { display: none !important; }
        body { background: #f8fafc; }

        .pm-wrap { max-width: 1080px; margin: 0 auto; padding: 28px 16px 48px; }
        .pm-grid { display: grid; grid-template-columns: 1fr 360px; gap: 24px; align-items: start; }
        @media (max-width: 900px) { .pm-grid { grid-template-columns: 1fr; } }
        .pm-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 28px 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,.05); }
        @media (max-width: 600px) { .pm-card { padding: 22px 20px; } }
        .pm-order { border: 2px solid #d1fae5; position: sticky; top: 20px; }
        .pm-badge { display: inline-flex; gap: 8px; background: #ecfdf5; color: #065f46; padding: 5px 10px; border-radius: 8px; font-size: .7rem; font-weight: 800; text-transform: uppercase; margin-bottom: 12px; }
        .pm-h1 { font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0 0 10px; letter-spacing: -.02em; line-height: 1.25; }
        .pm-lead { color: #475569; margin: 0 0 22px; line-height: 1.6; }
        .pm-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 22px; }
        .pm-stat { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; }
        .pm-stat-l { color: #94a3b8; font-size: .7rem; font-weight: 800; text-transform: uppercase; }
        .pm-stat-v { color: #0f172a; font-weight: 800; font-size: 1.05rem; }
        .pm-h2 { font-size: .8rem; font-weight: 800; text-transform: uppercase; color: #64748b; margin: 0 0 10px; }
        .pm-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 18px; margin: 0 0 18px; padding: 0; list-style: none; }
        @media (max-width: 600px) { .pm-cols { grid-template-columns: 1fr; } }
        .pm-cols li { color: #334155; font-size: .92rem; padding-left: 22px; position: relative; }
        .pm-cols li::before { content: '✓'; position: absolute; left: 0; color: #10b981; font-weight: 900; }
        .pm-note { font-size: .85rem; color: #64748b; line-height: 1.55; margin: 0; }
        .pm-row { display: flex; justify-content: space-between; color: #475569; margin: 6px 0; }
        .pm-total { display: flex; justify-content: space-between; margin-top: 14px; padding-top: 14px; border-top: 1px dashed #e2e8f0; font-size: 1.25rem; font-weight: 900; color: #0f172a; }
        .pm-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; margin-top: 20px; padding: 16px 20px; border: 0; border-radius: 14px; background: #10b981; color: #fff; font-weight: 900; font-size: 1.05rem; text-decoration: none; cursor: pointer; box-shadow: 0 10px 25px rgba(16,185,129,.3); }
        .pm-btn:hover { background: #059669; }
        .pm-btn[disabled] { background: #94a3b8; box-shadow: none; cursor: not-allowed; }
        .pm-small { font-size: .8rem; color: #94a3b8; text-align: center; margin: 12px 0 0; line-height: 1.5; }
        .pm-alert { border-radius: 12px; padding: 14px 16px; margin-bottom: 18px; font-size: .92rem; line-height: 1.5; }
        .pm-alert-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .pm-alert-ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .pm-admin { margin-top: 24px; background: #fffbeb; border: 1px dashed #f59e0b; border-radius: 14px; padding: 16px 18px; font-size: .85rem; color: #78350f; line-height: 1.6; }
        .pm-admin code { background: #fef3c7; padding: 1px 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="pm-wrap">
        <?php if ($es_admin && session()->getFlashdata('aviso')): ?>
            <div class="pm-alert pm-alert-ok"><?= esc(session()->getFlashdata('aviso')) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="pm-alert pm-alert-err"><?= esc($error) ?></div>
        <?php endif; ?>
        <?php if ($recienPagado): ?>
            <div class="pm-alert pm-alert-ok"><strong>¡Pago completado!</strong> Ya puedes descargar tu listado. También te hemos enviado el enlace por correo, y la factura llega en un correo aparte.</div>
        <?php endif; ?>

        <div class="pm-grid">
            <section class="pm-card">
                <span class="pm-badge">Pedido a medida · <?= esc($pedido['referencia']) ?></span>
                <h1 class="pm-h1"><?= esc($pedido['titulo']) ?></h1>
                <p class="pm-lead">
                    <?= $primerNombre !== '' ? 'Hola ' . esc($primerNombre) . ', este' : 'Este' ?>
                    es el listado que preparamos para ti: todas las empresas <strong>activas</strong> de la División 62,
                    tanto las registradas con CNAE-2009 (6201, 6202, 6203, 6209) como con CNAE-2025 (6210, 6220, 6290),
                    con una columna de equivalencia a CNAE-2025 para que lo tengas todo normalizado.
                </p>

                <div class="pm-stats">
                    <div class="pm-stat">
                        <div class="pm-stat-l">Empresas activas</div>
                        <div class="pm-stat-v"><?= number_format($total, 0, ',', '.') ?></div>
                    </div>
                    <div class="pm-stat">
                        <div class="pm-stat-l">Formato</div>
                        <div class="pm-stat-v">CSV · UTF-8 · Excel</div>
                    </div>
                </div>

                <h2 class="pm-h2">Columnas incluidas</h2>
                <ul class="pm-cols">
                    <li>NIF</li>
                    <li>Razón social</li>
                    <li>Estado mercantil (solo activas)</li>
                    <li>Fecha de constitución</li>
                    <li>CNAE registrado y actividad</li>
                    <li>CNAE-2025 homologado y actividad</li>
                    <li>Tramo de facturación (cuando consta)</li>
                    <li>Año de las últimas cuentas</li>
                    <li>Capital social</li>
                    <li>Teléfono (cuando consta)</li>
                    <li>Dirección, código postal y municipio</li>
                    <li>Provincia / Registro Mercantil</li>
                    <li>Objeto social</li>
                </ul>
                <p class="pm-note">
                    El tramo de facturación son rangos declarados en los depósitos de cuentas, no la cifra exacta, y
                    solo figura en las empresas que han depositado balance. Los datos son los que constan hoy en nuestra
                    base de datos (BORME y Registro Mercantil).
                </p>
            </section>

            <aside class="pm-card pm-order">
                <?php if ($pago): ?>
                    <h2 class="pm-h2">Pedido pagado</h2>
                    <p class="pm-note" style="margin-bottom:6px">
                        Pagado el <?= esc(date('d/m/Y', strtotime($pago['pagado_en']))) ?>.
                        Descárgalo las veces que necesites.
                    </p>
                    <a class="pm-btn" href="<?= esc($descarga, 'attr') ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Descargar CSV
                    </a>
                    <p class="pm-small">Si tienes cualquier problema, escríbenos a soporte@apiempresas.es.</p>
                <?php else: ?>
                    <h2 class="pm-h2">Resumen del pedido</h2>
                    <div class="pm-row"><span>Listado a medida</span><span><?= $eur((float) $pedido['importe']) ?></span></div>
                    <div class="pm-row"><span>IVA (21 %)</span><span><?= $eur((float) $pedido['iva']) ?></span></div>
                    <div class="pm-total"><span>Total</span><span><?= $eur((float) $pedido['total']) ?></span></div>

                    <?php if ($caducado): ?>
                        <button class="pm-btn" disabled>Enlace caducado</button>
                        <p class="pm-small">Escríbenos a soporte@apiempresas.es y te mandamos uno nuevo con las mismas condiciones.</p>
                    <?php else: ?>
                        <form method="post" action="<?= site_url('pedido/' . $pedido['token'] . '/pagar') ?>" onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Abriendo el pago…';">
                            <?= csrf_field() ?>
                            <button type="submit" class="pm-btn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Pagar <?= $eur((float) $pedido['total']) ?> con tarjeta
                            </button>
                        </form>
                        <p class="pm-small">
                            Pago seguro con Stripe. Al terminar descargas el archivo al instante y recibes la factura por correo
                            <?= !empty($usuario->email) ? '(' . esc($usuario->email) . ')' : '' ?>.
                            En el pago puedes indicar tu NIF si quieres que conste en la factura.
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </aside>
        </div>

        <?php if ($es_admin): ?>
            <div class="pm-admin">
                <strong>Solo lo ves tú (admin).</strong><br>
                Cliente: #<?= (int) $pedido['user_id'] ?> · <?= esc($nombre ?: '—') ?> · <?= esc($usuario->email ?? 'sin email') ?><br>
                Empresas hoy: <strong><?= number_format($total, 0, ',', '.') ?></strong>
                (acordadas en el ticket: <?= number_format((int) $pedido['empresas_acordadas'], 0, ',', '.') ?>)<br>
                Columnas: tramo de facturación → <code><?= esc($columnas['ventas'] ?: 'NO ENCONTRADA') ?></code>,
                año de cuentas → <code><?= esc($columnas['anio'] ?: 'NO ENCONTRADA') ?></code>,
                código postal → <code><?= esc($columnas['postal_code'] ?: 'NO ENCONTRADA') ?></code><br>
                Estado: <?= $pago ? 'pagado (' . esc($pago['session_id']) . ')' : ($caducado ? 'caducado' : 'pendiente de pago') ?> ·
                caduca el <?= esc(date('d/m/Y', strtotime($pedido['caduca']))) ?><br>
                Archivo:
                <?php if ($fichero): ?>
                    <strong>generado</strong> el <?= esc(date('d/m/Y H:i', strtotime($fichero['generado_en']))) ?> ·
                    <?= number_format($fichero['filas'], 0, ',', '.') ?> empresas ·
                    <?= number_format($fichero['bytes'] / 1048576, 1, ',', '.') ?> MB
                    — el cliente descarga exactamente este.
                <?php else: ?>
                    <strong>sin generar</strong> — genéralo antes de mandar el enlace.
                <?php endif; ?>
                <form method="post" action="<?= site_url('pedido/' . $pedido['token'] . '/generar') ?>" style="margin:10px 0 6px"
                      onsubmit="this.querySelector('button').disabled=true;this.querySelector('button').textContent='Generando… (puede tardar un minuto)';">
                    <?= csrf_field() ?>
                    <button type="submit" style="background:#f59e0b;color:#fff;border:0;border-radius:8px;padding:8px 14px;font-weight:800;cursor:pointer">
                        <?= $fichero ? 'Regenerar archivo' : 'Generar archivo ahora' ?>
                    </button>
                </form>
                <a href="<?= site_url('pedido/' . $pedido['token'] . '/descargar') ?>">Descargar el CSV para revisarlo</a>
                · Enlace para el cliente: <code><?= esc(site_url('pedido/' . $pedido['token'])) ?></code>
            </div>
        <?php endif; ?>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
