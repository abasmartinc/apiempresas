<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set up your APIEmpresas integration in 1 minute 🚀</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #2152FF 0%, #12B48A 100%); padding: 30px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .cta-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; }
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
            <p>Signing up is only the first step. The key moment is when you receive your first real data object in your own system.</p>
            
            <p>To make it easy, this is all it takes to make your first request:</p>
            
            <div style="background: #1e293b; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                <code style="color: #38bdf8; font-family: 'Fira Code', monospace; font-size: 13px; word-break: break-all;">
                    curl -X GET "https://apiempresas.es/api/v1/companies?cif=A15075062" \<br>
                    &nbsp;&nbsp;-H "X-API-KEY: YOUR_API_KEY"
                </code>
            </div>

            <p>It is a real tax ID (Inditex), so your first test returns real data. This is the response on your <strong>Free plan</strong>:</p>

            <pre style="background: #0f172a; color: #e2e8f0; padding: 20px; border-radius: 8px; font-family: 'Fira Code', monospace; font-size: 13px; line-height: 1.5; overflow-x: auto;">{
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

            <p style="margin-top: 25px;">Just send a <code>GET</code> request to our endpoint with your <strong>X-API-KEY</strong> in the headers.</p>

            <div class="cta-container">
                <a href="<?= site_url('dashboard?probar=A15075062') ?>" class="btn">▶ Try it now with a real company</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                <strong>Note:</strong> the Free plan automatically masks some fields, such as the address ("ACTUALIZA A PRO" means "upgrade to Pro"). Once your integration works, you can switch to the Pro plan to unlock all fields without changing a single line of code.
            </p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas - Official Spanish company data for developers.</p>
        </div>
    </div>
</body>
</html>
