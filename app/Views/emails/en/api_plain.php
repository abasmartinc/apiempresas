<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($subject) ?></title>
</head>
<!-- Plain person-to-person email: no header, no button, no colours. -->
<body style="margin: 0; padding: 16px; background: #ffffff; font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.6; color: #222222;">
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all; font-size: 1px; line-height: 1px; color: #ffffff;"><?= esc($preheader) ?></div>
    <div style="max-width: 560px;">
        <p style="margin: 0 0 14px;">Hi <?= esc($name) ?>,</p>
        <?= $content ?>
    </div>
</body>
</html>
