<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; }
        .dash-main { padding-top: 40px; padding-bottom: 60px; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #475569; text-decoration: none; font-weight: 600; padding: 10px 20px; border-radius: 10px; border: 1px solid #cbd5e1; background: #f8fafc; transition: all 0.2s; font-size: 0.95rem; }
        .btn-back:hover { color: #0f172a; border-color: #94a3b8; background: #f1f5f9; }
        
        .create-form-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden; }
        
        .form-group { margin-bottom: 24px; }
        .form-label { display: block; font-weight: 700; color: #334155; margin-bottom: 8px; font-size: 0.95rem; }
        .form-control { width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-size: 1rem; color: #0f172a; transition: all 0.2s; background: #f8fafc; }
        .form-control:focus { outline: none; border-color: #2152ff; box-shadow: 0 0 0 4px rgba(33, 82, 255, 0.1); background: white; }
        textarea.form-control { resize: vertical; min-height: 150px; }
        
        .btn-submit { background: linear-gradient(135deg, #2152ff 0%, #0369a1 100%); color: white; border: none; padding: 14px 32px; border-radius: 12px; font-weight: 800; font-size: 1.05rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(33, 82, 255, 0.2); width: 100%; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(33, 82, 255, 0.3); }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">

            <?php if(session()->getFlashdata('error')): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: '<?= esc(session()->getFlashdata('error')) ?>',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    });
                </script>
            <?php endif; ?>

            <div class="create-form-card">
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 32px; border-bottom: 1px solid #e2e8f0;">
                    <h1 style="font-size: 2rem; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.02em;"><?= lang('Tickets.create_title') ?></h1>
                    <a href="<?= site_url('tickets') ?>" class="btn-back">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        <?= lang('Tickets.btn_back') ?>
                    </a>
                </div>
                <div style="padding: 32px;">
                    <form action="<?= site_url('tickets/store') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    
                    <div class="form-group">
                        <label class="form-label" for="subject"><?= lang('Tickets.label_subject') ?></label>
                        <input type="text" id="subject" name="subject" class="form-control" placeholder="<?= lang('Tickets.ph_subject') ?>" required>
                    </div>
                    
                    <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <label class="form-label" for="category"><?= lang('Tickets.label_category') ?></label>
                            <select id="category" name="category" class="form-control">
                                <option value="api_key"><?= lang('Tickets.cat_api_key') ?></option>
                                <option value="dashboard"><?= lang('Tickets.cat_dashboard') ?></option>
                                <option value="facturacion"><?= lang('Tickets.cat_billing') ?></option>
                                <option value="busquedas"><?= lang('Tickets.cat_search') ?></option>
                                <option value="otro" selected><?= lang('Tickets.cat_other') ?></option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="priority"><?= lang('Tickets.label_priority') ?></label>
                            <select id="priority" name="priority" class="form-control">
                                <option value="low"><?= lang('Tickets.pr_low') ?></option>
                                <option value="medium" selected><?= lang('Tickets.pr_medium') ?></option>
                                <option value="high"><?= lang('Tickets.pr_high') ?></option>
                                <option value="urgent"><?= lang('Tickets.pr_urgent') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message"><?= lang('Tickets.label_message') ?></label>
                        <textarea id="message" name="message" class="form-control" placeholder="<?= lang('Tickets.ph_message') ?>" required></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="attachment"><?= lang('Tickets.label_attachment') ?></label>
                        <input type="file" id="attachment" name="attachment" class="form-control" style="padding: 10px;" accept=".jpg,.jpeg,.png,.pdf,.txt,.json">
                        <p style="font-size: 0.8rem; color: #64748b; margin-top: 4px;"><?= lang('Tickets.attachment_formats') ?></p>
                    </div>

                    <button type="submit" class="btn-submit"><?= lang('Tickets.btn_submit') ?></button>
                    </form>
                </div>
            </div>
        </div>
<?= $this->endSection() ?>
