<?php
$file = 'd:/laragon/www/apiempresas/app/Views/home_en_standalone.php';
$content = file_get_contents($file);

$translations = [
    'Soluciones integrales' => 'Complete Solutions',
    'Datos empresariales para <span class="gradient-text">validar, integrar y vender mejor</span>' => 'Business data to <span class="gradient-text">validate, integrate, and sell better</span>',
    'Acceda a información veraz para mejorar sus procesos de validación de clientes o para potenciar sus equipos comerciales con datos frescos.' => 'Access accurate information to improve your customer validation processes or empower your sales teams with fresh data.',
    'Validación y consulta' => 'Validation and Querying',
    'Compruebe la existencia de sociedades, verifique CIFs y acceda a los datos de registro básicos con total rapidez.' => 'Verify the existence of companies, check CIFs, and access basic registry data with total speed.',
    'Endpoints JSON para automatizar la validación de CIFs e integrar datos de empresas directamente en tu propio software o CRM.' => 'JSON endpoints to automate CIF validation and integrate company data directly into your own software or CRM.',
    'Ver Documentación API' => 'View API Documentation',
    'Genera argumentos de venta personalizados para cada empresa con nuestra IA.' => 'Generate customized sales pitches for each company using our AI.',
    'Radar B2B es la plataforma perfecta para equipos comerciales que no necesitan integración técnica.' => 'Radar B2B is the perfect platform for sales teams that don\'t need technical integration.',
    '¿Puedo probarla gratis?' => 'Can I try it for free?',
    'Sí, al registrarte obtienes un plan Free con <?= $freeLimit ?> consultas gratuitas sin caducidad para que puedas hacer pruebas en nuestro entorno Sandbox o en producción sin ningún tipo de compromiso.' => 'Yes, by registering you get a Free plan with <?= $freeLimit ?> free requests with no expiration date so you can test in our Sandbox environment or in production without any commitment.',
    '¿La información es oficial?' => 'Is the information official?',
    'Absolutamente. Todos nuestros datos provienen de fuentes oficiales del Estado, como el Registro Mercantil Central y el BORME, garantizando su validez y actualización constante.' => 'Absolutely. All our data comes from official State sources, such as the Central Mercantile Registry and the BORME, ensuring its validity and constant updating.',
    '¿Cuánto se tarda en integrar?' => 'How long does it take to integrate?',
    'Nuestra API REST está diseñada con estándares modernos y es extremadamente sencilla. Un desarrollador promedio puede completar la integración y validar su primera empresa en menos de una hora. Dispones de documentación detallada para guiarte.' => 'Our REST API is designed with modern standards and is extremely simple. An average developer can complete the integration and validate their first company in less than an hour. Detailed documentation is available to guide you.',
    '¿Sirve para prospección B2B?' => 'Is it useful for B2B prospecting?',
    'Resolvemos <span class="gradient-text" style="display: inline-block; padding-bottom: 4px;">tus dudas</span>' => 'We answer <span class="gradient-text" style="display: inline-block; padding-bottom: 4px;">your questions</span>',
    'Si no encuentras la respuesta que buscas, nuestro equipo de expertos está disponible para ayudarte a integrar la API o configurar tu Radar B2B al máximo nivel.' => 'If you can\'t find the answer you\'re looking for, our team of experts is available to help you integrate the API.',
    'Datos para prospección' => 'Data for prospecting',
    'Cerrar ventana' => 'Close window',
    'Empieza hoy a validar empresas o encontrar nuevos clientes' => 'Start validating companies or finding new customers today'
];

foreach ($translations as $es => $en) {
    $content = str_replace($es, $en, $content);
}

file_put_contents($file, $content);
echo "Done";
