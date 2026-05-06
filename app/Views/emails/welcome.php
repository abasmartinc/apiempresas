<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a APIEmpresas.es</title>
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
                            <p style="margin: 5px 0 0; color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500;">Datos oficiales para desarrolladores</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 40px 30px;">
                            <h2 style="margin: 0 0 20px; color: #0f172a; font-size: 24px; font-weight: 800;">¡Hola, <?= esc($name) ?>!</h2>
                            <p style="margin: 0 0 25px; color: #475569; font-size: 16px;">
                                Tu cuenta ya está lista. Estás a un paso de automatizar el enriquecimiento de datos de cualquier sociedad en España con fuentes oficiales.
                            </p>
                            
                            <!-- Steps Table -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="margin: 0 0 16px; color: #0f172a; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Tus primeros pasos:</p>
                                        
                                        <!-- Step 1 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 16px;">
                                            <tr>
                                                <td width="24" valign="top" style="color: #2152FF; font-weight: 900; font-size: 16px;">1.</td>
                                                <td style="padding-left: 10px; color: #334155; font-size: 15px;">
                                                    <strong>Obtén tu API Key:</strong> La encontrarás en la sección principal de tu <a href="<?= site_url('dashboard') ?>" style="color: #2152FF; font-weight: 700; text-decoration: none;">Dashboard</a>.
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Step 2 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 16px;">
                                            <tr>
                                                <td width="24" valign="top" style="color: #2152FF; font-weight: 900; font-size: 16px;">2.</td>
                                                <td style="padding-left: 10px; color: #334155; font-size: 15px;">
                                                    <strong>Lanza una petición:</strong> Usa el Terminal Interactivo o integra el endpoint <code>GET /companies</code>.
                                                </td>
                                            </tr>
                                        </table>
                                        
                                        <!-- Step 3 -->
                                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td width="24" valign="top" style="color: #2152FF; font-weight: 900; font-size: 16px;">3.</td>
                                                <td style="padding-left: 10px; color: #334155; font-size: 15px;">
                                                    <strong>Lee la Doc:</strong> Guía técnica completa con ejemplos en <strong>PHP, JS y Python</strong>.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                             <p style="margin: 25px 0 10px; color: #0f172a; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Ejemplo de integración rápida:</p>
                             <div style="background: #1e293b; padding: 20px; border-radius: 8px; margin: 0 0 20px;">
                                 <code style="color: #38bdf8; font-family: 'Fira Code', monospace; font-size: 13px; word-break: break-all;">
                                     curl -X GET "https://apiempresas.es/api/v1/companies?cif=B12345678" \<br>
                                     &nbsp;&nbsp;-H "X-API-KEY: TU_CLAVE_API"
                                 </code>
                             </div>

                             <p style="margin: 0 0 10px; color: #0f172a; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Respuesta esperada (JSON):</p>
                             <pre style="background: #f8fafc; color: #334155; padding: 15px; border-radius: 8px; font-family: 'Fira Code', monospace; font-size: 12px; border: 1px solid #e2e8f0; margin: 0;">{
  "success": true,
  "data": {
    "name": "GOOGLE SPAIN SL",
    "cif": "B83803884",
    "status": "ACTIVA",
    "address": "PLAZA PABLO RUIZ PICASSO, 1",
    "score": 98
  }
}</pre>

                             <p style="margin: 25px 0; color: #64748b; font-size: 14px; font-weight: 500;">
                                 Tu plan <strong>Free</strong> incluye 30 consultas mensuales para que pruebes la integración sin límites técnicos.
                             </p>

                            <!-- CTA -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0 20px;">
                                        <a href="<?= site_url('dashboard') ?>" style="display: inline-block; background-color: #2152FF; color: #ffffff !important; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 16px; box-shadow: 0 4px 12px rgba(33, 82, 255, 0.2);">Acceder a mi Dashboard</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 0 40px 40px;">
                            <p style="margin: 0; padding-top: 25px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 13px; text-align: center;">
                                Si tienes dudas técnicas, responde a este correo. Nuestro equipo de ingeniería te ayudará con la integración.<br><br>
                                &copy; <?= date('Y') ?> APIEmpresas España.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
