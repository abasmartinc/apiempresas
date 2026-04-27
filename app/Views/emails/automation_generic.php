<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación APIEmpresas.es</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f7f6; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1a1a1a; padding: 30px; text-align: center;">
                            <img src="https://apiempresas.es/logo.png" alt="APIEmpresas.es" style="max-width: 200px;">
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px; line-height: 1.6;">
                            <h2 style="margin-top: 0; color: #1a1a1a; font-size: 22px;">Hola <?= esc($name) ?>,</h2>
                            <p style="font-size: 16px; color: #4b5563;">
                                <?= $content ?>
                            </p>
                            
                            <!-- CTA Button -->
                            <table border="0" cellspacing="0" cellpadding="0" style="margin: 35px 0;">
                                <tr>
                                    <td align="center" bgcolor="#2563eb" style="border-radius: 8px;">
                                        <a href="<?= $button_url ?>" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                                            <?= esc($button_text) ?>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0;">
                            
                            <p style="font-size: 14px; color: #9ca3af; margin-bottom: 0;">
                                Atentamente,<br>
                                <strong>El equipo de APIEmpresas.es</strong>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af;">
                            &copy; <?= date('Y') ?> APIEmpresas.es. Todos los derechos reservados.<br>
                            Estás recibiendo este email porque te registraste en nuestra plataforma.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
