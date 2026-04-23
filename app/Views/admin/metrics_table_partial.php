<div class="table-responsive">
    <table class="table table-custom mb-0">
        <thead>
            <tr>
                <th>Acción</th>
                <th>Ruta</th>
                <th>Elemento</th>
                <th>Usuario / ID Anon</th>
                <th class="text-end">Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($recentEvents)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No se encontraron eventos.</td></tr>
            <?php endif; ?>
            <?php foreach($recentEvents as $event): ?>
            <tr>
                <td><span class="badge-event"><?= $event['event_name'] ?></span></td>
                <td class="text-truncate" style="max-width: 250px;"><?= parse_url($event['page'], PHP_URL_PATH) ?></td>
                <td class="text-muted small"><?= esc($event['element'] ?: 'N/A') ?></td>
                <td class="small">
                    <?php if($event['user_id']): ?>
                        <span class="text-primary fw-bold">ID: <?= $event['user_id'] ?></span>
                    <?php else: ?>
                        <span class="text-muted">Anon: <?= substr($event['anonymous_id'], -8) ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-end small text-muted"><?= date('H:i d/m', strtotime($event['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-4 d-flex justify-content-center ajax-pagination">
    <?= $pager->links('events', 'admin_full') ?>
</div>
