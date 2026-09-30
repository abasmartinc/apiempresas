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
                            <p style="margin: 0 0 20px; color: #475569; font-size: 16px;">
                                Tu cuenta y tu API Key ya están listas. Lo más rápido para ver qué devuelve la API: una consulta real desde tu panel, sin escribir código.
                            </p>

                            <!-- Una sola acción principal: la consulta de prueba (dashboard?probar=) -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 5px 0 25px;">
                                        <a href="<?= site_url('dashboard?probar=A15075062') ?>" style="display: inline-block; background-color: #2152FF; color: #ffffff !important; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 16px; box-shadow: 0 4px 12px rgba(33, 82, 255, 0.2);">Ver mi primera consulta</a>
                                        <p style="margin: 10px 0 0; color: #94a3b8; font-size: 13px;">Consulta la ficha de Inditex (A15075062) y gasta 1 de tus <?= $freeLimit ?> consultas gratuitas.</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 10px; color: #0f172a; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">Y desde tu código:</p>
                            <div style="background: #1e293b; padding: 20px; border-radius: 8px; margin: 0 0 12px;">
                                <code style="color: #38bdf8; font-family: 'Fira Code', monospace; font-size: 13px; word-break: break-all;">
                                    curl "https://apiempresas.es/api/v1/companies?cif=A15075062" \<br>
                                    &nbsp;&nbsp;-H "X-API-KEY: TU_CLAVE_API"
                                </code>
                            </div>
                            <p style="margin: 0 0 20px; color: #64748b; font-size: 14px;">
                                Tu clave está en el panel. Para probar sin gastar consultas, usa la misma clave con <code>/api/sandbox/v1</code>.
                            </p>

                            <p style="margin: 0 0 20px; color: #64748b; font-size: 14px;">
                                Tu plan <strong>Free</strong> incluye <?= $freeLimit ?> consultas en total (no se renuevan), con los datos básicos de cada empresa. La dirección completa y los administradores vienen con el plan Pro.
                            </p>

                            <p style="margin: 0 0 5px; color: #64748b; font-size: 14px;">
                                ¿Empiezas desde cero con las APIs? Tienes una <a href="<?= site_url('public/docs/guia-api-apiempresas.pdf') ?>" style="color: #2152FF; font-weight: 700; text-decoration: none;">guía paso a paso en PDF</a> (26 páginas, sin jerga).
                            </p>
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
