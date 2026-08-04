<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Guion de Ventas</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 0; color: #334155; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #a855f7, #3b82f6); padding: 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 800; }
        .content { padding: 30px; }
        .score-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .score-number { font-size: 32px; font-weight: 900; color: #16a34a; line-height: 1; }
        .score-text { font-size: 14px; color: #15803d; margin-top: 5px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
        .box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; font-size: 15px; line-height: 1.5; color: #0f172a; }
        .box-highlight { background: #eff6ff; border-color: #bfdbfe; }
        .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
        .btn { display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dossier Copiloto B2B</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Empresa: <?= esc($companyName) ?></p>
        </div>
        
        <div class="content">
            <p>Hola <?= esc($user->name) ?>,</p>
            <p>Aquí tienes el resumen y los guiones generados por IA para atacar esta cuenta con tu producto/servicio de: <strong><?= esc($product) ?></strong>.</p>
            
            <div class="score-box">
                <div class="score-number"><?= esc($dossier['score']) ?>/100</div>
                <div class="score-text"><?= esc($dossier['score_insight']) ?></div>
            </div>

            <div class="section">
                <div class="section-title">🎯 Motivo de llamada (Trigger)</div>
                <div class="box box-highlight"><?= nl2br(esc($dossier['trigger_event'])) ?></div>
            </div>

            <div class="section">
                <div class="section-title">📞 Guion Cold Call</div>
                <div class="box"><em>"<?= nl2br(esc($dossier['cold_call'])) ?>"</em></div>
            </div>

            <div class="section">
                <div class="section-title">✉️ Secuencia Email</div>
                <div class="box" style="margin-bottom: 10px;">
                    <strong>Email 1:</strong><br>
                    <?= nl2br(esc($dossier['email_1'])) ?>
                </div>
                <div class="box">
                    <strong>Follow-up (Email 2):</strong><br>
                    <?= nl2br(esc($dossier['email_2'])) ?>
                </div>
            </div>

            <div class="section">
                <div class="section-title">💼 LinkedIn Icebreaker</div>
                <div class="box"><?= nl2br(esc($dossier['linkedin'])) ?></div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="<?= site_url('empresa/' . $cif) ?>" class="btn">Ver ficha completa de la empresa</a>
            </div>
        </div>

        <div class="footer">
            Este email ha sido generado por el Copiloto de Inteligencia de API Empresas.<br>
            © <?= date('Y') ?> API Empresas. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
