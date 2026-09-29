<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You are now using the API</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f8fafc; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background: #059669; padding: 30px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 22px; margin-top: 0; }
        .content p { margin-bottom: 20px; color: #475569; }
        .alert-box { background: #fffbeb; border: 1px solid #fef3c7; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #f59e0b; }
        .cta-container { text-align: center; margin-top: 35px; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 16px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Congratulations, <?= esc($name) ?>!</h1>
        </div>
        <div class="content">
            <h2>Congratulations, <?= esc($name) ?>! You just made your first lookup ⚡</h2>
            <p>It is the first step towards a solid integration. You have seen how easy it is to get structured, reliable Spanish company data.</p>
            
            <div class="alert-box">
                <strong>⚡ Production tip:</strong>
                <p style="margin: 10px 0 0; font-size: 14px;">If you are taking your application to production or expect real traffic, we recommend the <strong>Pro plan</strong>. Free is 100 lookups in total; Pro gives you 3,000 every month and the full response, with no masked fields.</p>
            </div>

            <div class="cta-container">
                <a href="<?= site_url('billing') ?>" class="btn">👉 Upgrade to Pro</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #64748b;">
                We are here to make your integration a success.
            </p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> APIEmpresas - Data for smart decisions.</p>
        </div>
    </div>
</body>
</html>
