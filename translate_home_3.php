<?php
$file = 'd:/laragon/www/apiempresas/app/Views/home_en_standalone.php';
$content = file_get_contents($file);

$translations = [
    'Soporte Técnico' => 'Technical Support',
    'Soporte en España' => 'Support in Spain',
    'Tiempo de respuesta &lt; 2h' => 'Response time &lt; 2h',
    'Contactar equipo' => 'Contact team',
    '¿Qué datos devuelve la API?' => 'What data does the API return?',
    'Devuelve la razón social oficial, el estado de actividad (activa, extinguida, etc.), la fecha de constitución, la provincia, y la actividad principal (CNAE) obtenida directamente del Registro Mercantil.' => 'It returns the official company name, activity status, incorporation date, province, and main activity (CNAE) obtained directly from the Mercantile Registry.',
    '<div class="cap-feature-name">Validación y Enriquecimiento</div>' => '<div class="cap-feature-name">Validation and Enrichment</div>',
    '<div class="cap-feature-name">Buscador Inteligente</div>' => '<div class="cap-feature-name">Intelligent Search</div>',
    '<div class="cap-feature-name">Consulta Múltiple (Batch)</div>' => '<div class="cap-feature-name">Batch Query</div>',
    '<div class="cap-feature-name">Scoring Comercial IA</div>' => '<div class="cap-feature-name">AI Commercial Scoring</div>',
    '<div class="cap-feature-name">Señales Societarias BORME</div>' => '<div class="cap-feature-name">BORME Corporate Signals</div>',
    '<div class="cap-feature-name">Historial Actos BORME</div>' => '<div class="cap-feature-name">BORME Acts History</div>'
];

foreach ($translations as $es => $en) {
    $content = str_replace($es, $en, $content);
}

file_put_contents($file, $content);
echo "Done 3";
