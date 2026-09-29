<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Did something get in the way with the API?</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: #f1f5f9; padding: 30px 20px; text-align: center; color: #0f172a; border-bottom: 1px solid #e2e8f0; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .cta-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #0f172a; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>APIEmpresas.es</h1>
        </div>
        <div class="content">
            <h2>Hi <?= esc($name) ?>,</h2>
            
            <p>You created your account a few days ago and your API Key has not made any calls yet.</p>

            <p>If something got in the way (authentication, the tax ID format or your project's language), <strong>reply to this email and tell us what you are building</strong>: we will send you ready-to-use code for your case.</p>

            <p>And if you just ran out of time, here is the full call. The tax ID is real, so it returns real data:</p>

            <div style="background: #1e293b; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <p style="color: #94a3b8; font-size: 12px; margin-bottom: 10px; font-family: monospace;"># Quick request example (curl)</p>
                <code style="color: #38bdf8; font-family: 'Fira Code', monospace; font-size: 13px; word-break: break-all;">
                    curl -X GET "https://apiempresas.es/api/v1/companies?cif=A15075062" \<br>
                    &nbsp;&nbsp;-H "X-API-KEY: YOUR_API_KEY"
                </code>
            </div>

            <p>Remember that on the <strong>Free plan</strong> you get the full structure, with some fields masked:</p>

            <pre style="background: #f1f5f9; color: #334155; padding: 15px; border-radius: 8px; font-family: 'Fira Code', monospace; font-size: 12px; border: 1px solid #e2e8f0;">{
  "success": true,
  "data": {
    "name": "INDUSTRIA DE DISENO TEXTIL SA",
    "cif": "A15075062",
    "status": "ACTIVA",
    "founded": "1985-06-12",
    "province": "A CORUÑA",
    "cnae": "4642",
    "address": "*** [ACTUALIZA A PRO PARA VER LA DIRECCION ]",
    "corporate_purpose": "COMERCIO AL POR MAYOR Y MENOR DE TODA CLASE DE PRENDAS DE VESTIR."
  }
}</pre>

            <div class="cta-container">
                <a href="<?= site_url('dashboard?probar=A15075062') ?>" class="btn">▶ Try it now in one click</a>
            </div>

        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas - Official Spanish company data for developers.</p>
        </div>
    </div>
</body>
</html>
