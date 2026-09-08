<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/admin_app' ) ?>
<?= $this->section('styles') ?>
    <style>
        :root {
            --kpi-emerald: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-blue: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --kpi-violet: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            --kpi-rose: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
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
        .kpi-card:hover { transform: translateY(-8px); box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); }
        .kpi-card::before {
            content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px;
            background: var(--kpi-color); opacity: 0.05; border-radius: 0 0 0 100%; pointer-events: none;
        }
        .kpi-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }
        .kpi-icon-wrapper {
            width: 48px; height: 48px; border-radius: 14px; background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center;
            color: white; box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-trend-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 0.28rem 0.65rem;
            border-radius: 9999px;
            letter-spacing: 0.01em;
            line-height: 1;
        }
        .kpi-trend-badge svg {
            width: 13px;
            height: 13px;
            stroke-width: 2.5;
        }
        .kpi-trend-badge.trend-up {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .kpi-trend-badge.trend-down {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .kpi-trend-badge.trend-neutral {
            background-color: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.5rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .pill { padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; text-align: center; }

        .invoice-filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        .filter-actions {
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
        }
        .badge-user-link {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #2563eb;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 2px 7px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
        }
        .badge-user-link:hover {
            background: #dbeafe;
            color: #1d4ed8;
        }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 class="title" style="margin-bottom: 0.25rem;">Gestión de Facturas</h1>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Historial y emisión de facturación de usuarios y suscripciones</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <?php
        $trendRevenue = $stats['trend_revenue'] ?? ['diff' => 0, 'percent' => 0, 'direction' => 'neutral', 'formatted_percent' => '0%', 'previous' => 0];
        $trendCount = $stats['trend_count'] ?? ['diff' => 0, 'percent' => 0, 'direction' => 'neutral', 'formatted_percent' => '0%', 'previous' => 0];
        $trendAvg = $stats['trend_avg'] ?? ['diff' => 0, 'percent' => 0, 'direction' => 'neutral', 'formatted_percent' => '0%', 'previous' => 0];
        $trendPending = $stats['trend_pending'] ?? ['diff' => 0, 'percent' => 0, 'direction' => 'neutral', 'formatted_percent' => '0%', 'previous' => 0];
        $prevMonthName = esc($stats['prev_month_name'] ?? 'mes anterior');
    ?>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-emerald);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <span class="kpi-trend-badge trend-<?= $trendRevenue['direction'] ?>" title="Variación de facturación respecto a <?= $prevMonthName ?>">
                    <?php if ($trendRevenue['direction'] === 'up'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php elseif ($trendRevenue['direction'] === 'down'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php endif; ?>
                    <?= $trendRevenue['formatted_percent'] ?>
                </span>
            </div>
            <span class="kpi-label">Facturado (Mes)</span>
            <span class="kpi-value"><?= number_format($stats['revenue_month'], 0, ',', '.') ?> €</span>
            <span class="kpi-sub">vs. <?= number_format($trendRevenue['previous'], 0, ',', '.') ?> € en <?= $prevMonthName ?></span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <span class="kpi-trend-badge trend-<?= $trendCount['direction'] ?>" title="Variación de número de facturas respecto a <?= $prevMonthName ?>">
                    <?php if ($trendCount['direction'] === 'up'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php elseif ($trendCount['direction'] === 'down'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php endif; ?>
                    <?= $trendCount['formatted_percent'] ?>
                </span>
            </div>
            <span class="kpi-label">Facturas (Mes)</span>
            <span class="kpi-value"><?= number_format($stats['count_month'], 0, ',', '.') ?></span>
            <span class="kpi-sub">vs. <?= number_format($trendCount['previous'], 0, ',', '.') ?> emitidas en <?= $prevMonthName ?></span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-violet);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 6l-9.5 9.5-5-5L1 18"></path><polyline points="17 6 23 6 23 12"></polyline></svg>
                </div>
                <span class="kpi-trend-badge trend-<?= $trendAvg['direction'] ?>" title="Variación del ticket medio vs. <?= $prevMonthName ?>">
                    <?php if ($trendAvg['direction'] === 'up'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <?php elseif ($trendAvg['direction'] === 'down'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <?php endif; ?>
                    <?= $trendAvg['formatted_percent'] ?>
                </span>
            </div>
            <span class="kpi-label">Ticket Medio</span>
            <span class="kpi-value"><?= number_format($stats['avg_ticket'], 2, ',', '.') ?> €</span>
            <span class="kpi-sub">vs. <?= number_format($trendAvg['previous'], 2, ',', '.') ?> € en <?= $prevMonthName ?></span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-rose);">
            <div class="kpi-top-row">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <span class="kpi-trend-badge <?= $stats['pending_count'] > 0 ? 'trend-down' : 'trend-neutral' ?>">
                    <?= $stats['pending_count'] > 0 ? 'Atención' : 'Al día' ?>
                </span>
            </div>
            <span class="kpi-label">Pendientes / Fallidas</span>
            <span class="kpi-value"><?= number_format($stats['pending_count'], 0, ',', '.') ?></span>
            <span class="kpi-sub"><?= $stats['pending_count'] > 0 ? 'Facturas que requieren seguimiento' : 'Sin cobros pendientes' ?></span>
        </div>
    </div>

    <!-- Panel de Filtros -->
    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem;">
        <form action="<?= site_url('admin/invoices') ?>" method="get" id="invoicesFilterForm">
            <div class="invoice-filters-grid">
                <!-- Búsqueda libre -->
                <div style="min-width: 220px;">
                    <label class="input-label">Buscar factura o cliente</label>
                    <input type="text" name="search" value="<?= esc($search) ?>" placeholder="Nº factura, cliente, email, CIF..." class="input w-full">
                </div>

                <!-- Filtrar por Usuario -->
                <div style="min-width: 200px;">
                    <label class="input-label">Usuario</label>
                    <select name="user_id" class="input w-full">
                        <option value="">Todos los usuarios</option>
                        <?php foreach ($users_with_invoices as $u): ?>
                            <option value="<?= $u['user_id'] ?>" <?= ((string)$user_id === (string)$u['user_id']) ? 'selected' : '' ?>>
                                #<?= $u['user_id'] ?> - <?= esc($u['name'] ?: $u['email']) ?> (<?= $u['invoice_count'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtrar por Estado -->
                <div style="min-width: 140px;">
                    <label class="input-label">Estado</label>
                    <select name="status" class="input w-full">
                        <option value="">Todos los estados</option>
                        <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>✓ Pagada (<?= $stats['paid_count'] ?>)</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>⏳ Pendiente (<?= $stats['pending_count'] ?>)</option>
                        <option value="failed" <?= in_array($status, ['failed', 'uncollectible']) ? 'selected' : '' ?>>✕ Fallida (<?= $stats['failed_count'] ?>)</option>
                        <option value="cancelled" <?= in_array($status, ['cancelled', 'void']) ? 'selected' : '' ?>>⊘ Cancelada</option>
                    </select>
                </div>

                <!-- Fecha Desde -->
                <div>
                    <label class="input-label">Fecha Desde</label>
                    <input type="date" name="date_from" value="<?= esc($date_from) ?>" class="input w-full">
                </div>

                <!-- Fecha Hasta -->
                <div>
                    <label class="input-label">Fecha Hasta</label>
                    <input type="date" name="date_to" value="<?= esc($date_to) ?>" class="input w-full">
                </div>

                <!-- Ordenación -->
                <div style="min-width: 160px;">
                    <label class="input-label">Ordenar por</label>
                    <select name="order_by" class="input w-full">
                        <option value="created_at_desc" <?= $order_by === 'created_at_desc' ? 'selected' : '' ?>>Fecha: Más recientes</option>
                        <option value="created_at_asc" <?= $order_by === 'created_at_asc' ? 'selected' : '' ?>>Fecha: Más antiguas</option>
                        <option value="amount_desc" <?= $order_by === 'amount_desc' ? 'selected' : '' ?>>Importe: Mayor a menor</option>
                        <option value="amount_asc" <?= $order_by === 'amount_asc' ? 'selected' : '' ?>>Importe: Menor a mayor</option>
                        <option value="number_desc" <?= $order_by === 'number_desc' ? 'selected' : '' ?>>Nº Factura: Descendente</option>
                    </select>
                </div>

                <!-- Botones de Acción -->
                <div class="filter-actions">
                    <button type="submit" class="btn primary" style="white-space: nowrap;">Filtrar</button>
                    <a href="<?= site_url('admin/invoices') ?>" class="btn ghost" title="Limpiar todos los filtros">🔄</a>
                </div>
            </div>
        </form>

        <!-- Píldoras de Filtro Rápido -->
        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; align-items: center;">
            <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-right: 4px;">Filtro rápido por estado:</span>
            
            <!-- Todas -->
            <a href="<?= site_url('admin/invoices?' . http_build_query(array_filter(['search' => $search, 'user_id' => $user_id, 'date_from' => $date_from, 'date_to' => $date_to, 'order_by' => $order_by]))) ?>" 
               class="pill" style="text-decoration: none; padding: 5px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= empty($status) ? 'background: #2152ff; color: white;' : 'background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;' ?>">
               Todas (<?= $stats['total_invoices'] ?>)
            </a>

            <!-- Pagadas -->
            <a href="<?= site_url('admin/invoices?' . http_build_query(array_merge(array_filter(['search' => $search, 'user_id' => $user_id, 'date_from' => $date_from, 'date_to' => $date_to, 'order_by' => $order_by]), ['status' => 'paid']))) ?>" 
               class="pill" style="text-decoration: none; padding: 5px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $status === 'paid' ? 'background: #059669; color: white;' : 'background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' ?>">
               ✓ Pagadas (<?= $stats['paid_count'] ?>)
            </a>

            <!-- Pendientes -->
            <a href="<?= site_url('admin/invoices?' . http_build_query(array_merge(array_filter(['search' => $search, 'user_id' => $user_id, 'date_from' => $date_from, 'date_to' => $date_to, 'order_by' => $order_by]), ['status' => 'pending']))) ?>" 
               class="pill" style="text-decoration: none; padding: 5px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 99px; transition: all 0.2s; <?= $status === 'pending' ? 'background: #d97706; color: white;' : 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;' ?>">
               ⏳ Pendientes (<?= $stats['pending_count'] ?>)
            </a>

            <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-left: 12px; margin-right: 4px;">Periodo rápido:</span>

            <!-- Este mes -->
            <a href="<?= site_url('admin/invoices?' . http_build_query(array_merge(array_filter(['search' => $search, 'user_id' => $user_id, 'status' => $status, 'order_by' => $order_by]), ['date_preset' => 'this_month']))) ?>" 
               class="pill" style="text-decoration: none; padding: 5px 12px; font-size: 0.8rem; font-weight: 600; border-radius: 99px; transition: all 0.2s; <?= ($date_preset === 'this_month' || ($date_from === date('Y-m-01') && $date_to === date('Y-m-t'))) ? 'background: #0f172a; color: white;' : 'background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;' ?>">
               Este mes
            </a>

            <!-- Mes anterior -->
            <a href="<?= site_url('admin/invoices?' . http_build_query(array_merge(array_filter(['search' => $search, 'user_id' => $user_id, 'status' => $status, 'order_by' => $order_by]), ['date_preset' => 'last_month']))) ?>" 
               class="pill" style="text-decoration: none; padding: 5px 12px; font-size: 0.8rem; font-weight: 600; border-radius: 99px; transition: all 0.2s; <?= ($date_preset === 'last_month') ? 'background: #0f172a; color: white;' : 'background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;' ?>">
               <?= ucfirst($prevMonthName) ?>
            </a>

            <!-- Últimos 30 días -->
            <a href="<?= site_url('admin/invoices?' . http_build_query(array_merge(array_filter(['search' => $search, 'user_id' => $user_id, 'status' => $status, 'order_by' => $order_by]), ['date_preset' => 'last_30d']))) ?>" 
               class="pill" style="text-decoration: none; padding: 5px 12px; font-size: 0.8rem; font-weight: 600; border-radius: 99px; transition: all 0.2s; <?= ($date_preset === 'last_30d') ? 'background: #0f172a; color: white;' : 'background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;' ?>">
               Últimos 30 días
            </a>

            <!-- Este año -->
            <a href="<?= site_url('admin/invoices?' . http_build_query(array_merge(array_filter(['search' => $search, 'user_id' => $user_id, 'status' => $status, 'order_by' => $order_by]), ['date_preset' => 'this_year']))) ?>" 
               class="pill" style="text-decoration: none; padding: 5px 12px; font-size: 0.8rem; font-weight: 600; border-radius: 99px; transition: all 0.2s; <?= ($date_preset === 'this_year') ? 'background: #0f172a; color: white;' : 'background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;' ?>">
               <?= date('Y') ?>
            </a>
        </div>

        <?php if (!empty($user_id) || !empty($status) || !empty($date_from) || !empty($date_to) || !empty($search)): ?>
            <div style="margin-top: 1rem; padding: 0.6rem 0.9rem; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe; font-size: 0.8rem; color: #1e40af; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <strong>Filtros activos:</strong>
                    <?php if (!empty($search)): ?>
                        <span style="background: white; padding: 2px 8px; border-radius: 4px; border: 1px solid #dbeafe;">Texto: "<?= esc($search) ?>"</span>
                    <?php endif; ?>
                    <?php if (!empty($user_id)): ?>
                        <span style="background: white; padding: 2px 8px; border-radius: 4px; border: 1px solid #dbeafe;">Usuario ID: #<?= esc($user_id) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($status)): ?>
                        <span style="background: white; padding: 2px 8px; border-radius: 4px; border: 1px solid #dbeafe;">Estado: <?= esc($status) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($date_from) || !empty($date_to)): ?>
                        <span style="background: white; padding: 2px 8px; border-radius: 4px; border: 1px solid #dbeafe;">Fecha: <?= esc($date_from ?: 'inicio') ?> hasta <?= esc($date_to ?: 'hoy') ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= site_url('admin/invoices') ?>" style="color: #2563eb; font-weight: 700; text-decoration: none;">Limpiar filtros ✕</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (session('message')): ?>
        <div style="padding: 12px; background: #f0fdf4; color: #166534; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0;">
            <?= session('message') ?>
        </div>
    <?php endif; ?>

    <?php if (session('error')): ?>
        <div style="padding: 12px; background: #fef2f2; color: #991b1b; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca;">
            <?= session('error') ?>
        </div>
    <?php endif; ?>

    <!-- Listado de Facturas -->
    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 980px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 14px 12px; color: #64748b; font-size: 0.85rem; font-weight: 700;">Nº Factura</th>
                    <th style="padding: 14px 12px; color: #64748b; font-size: 0.85rem; font-weight: 700;">Cliente / Usuario</th>
                    <th style="padding: 14px 12px; color: #64748b; font-size: 0.85rem; font-weight: 700;">Fecha</th>
                    <th style="padding: 14px 12px; color: #64748b; font-size: 0.85rem; font-weight: 700;">Base + IVA</th>
                    <th style="padding: 14px 12px; color: #64748b; font-size: 0.85rem; font-weight: 700;">Total</th>
                    <th style="padding: 14px 12px; color: #64748b; font-size: 0.85rem; font-weight: 700;">Estado</th>
                    <th style="padding: 14px 12px; color: #64748b; font-size: 0.85rem; font-weight: 700; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 48px 20px; color: #94a3b8;">
                            <div style="font-size: 1.1rem; font-weight: 600; color: #64748b; margin-bottom: 6px;">No se encontraron facturas</div>
                            <div style="font-size: 0.85rem;">Prueba ajustando los filtros de búsqueda, fecha o usuario.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.15s ease;">
                            <!-- Nº Factura -->
                            <td style="padding: 14px 12px;">
                                <strong style="color: #1e293b; font-size: 0.95rem; font-family: monospace;"><?= esc($inv->invoice_number) ?></strong>
                                <?php if (!empty($inv->stripe_invoice_id)): ?>
                                    <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 2px;" title="Stripe Invoice ID: <?= esc($inv->stripe_invoice_id) ?>">
                                        ⚡ <?= esc(substr($inv->stripe_invoice_id, 0, 14)) ?>...
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Cliente y Usuario -->
                            <td style="padding: 14px 12px;">
                                <div style="font-weight: 600; color: #1e293b;"><?= esc($inv->billing_name ?: 'Sin nombre fiscal') ?></div>
                                <div style="font-size: 0.78rem; color: #64748b;"><?= esc($inv->billing_email) ?></div>
                                
                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px; flex-wrap: wrap;">
                                    <?php if (!empty($inv->billing_vat)): ?>
                                        <span style="font-size: 0.7rem; background: #f1f5f9; color: #475569; padding: 1px 6px; border-radius: 4px; font-weight: 600;">
                                            CIF/NIF: <?= esc($inv->billing_vat) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($inv->user_id)): ?>
                                        <a href="<?= site_url('admin/invoices?user_id=' . $inv->user_id) ?>" class="badge-user-link" title="Filtrar facturas del usuario #<?= $inv->user_id ?>">
                                            👤 #<?= $inv->user_id ?> <?= !empty($inv->user_account_name) ? esc($inv->user_account_name) : '' ?>
                                        </a>
                                        <a href="<?= site_url('admin/users?q=' . urlencode($inv->billing_email)) ?>" target="_blank" title="Ver ficha en Gestión de Usuarios" style="font-size: 0.7rem; color: #64748b; text-decoration: none;">
                                            ↗
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Fecha -->
                            <td style="padding: 14px 12px; font-size: 0.85rem; color: #64748b; white-space: nowrap;">
                                <div style="font-weight: 600; color: #334155;"><?= date('d/m/Y', strtotime($inv->created_at)) ?></div>
                                <div style="font-size: 0.75rem; color: #94a3b8;"><?= date('H:i', strtotime($inv->created_at)) ?> hs</div>
                            </td>

                            <!-- Base + IVA -->
                            <td style="padding: 14px 12px; font-size: 0.85rem; color: #64748b; white-space: nowrap;">
                                <div>Base: <?= number_format($inv->amount, 2, ',', '.') ?> €</div>
                                <div style="font-size: 0.75rem; color: #94a3b8;">IVA: <?= number_format($inv->tax_amount, 2, ',', '.') ?> €</div>
                            </td>

                            <!-- Total -->
                            <td style="padding: 14px 12px; white-space: nowrap;">
                                <strong style="color: #0f172a; font-size: 1.05rem;"><?= number_format($inv->total_amount, 2, ',', '.') ?> <?= esc($inv->currency) ?></strong>
                            </td>

                            <!-- Estado dinámico -->
                            <td style="padding: 14px 12px; white-space: nowrap;">
                                <?php if ($inv->status === 'paid'): ?>
                                    <span class="pill" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        Pagada
                                    </span>
                                <?php elseif ($inv->status === 'pending'): ?>
                                    <span class="pill" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        ⏳ Pendiente
                                    </span>
                                <?php elseif (in_array($inv->status, ['failed', 'uncollectible'])): ?>
                                    <span class="pill" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        ✕ Fallida
                                    </span>
                                <?php elseif (in_array($inv->status, ['cancelled', 'void'])): ?>
                                    <span class="pill" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-weight: 700;">
                                        Anulada
                                    </span>
                                <?php else: ?>
                                    <span class="pill" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-weight: 700;">
                                        <?= esc(ucfirst($inv->status)) ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Acciones -->
                            <td style="padding: 14px 12px; text-align: right; white-space: nowrap;">
                                <a href="<?= site_url('admin/invoices/download/' . $inv->id) ?>" class="btn ghost" style="padding: 6px 12px; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 6px;" title="Descargar factura PDF">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    PDF
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div style="margin-top: 2rem;">
        <?= $pager->links('default', 'admin_full') ?>
    </div>
<?= $this->endSection() ?>
