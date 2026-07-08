<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<style>
    .docs-error-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background: var(--bg-card, #fff);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color, #e0e0e0);
        font-family: system-ui, -apple-system, sans-serif;
    }
    .docs-error-header {
        border-bottom: 1px solid var(--border-color, #e0e0e0);
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    .docs-error-title {
        font-size: 24px;
        color: #d32f2f;
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .docs-error-code {
        background: #f5f5f5;
        padding: 4px 12px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 16px;
        color: #333;
        border: 1px solid #ddd;
    }
    .docs-error-content h3 {
        margin-top: 25px;
        color: var(--text-color, #333);
    }
    .docs-error-content p {
        color: var(--text-muted, #666);
        line-height: 1.6;
    }
    .btn-back {
        display: inline-block;
        margin-top: 30px;
        padding: 10px 20px;
        background: #f0f0f0;
        color: #333;
        text-decoration: none;
        border-radius: 6px;
        font-weight: 500;
        transition: background 0.2s;
    }
    .btn-back:hover {
        background: #e0e0e0;
    }
    
    [data-theme="dark"] .docs-error-container {
        background: #1e1e1e;
        border-color: #333;
    }
    [data-theme="dark"] .docs-error-header {
        border-color: #333;
    }
    [data-theme="dark"] .docs-error-title {
        color: #ff5252;
    }
    [data-theme="dark"] .docs-error-code {
        background: #2d2d2d;
        border-color: #444;
        color: #e0e0e0;
    }
    [data-theme="dark"] .docs-error-content h3 {
        color: #fff;
    }
    [data-theme="dark"] .docs-error-content p {
        color: #bbb;
    }
    [data-theme="dark"] .btn-back {
        background: #333;
        color: #fff;
    }
    [data-theme="dark"] .btn-back:hover {
        background: #444;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="docs-error-container">
        <div class="docs-error-header">
            <h1 class="docs-error-title">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                Detalles del Error
            </h1>
            <span class="docs-error-code"><?= esc($errorCode) ?></span>
        </div>
        
        <div class="docs-error-content">
            <h3>¿Qué significa este error?</h3>
            <?php if ($errorCode === 'invalid_cif_format'): ?>
                <p>El CIF (Código de Identificación Fiscal) proporcionado en tu petición no cumple con el formato oficial establecido en España.</p>
                <p><strong>Formato válido:</strong> Debe contener exactamente 9 caracteres: una letra inicial, seguida de 7 números y un carácter de control final (que puede ser letra o número).</p>
            <?php elseif ($errorCode === 'too_many_requests'): ?>
                <p>Has superado el límite de peticiones permitidas por tu plan actual (Rate Limit).</p>
                <p>Por favor, reduce la velocidad de tus peticiones o actualiza tu plan en el panel de control para obtener límites más altos.</p>
            <?php elseif ($errorCode === 'company_not_found'): ?>
                <p>No hemos podido encontrar ninguna empresa que coincida con los parámetros de búsqueda proporcionados.</p>
                <p>Verifica que el CIF u otros datos introducidos sean correctos.</p>
            <?php else: ?>
                <p>Estamos trabajando en la documentación detallada para este código de error específico.</p>
                <p>Por lo general, el campo <code>message</code> o <code>detail</code> en la respuesta de la API contiene información suficiente para que puedas diagnosticar y solucionar el problema en tu implementación.</p>
            <?php endif; ?>
            
            <h3>¿Cómo solucionarlo?</h3>
            <p>Revisa los datos que estás enviando en tu petición y compáralos con el mensaje devuelto por la API en el campo <code>detail</code>. Si sigues teniendo problemas, contacta con nuestro soporte.</p>
        </div>
        
        <a href="<?= site_url('documentation') ?>" class="btn-back">
            &larr; Volver a la Documentación
        </a>
    </div>
</div>
<?= $this->endSection() ?>
