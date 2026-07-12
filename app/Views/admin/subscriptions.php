<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>
<?= $this->section('styles') ?>

    <style>
        :root {
            --kpi-free: #5c6370;
            --kpi-pro: #4f46e5;
            --kpi-biz: #4b9a69;
        }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .kpi-card { 
            position: relative;
            overflow: hidden;
            background: white; 
            border-radius: 24px; 
            padding: 2rem; 
            border: 1px solid rgba(255, 255, 255, 0.7); 
            display: flex; 
            flex-direction: column; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); 
        }
        .kpi-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); 
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 100px; height: 100px;
            background: var(--kpi-color);
            opacity: 0.05;
            border-radius: 0 0 0 100%;
            pointer-events: none;
        }
        .kpi-icon-wrapper {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.5rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Gestión de Suscripciones</h1>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('admin/subscriptions/create') ?>" class="btn">Nueva Suscripción</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-free);">
            <div class="kpi-icon-wrapper" style="background: var(--kpi-free);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <span class="kpi-label">Plan Free</span>
            <span class="kpi-value"><?= number_format($stats['free'] ?? 0, 0, ',', '.') ?></span>
            <span class="kpi-sub">Usuarios activos en Free</span>
        </div>
        <div class="kpi-card" style="--kpi-color: var(--kpi-pro);">
            <div class="kpi-icon-wrapper" style="background: var(--kpi-pro);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <span class="kpi-label">Plan Pro</span>
            <span class="kpi-value"><?= number_format($stats['pro'] ?? 0, 0, ',', '.') ?></span>
            <span class="kpi-sub">Usuarios activos en Pro</span>
        </div>
        <div class="kpi-card" style="--kpi-color: var(--kpi-biz);">
            <div class="kpi-icon-wrapper" style="background: var(--kpi-biz);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            </div>
            <span class="kpi-label">Plan Business</span>
            <span class="kpi-value"><?= number_format($stats['business'] ?? 0, 0, ',', '.') ?></span>
            <span class="kpi-sub">Usuarios activos en Business</span>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/subscriptions') ?>" method="get" class="grid" style="grid-template-columns: 1fr 1fr auto auto; gap: 1rem; align-items: end;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Usuario</label>
                <select name="user_id" id="user-select" class="input" style="width: 100%;">
                    <option value="">Todos los usuarios</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u->id ?>" <?= $user_id == $u->id ? 'selected' : '' ?>>
                            <?= esc($u->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Plan</label>
                <select name="plan_id" class="input" style="width: 100%;">
                    <option value="">Todos los planes</option>
                    <?php foreach ($plans as $p): ?>
                        <option value="<?= $p->id ?>" <?= $plan_id == $p->id ? 'selected' : '' ?>>
                            <?= esc($p->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Estado</label>
                <select name="status" class="input" style="width: 100%;">
                    <option value="">Todos</option>
                    <option value="active" <?= $status == 'active' ? 'selected' : '' ?>>Activa</option>
                    <option value="trialing" <?= $status == 'trialing' ? 'selected' : '' ?>>Prueba</option>
                    <option value="past_due" <?= $status == 'past_due' ? 'selected' : '' ?>>Vencida</option>
                    <option value="canceled" <?= $status == 'canceled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            <div style="display: flex; gap: 5px;">
                <button type="submit" class="btn">Filtrar</button>
                <a href="<?= site_url('admin/subscriptions') ?>" class="btn ghost" title="Limpiar filtros">🔄</a>
            </div>
        </form>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #bbf7d0;">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid #fecaca;">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Usuario</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Plan</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Motivo cancelaciÃ³n</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Periodo Actual</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $s): ?>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($s->user_name ?: 'Desconocido') ?></div>
                        <div style="font-size: 0.75rem; color: #64748b;"><?= esc($s->user_email ?: '-') ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <?php 
                        $planNameLow = strtolower($s->plan_name ?: '');
                        if (strpos($planNameLow, 'free') !== false) {
                            $badgeBg = '#5c6370'; $badgeColor = '#ffffff'; $badgeBorder = '#5c6370';
                        } elseif (strpos($planNameLow, 'pro') !== false) {
                            $badgeBg = '#4f46e5'; $badgeColor = '#ffffff'; $badgeBorder = '#4f46e5';
                        } elseif (strpos($planNameLow, 'business') !== false) {
                            $badgeBg = '#4b9a69'; $badgeColor = '#ffffff'; $badgeBorder = '#4b9a69';
                        } else {
                            $badgeBg = '#f1f5f9'; $badgeColor = '#475569'; $badgeBorder = '#e2e8f0';
                        }
                        ?>
                        <span class="pill" style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border: 1px solid <?= $badgeBorder ?>; font-weight: 600;">
                            <?= esc($s->plan_name ?: 'Plan #' . $s->plan_id) ?>
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <?php 
                        $statusClass = '';
                        switch($s->status) {
                            case 'active': $statusClass = 'estado--activa'; break;
                            case 'trialing': $statusClass = 'pill'; break;
                            case 'past_due': $statusClass = 'estado--inactiva'; break;
                            case 'canceled': $statusClass = 'estado--inactiva'; break;
                        }
                        ?>
                        <span class="pill <?= $statusClass ?>" style="font-size: 0.7rem;">
                            <?= strtoupper($s->status) ?>
                        </span>
                    </td>
                    <td style="padding: 12px; font-size: 0.78rem; color: #475569; max-width: 260px;">
                        <?php
                            $reasonLabels = [
                                'too_expensive' => 'Precio',
                                'missing_features' => 'Faltan funcionalidades',
                                'low_usage' => 'Poco uso',
                                'technical_issues' => 'Problemas tecnicos',
                                'switched_solution' => 'Otra solucion',
                                'temporary_pause' => 'Pausa temporal',
                                'other' => 'Otro motivo',
                                'prefer_not_to_say' => 'Prefiere no responder',
                            ];
                            $reason = $s->cancellation_reason ?? '';
                            $feedback = trim((string) ($s->cancellation_feedback ?? ''));
                        ?>
                        <?php if ($s->status === 'canceled' && ($reason || $feedback)): ?>
                            <div style="font-weight: 800; color: #0f172a;"><?= esc($reasonLabels[$reason] ?? $reason) ?></div>
                            <?php if ($feedback !== ''): ?>
                                <div title="<?= esc($feedback) ?>" style="margin-top: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= esc($feedback) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #cbd5e1;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 12px; font-size: 0.8rem;">
                        <div style="color: #16a34a;">Inicio: <?= date('d/m/Y', strtotime($s->current_period_start)) ?></div>
                        <div style="color: #ef4444;">Fin: <?= date('d/m/Y', strtotime($s->current_period_end)) ?></div>
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 5px;">
                            <a href="<?= site_url('admin/subscriptions/edit/' . $s->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem;" title="Editar">✏️</a>
                            <a href="<?= site_url('admin/subscriptions/delete/' . $s->id) ?>" class="btn ghost" style="padding: 4px 8px; font-size: 0.75rem; color: #ef4444; border-color: #fee2e2;" title="Eliminar" data-confirm="¿Estás seguro de eliminar esta suscripción?">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 2rem;">
        <?= $pager->links('default', 'admin_full') ?>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Select2 para hacer el select buscable -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    /* Ajustes para Select2 encaje con el .input */
    .select2-container--default .select2-selection--single {
        border-radius: 8px;
        height: 42px;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a;
        font-size: 0.85rem;
        line-height: normal;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        font-size: 0.85rem;
    }
    .select2-search--dropdown {
        padding: 8px !important;
    }
    .select2-search--dropdown .select2-search__field {
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        width: 100% !important;
        min-height: 34px !important;
        padding: 4px 8px !important;
        box-sizing: border-box;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #2152ff;
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var options = {
            width: '100%'
        };

        if(document.getElementById('user-select')) {
            $('#user-select').select2(Object.assign({}, options, { placeholder: "Buscar usuario..." }));
        }
        $('select[name="plan_id"]').select2(Object.assign({}, options, { placeholder: "Buscar plan..." }));
        $('select[name="status"]').select2(Object.assign({}, options, { placeholder: "Buscar estado..." }));

        // Parche definitivo: obligar a mostrar la caja de búsqueda cuando Select2 la oculta
        $(document).on('select2:open', function() {
            setTimeout(function() {
                $('.select2-search--hide').removeClass('select2-search--hide');
            }, 10);
        });
    });
</script>
<?= $this->endSection() ?>


