<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to APIEmpresas.es</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 0; background-color: #f1f5f9;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f1f5f9; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #2152FF 0%, #10B981 100%); padding: 25px 20px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 800; letter-spacing: -1px;">APIEmpresas.es</h1>
                            <p style="margin: 5px 0 0; color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500;">Official Spanish company data for developers</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 40px 30px;">
                            <h2 style="margin: 0 0 20px; color: #0f172a; font-size: 24px; font-weight: 800;">Hi <?= esc($name) ?>,</h2>
                            <p style="margin: 0 0 25px; color: #475569; font-size: 16px;">
                                Your account is ready. You are one step away from automatically enriching any Spanish company with data from official sources.
                            </p>
                            
                            <!-- Steps Table -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="margin: 0 0 16px; color: #0f172a; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Your first steps:</p>
                                        
                                        <!-- Step 1 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 16px;">
                                            <tr>
                                                <td width="24" valign="top" style="color: #2152FF; font-weight: 900; font-size: 16px;">1.</td>
                                                <td style="padding-left: 10px; color: #334155; font-size: 15px;">
                                                    <strong>Get your API Key:</strong> you will find it on the main page of your <a href="<?= site_url('dashboard') ?>" style="color: #2152FF; font-weight: 700; text-decoration: none;">Dashboard</a>.
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Step 2 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 16px;">
                                            <tr>
                                                <td width="24" valign="top" style="color: #2152FF; font-weight: 900; font-size: 16px;">2.</td>
                                                <td style="padding-left: 10px; color: #334155; font-size: 15px;">
                                                    <strong>Make a request:</strong> use the interactive terminal or call the <code>GET /companies</code> endpoint.
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Step 3 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="24" valign="top" style="color: #2152FF; font-weight: 900; font-size: 16px;">3.</td>
                                                <td style="padding-left: 10px; color: #334155; font-size: 15px;">
                                                    <strong>Read the step-by-step guide</strong> (PDF, in Spanish) below, written to start from scratch. The <a href="https://spaincompanyapi.com/docs" style="color: #2152FF; font-weight: 700; text-decoration: none;">English documentation</a> covers the same.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Guía en PDF (enlace, no adjunto: llega mejor a la bandeja y el clic se mide) -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 20px 0 5px; background-color: #eef2ff; border-radius: 12px; border: 1px solid #c7d2fe;">
                                <tr>
                                    <td style="padding: 22px 24px;">
                                        <p style="margin: 0 0 6px; color: #2152FF; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 0.08em;">Free guide · PDF · 26 pages · in Spanish</p>
                                        <p style="margin: 0 0 8px; color: #0f172a; font-weight: 800; font-size: 18px;">The company API explained step by step</p>
                                        <p style="margin: 0 0 18px; color: #475569; font-size: 14px;">What an API is, your first lookup in 5 minutes, what each function does, what to do when something fails and ready-to-copy recipes.</p>
                                        <a href="<?= site_url('public/docs/guia-api-apiempresas.pdf') ?>" style="display: inline-block; background-color: #ffffff; color: #2152FF !important; padding: 12px 22px; border-radius: 10px; border: 2px solid #2152FF; text-decoration: none; font-weight: 800; font-size: 15px;">Download the guide (PDF)</a>
                                    </td>
                                </tr>
                            </table>

                             <p style="margin: 25px 0 10px; color: #0f172a; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Quick integration example:</p>
                             <div style="background: #1e293b; padding: 20px; border-radius: 8px; margin: 0 0 20px;">
                                 <code style="color: #38bdf8; font-family: 'Fira Code', monospace; font-size: 13px; word-break: break-all;">
                                     curl -X GET "https://apiempresas.es/api/v1/companies?cif=A15075062" \<br>
                                     &nbsp;&nbsp;-H "X-API-KEY: YOUR_API_KEY"
                                 </code>
                             </div>

                             <p style="margin: 0 0 10px; color: #0f172a; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Response on the Free plan (JSON):</p>
                             <pre style="background: #f8fafc; color: #334155; padding: 15px; border-radius: 8px; font-family: 'Fira Code', monospace; font-size: 12px; border: 1px solid #e2e8f0; margin: 0;">{
  "success": true,
  "data": {
    "name": "INDUSTRIA DE DISENO TEXTIL SA",
    "cif": "A15075062",
    "status": "ACTIVA",
    "province": "A CORUÑA",
    "address": "*** [ACTUALIZA A PRO PARA VER LA DIRECCION ]"
  }
}</pre>

                             <p style="margin: 25px 0; color: #64748b; font-size: 14px; font-weight: 500;">
                                 Your <strong>Free</strong> plan includes <?= $freeLimit ?> free lookups to test your integration, with no expiry. Some fields, such as the address, are masked until you upgrade to Pro.
                             </p>

                            <!-- CTA -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0 20px;">
                                        <a href="<?= site_url('dashboard') ?>" style="display: inline-block; background-color: #2152FF; color: #ffffff !important; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 16px; box-shadow: 0 4px 12px rgba(33, 82, 255, 0.2);">Open my dashboard</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 0 40px 40px;">
                            <p style="margin: 0; padding-top: 25px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 13px; text-align: center;">
                                If you have technical questions, reply to this email. Our engineering team will help you with the integration.<br><br>
                                &copy; <?= date('Y') ?> APIEmpresas.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
