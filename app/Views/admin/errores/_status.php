<?php
    $styles = [
        'open'      => ['#991b1b', '#fee2e2', '●'],
        'regressed' => ['#9a3412', '#ffedd5', '↻'],
        'resolved'  => ['#166534', '#dcfce7', '✓'],
        'ignored'   => ['#475569', '#f1f5f9', '–'],
    ];
    [$fg, $bg, $icon] = $styles[$status] ?? ['#475569', '#f1f5f9', '?'];
    $label = \App\Libraries\ErrorTracking::STATUS_LABELS[$status] ?? (string) $status;
?>
<span class="et-status" style="color: <?= $fg ?>; background: <?= $bg ?>;"><?= $icon ?> <?= esc(mb_strtoupper($label)) ?></span>
