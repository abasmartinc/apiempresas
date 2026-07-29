<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>

<?= $this->section('styles') ?>
<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-header-wrap {
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 48px;
    }

    .profile-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 900;
        box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5);
    }

    .profile-header {
        flex: 1;
    }
    .profile-title {
        font-size: 32px;
        font-weight: 900;
        color: var(--ve-text);
        margin: 0 0 4px;
        letter-spacing: -0.5px;
    }
    .profile-subtitle {
        color: var(--ve-muted);
        font-size: 16px;
        margin: 0;
    }

    .wow-card {
        background: var(--ve-card);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        margin-bottom: 32px;
    }
    
    .wow-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        border-color: #cbd5e1;
    }

    /* Subtle gradient glow behind the card content */
    .wow-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 150px; height: 150px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
        pointer-events: none;
    }

    .profile-card-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--ve-text);
        margin: 0 0 8px;
    }
    .profile-card-desc {
        font-size: 14px;
        color: var(--ve-muted);
        margin: 0 0 32px;
        line-height: 1.5;
    }
    
    .p-form-group {
        margin-bottom: 24px;
    }
    .p-form-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
    }
    .p-form-input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 15px;
        color: var(--ve-text);
        background: #f8fafc;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    .p-form-input:focus {
        outline: none;
        border-color: #10b981;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
    }
    
    .btn-wow {
        padding: 14px 24px;
        border: none;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }
    .btn-wow-primary {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
        box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.39);
    }
    .btn-wow-primary:hover {
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.23);
        transform: translateY(-2px);
    }
    .btn-wow-danger {
        background: #fef2f2;
        color: #ef4444;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
    }
    .btn-wow-danger:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    .p-alert {
        padding: 16px 20px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: fadeIn 0.4s ease-out;
    }
    .p-alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    .p-alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .ips-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .ips-table th {
        text-align: left;
        padding: 12px 16px;
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
        border-bottom: 1px solid #e2e8f0;
    }
    .ips-table td {
        padding: 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-size: 14px;
        font-weight: 500;
    }
    .ips-table tr:last-child td {
        border-bottom: none;
    }
    
    .add-ip-box {
        background: #f8fafc;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 32px;
        border: 1px solid #e2e8f0;
    }

    @media (max-width: 768px) {
        .profile-header-wrap { flex-direction: column; text-align: center; }
        .add-ip-box form { flex-direction: column; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="profile-container">
    
    <div class="profile-header-wrap">
        <div class="profile-avatar-large">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
        </div>
        <div class="profile-header">
            <h1 class="profile-title"><?= lang('Whitelist.title') ?></h1>
            <p class="profile-subtitle"><?= lang('Whitelist.subtitle') ?></p>
        </div>
    </div>

    <div class="wow-card">
        <h2 class="profile-card-title"><?= lang('Whitelist.add_ip_title') ?></h2>
        <p class="profile-card-desc"><?= lang('Whitelist.add_ip_desc') ?></p>
        
        <div class="add-ip-box">
            <form action="<?= site_url('whitelist-ips/add') ?>" method="POST" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
                <?= csrf_field() ?>
                
                <div style="flex: 1; min-width: 200px;">
                    <label for="ip_address" class="p-form-label"><?= lang('Whitelist.ip_address') ?></label>
                    <input type="text" id="ip_address" name="ip_address" class="p-form-input" placeholder="<?= lang('Whitelist.ip_placeholder') ?>" required>
                </div>
                
                <div style="flex: 1; min-width: 200px;">
                    <label for="description" class="p-form-label"><?= lang('Whitelist.description') ?></label>
                    <input type="text" id="description" name="description" class="p-form-input" placeholder="<?= lang('Whitelist.desc_placeholder') ?>">
                </div>
                
                <button type="submit" class="btn-wow btn-wow-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <?= lang('Whitelist.add_ip_btn') ?>
                </button>
            </form>
        </div>

        <h2 class="profile-card-title"><?= lang('Whitelist.allowed_ips') ?></h2>
        <?php if(empty($ips)): ?>
            <div style="padding: 40px; text-align: center; border: 2px dashed #e2e8f0; border-radius: 16px;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: #94a3b8; margin-bottom: 16px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <h3 style="margin: 0 0 8px; font-weight: 700; color: #475569;"><?= lang('Whitelist.no_restrictions') ?></h3>
                <p style="margin: 0; color: #64748b; font-size: 14px;"><?= lang('Whitelist.no_restrictions_desc') ?></p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="ips-table">
                    <thead>
                        <tr>
                            <th><?= lang('Whitelist.table_ip') ?></th>
                            <th><?= lang('Whitelist.table_desc') ?></th>
                            <th><?= lang('Whitelist.table_date') ?></th>
                            <th style="text-align: right;"><?= lang('Whitelist.table_actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ips as $ip): ?>
                        <tr>
                            <td style="font-family: monospace; font-size: 15px; color: #0f172a; font-weight: 600;">
                                <?= esc($ip->ip_address) ?>
                            </td>
                            <td><?= esc($ip->description ?: '-') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($ip->created_at)) ?></td>
                            <td style="text-align: right;">
                                <form action="<?= site_url('whitelist-ips/delete/'.$ip->id) ?>" method="POST" class="delete-ip-form" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="button" class="btn-wow btn-wow-danger btn-delete-ip" title="Eliminar IP">
                                        <?= lang('Whitelist.delete_btn') ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (session()->getFlashdata('success')): ?>
            if (typeof toastr !== 'undefined') {
                toastr.success("<?= addslashes(session()->getFlashdata('success')) ?>");
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "<?= addslashes(session()->getFlashdata('success')) ?>",
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert("<?= addslashes(session()->getFlashdata('success')) ?>");
            }
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            if (typeof toastr !== 'undefined') {
                toastr.error("<?= addslashes(session()->getFlashdata('error')) ?>");
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: "<?= addslashes(session()->getFlashdata('error')) ?>",
                    showConfirmButton: false,
                    timer: 3000
                });
            } else {
                alert("<?= addslashes(session()->getFlashdata('error')) ?>");
            }
        <?php endif; ?>

        const deleteButtons = document.querySelectorAll('.btn-delete-ip');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '<?= lang('Whitelist.confirm_title') ?>',
                        text: "<?= lang('Whitelist.confirm_text') ?>",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: '<?= lang('Whitelist.confirm_yes') ?>',
                        cancelButtonText: '<?= lang('Whitelist.confirm_no') ?>'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('<?= lang('Whitelist.confirm_text') ?>')) {
                        form.submit();
                    }
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>
