<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABASmart API Docs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.15.5/swagger-ui.css">
    <style>
        .title{
            padding: 15px;
            background-color: #2c7bab;
            color: #fff !important;
        }
        .text-center{
            margin: auto;
            text-align: center;
        }
        #swagger-ui{
            margin-top: -40px;
        }

        .main .link{
            display: none;
        }
    </style>
</head>
<body>
<div class="text-center">
    <img style="width: 250px" src="<?=getenv('logo_path') ?>"/>
</div>
<div id="swagger-ui"></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/4.15.5/swagger-ui-bundle.js"></script>
<script>
    const ui = SwaggerUIBundle({
        url: "<?= base_url('public/swagger.json') ?>",
        dom_id: '#swagger-ui',
    });
</script>
</body>
</html>

