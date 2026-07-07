<?php
/**
 * UI Card Component
 * @param string $title Card title (optional)
 * @param string $description Card description (optional)
 * @param string $content Card main content HTML
 * @param string $footer Card footer HTML (optional)
 * @param string $icon SVG icon for title (optional)
 * @param string $class Additional CSS classes
 */
$title = $title ?? '';
$description = $description ?? '';
$content = $content ?? '';
$footer = $footer ?? '';
$icon = $icon ?? '';
$class = $class ?? '';
?>
<div class="ui-card <?= esc($class) ?>" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 24px; overflow: hidden; transition: all 0.2s;">
    <?php if($title || $description): ?>
        <div class="ui-card-header" style="padding: 24px 24px 16px; border-bottom: 1px solid #f1f5f9;">
            <?php if($title): ?>
                <h3 style="margin: 0 0 4px; font-size: 1.15rem; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                    <?php if($icon): ?>
                        <span style="color: #2152ff; display: flex; align-items: center;"><?= $icon ?></span>
                    <?php endif; ?>
                    <?= esc($title) ?>
                </h3>
            <?php endif; ?>
            <?php if($description): ?>
                <p style="margin: 0; font-size: 0.9rem; color: #64748b; font-weight: 600;"><?= esc($description) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="ui-card-body" style="padding: 24px;">
        <?= $content ?>
    </div>
    
    <?php if($footer): ?>
        <div class="ui-card-footer" style="padding: 16px 24px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between;">
            <?= $footer ?>
        </div>
    <?php endif; ?>
</div>
