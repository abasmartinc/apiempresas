<?php
/**
 * UI Alert Component
 * @param string $type success|info|warning|error (default: info)
 * @param string $title Alert title
 * @param string $text Alert text
 * @param string $actionUrl (optional) Link for CTA button
 * @param string $actionText (optional) Text for CTA button
 */
$type = $type ?? 'info';
$title = $title ?? '';
$text = $text ?? '';
$actionUrl = $actionUrl ?? '';
$actionText = $actionText ?? 'Ver más';

// Definiendo paletas según tipo
$palettes = [
    'info' => [
        'bg' => '#f0f9ff', 'border' => '#bae6fd', 'icon_bg' => '#2152ff', 'title' => '#0c4a6e', 'text' => '#0369a1',
        'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>'
    ],
    'success' => [
        'bg' => '#ecfdf5', 'border' => '#a7f3d0', 'icon_bg' => '#10b981', 'title' => '#065f46', 'text' => '#047857',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>'
    ],
    'warning' => [
        'bg' => '#fffbeb', 'border' => '#fde68a', 'icon_bg' => '#f59e0b', 'title' => '#92400e', 'text' => '#b45309',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
    ],
    'error' => [
        'bg' => '#fef2f2', 'border' => '#fecaca', 'icon_bg' => '#ef4444', 'title' => '#991b1b', 'text' => '#b91c1c',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
    ]
];

$theme = $palettes[$type] ?? $palettes['info'];
?>
<div style="background: <?= $theme['bg'] ?>; border: 1px solid <?= $theme['border'] ?>; border-radius: 16px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 16px; transition: all 0.2s ease;">
    <div style="background: <?= $theme['icon_bg'] ?>; color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <?= $theme['icon'] ?>
    </div>
    <div style="flex: 1;">
        <?php if($title): ?>
            <h3 style="margin: 0 0 4px; font-size: 1.1rem; font-weight: 800; color: <?= $theme['title'] ?>;"><?= esc($title) ?></h3>
        <?php endif; ?>
        <?php if($text): ?>
            <p style="margin: 0 0 <?= $actionUrl ? '12px' : '0' ?>; font-size: 0.95rem; color: <?= $theme['text'] ?>; font-weight: 600; line-height: 1.4;"><?= $text ?></p>
        <?php endif; ?>
        <?php if($actionUrl): ?>
            <a href="<?= esc($actionUrl) ?>" style="display: inline-block; background: <?= $theme['icon_bg'] ?>; color: white; padding: 6px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 800; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';"><?= esc($actionText) ?></a>
        <?php endif; ?>
    </div>
</div>
