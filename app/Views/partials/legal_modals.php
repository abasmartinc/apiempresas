<?php if (service('request')->getLocale() === 'en'): ?>
    <?= view('partials/legal_modals_en') ?>
<?php else: ?>
    <?= view('partials/legal_modals_es') ?>
<?php endif; ?>
