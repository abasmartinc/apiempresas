<?php
/**
 * Radar Bridge Component
 * @var string|null $context
 */

$title = "💡 ¿Buscas empresas nuevas automáticamente?";
$text = "En lugar de hacer búsquedas manuales, el Radar detecta nuevas empresas por ti según sector y provincia.";

if (($context ?? '') === 'search') {
    $title = "💡 ¿Cansado de buscar una a una?";
    $text = "El Radar automatiza tus búsquedas y te avisa cuando aparecen empresas que encajan con tu cliente ideal.";
} elseif (($context ?? '') === 'validation') {
    $title = "💡 No solo valides, ¡descubre!";
    $text = "Mientras validas empresas con la API, el Radar puede estar encontrando tu próxima gran oportunidad de venta.";
}
?>

<div class="radar-upsell">
    <div class="radar-upsell-icon">📡</div>
    <div class="radar-upsell-body">
        <h4><?= $title ?></h4>
        <p><?= $text ?></p>
    </div>
    <div class="radar-upsell-action">
        <a href="<?= site_url('radar') ?>" class="btn-radar">Explorar Radar &rarr;</a>
    </div>
</div>
