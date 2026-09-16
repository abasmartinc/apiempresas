<?= $this->extend(($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/public') ?>

<?= $this->section('content') ?>
<div class="container" style="max-width: 760px; padding-top: 40px; padding-bottom: 70px;">

    <?php if (session()->getFlashdata('success')): ?>
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 12px; margin-bottom: 22px; font-weight: 600;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 12px; margin-bottom: 22px; font-weight: 600;">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 34px 32px; box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.12);">

        <div style="display: inline-flex; align-items: center; gap: 7px; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 5px 13px; border-radius: 999px; font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 16px;">
            🛡️ Garantía de <?= (int) $dias ?> días
        </div>

        <h1 style="font-size: 1.85rem; font-weight: 900; color: #0f172a; margin: 0 0 12px 0; letter-spacing: -0.7px; line-height: 1.2;">
            <?php /* "el mes entero" dejaba fuera al plan anual, que también se vende. */ ?>
            Si Solvencia Pro no te sirve, te devolvemos el periodo entero
        </h1>

        <p style="color: #475569; font-size: 1rem; line-height: 1.6; margin: 0 0 22px 0;">
            Solvencia no se demuestra el primer día: se demuestra el día que aparece un movimiento
            en el BORME de una empresa que te importa y te enteras por un correo nuestro. Por eso no
            te pedimos que nos creas por adelantado. Pruébalo <?= (int) $dias ?> días con todo incluido
            y, si no te ha servido, escríbenos y te devolvemos íntegro lo que hayas pagado:
            <?= esc($precioPro) ?> si vas mes a mes, <?= esc($precioAnual ?? '290 €') ?> si elegiste el plan anual.
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 22px; margin-bottom: 26px;">
            <div style="font-weight: 900; color: #0f172a; font-size: 0.95rem; margin-bottom: 12px;">Cómo funciona</div>
            <ol style="margin: 0; padding-left: 20px; color: #334155; font-size: 0.93rem; line-height: 1.75;">
                <li>Contratas Solvencia Pro y lo usas con normalidad durante <?= (int) $dias ?> días.</li>
                <li>Si no te ha resultado útil, lo pides desde esta misma página (o respondiendo a cualquiera de nuestros correos).</li>
                <li>Te devolvemos el importe del periodo facturado a la misma tarjeta. Sin preguntas y sin condiciones.</li>
            </ol>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 28px;">
            <div style="flex: 1; min-width: 210px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px 17px;">
                <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem; margin-bottom: 3px;">Sin permanencia</div>
                <div style="color: #64748b; font-size: 0.83rem; line-height: 1.45;">Puedes cancelar cuando quieras desde tu panel, en un clic.</div>
            </div>
            <div style="flex: 1; min-width: 210px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px 17px;">
                <div style="font-weight: 800; color: #0f172a; font-size: 0.88rem; margin-bottom: 3px;">Devolución en dinero</div>
                <div style="color: #64748b; font-size: 0.83rem; line-height: 1.45;">No es saldo ni crédito: vuelve a tu tarjeta.</div>
            </div>
        </div>

        <?php if (!$activa): ?>

            <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 16px 18px; border-radius: 12px; font-size: 0.9rem;">
                La garantía no está activa en este momento. Si tienes cualquier problema con tu
                suscripción, escríbenos y lo miramos igualmente.
            </div>

        <?php elseif (!$estado['logueado']): ?>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 22px;">
                <p style="color: #475569; font-size: 0.93rem; margin: 0 0 14px 0;">
                    Para pedir la devolución, entra con la cuenta con la que contrataste.
                </p>
                <a href="<?= site_url('enter') ?>" style="display: inline-block; background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 10px; font-weight: 800; text-decoration: none; font-size: 0.95rem;">
                    Iniciar sesión
                </a>
            </div>

        <?php elseif (!$estado['tiene_suscripcion']): ?>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 22px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px 18px; border-radius: 12px; color: #475569; font-size: 0.92rem; line-height: 1.55;">
                    No vemos una suscripción a Solvencia Pro en tu cuenta. Si crees que es un error,
                    <a href="<?= site_url('tickets/create') ?>" style="color: #2563eb; font-weight: 700;">abre un ticket</a>
                    y lo revisamos.
                </div>
            </div>

        <?php else: ?>

            <div style="border-top: 1px solid #e2e8f0; padding-top: 22px;">
                <?php if ($estado['dias_desde_alta'] !== null && !$estado['en_plazo']): ?>
                    <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 14px 16px; border-radius: 12px; font-size: 0.9rem; line-height: 1.5; margin-bottom: 18px;">
                        Tu suscripción tiene <?= (int) $estado['dias_desde_alta'] ?> días, así que queda fuera
                        de la ventana de <?= (int) $dias ?>. Puedes pedirla igualmente y la revisamos a mano:
                        preferimos mirar cada caso antes que rechazarlo por una fecha.
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= site_url('garantia/solicitar') ?>">
                    <?= csrf_field() ?>
                    <label for="motivo" style="display: block; font-weight: 800; color: #0f172a; font-size: 0.92rem; margin-bottom: 7px;">
                        ¿Qué esperabas que hiciera y no ha hecho? <span style="font-weight: 500; color: #94a3b8;">(opcional)</span>
                    </label>
                    <textarea id="motivo" name="motivo" rows="4" placeholder="Nos ayuda a arreglarlo. No condiciona la devolución." style="width: 100%; box-sizing: border-box; padding: 13px 15px; border: 1px solid #cbd5e1; border-radius: 12px; font-size: 0.95rem; font-family: inherit; color: #0f172a; background: #f8fafc; resize: vertical;"></textarea>

                    <button type="submit" data-loading="Enviando…" style="margin-top: 16px; background: #2563eb; color: #fff; border: none; padding: 13px 26px; border-radius: 11px; font-weight: 800; font-size: 0.97rem; cursor: pointer; box-shadow: 0 5px 16px rgba(37, 99, 235, 0.28);">
                        Solicitar la devolución
                    </button>
                    <div style="margin-top: 10px; color: #64748b; font-size: 0.82rem;">
                        Te respondemos en menos de 24 horas laborables.
                    </div>
                </form>
            </div>

        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
