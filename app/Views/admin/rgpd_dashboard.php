<?= $this->extend('layouts/admin_app') ?>

<?= $this->section('styles') ?>
<style>
    .rgpd-card {
        background: #fff;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 4px 20px -2px rgba(0,0,0,0.05);
        margin-bottom: 32px;
    }
    .form-group { margin-bottom: 20px; }
    .form-control {
        width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid #e2e8f0;
        font-size: 1rem;
    }
    .btn { padding: 12px 24px; border-radius: 12px; font-weight: 600; cursor: pointer; border: none; }
    .btn-primary { background: #2152ff; color: white; }
    .btn-danger { background: #ef4444; color: white; }
    .preview-box {
        background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-top: 24px; display: none;
    }
    .cert-box {
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 24px; margin-top: 24px; display: none;
    }
    textarea { width: 100%; border-radius: 12px; border: 1px solid #e2e8f0; padding: 12px; min-height: 120px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
    th { color: #64748b; font-weight: 600; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div style="margin-bottom: 48px;">
    <h1 class="title" style="font-size: 2.8rem; margin-bottom: 8px;">Gestión <span class="grad">RGPD</span></h1>
    <p style="color: #64748b; font-size: 1.1rem;">Panel de anonimización y derecho al olvido.</p>
</div>

<div class="rgpd-card">
    <h2 style="margin-top:0;">Nueva Solicitud de Supresión</h2>
    <p style="color: #64748b; margin-bottom: 24px;">Introduce los datos del administrador para buscar y anonimizar sus registros históricos.</p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
            <label><strong>Nombre Exacto (Ej: LUIS MIGUEL GARCIA GONZALEZ)</strong></label>
            <input type="text" id="rgpdName" class="form-control" placeholder="Nombre completo sin acentos">
        </div>
        <div class="form-group">
            <label><strong>Slug (Ej: garcia-gonzalez-luis-miguel)</strong></label>
            <input type="text" id="rgpdSlug" class="form-control" placeholder="Identificador URL">
        </div>
    </div>
    <button class="btn btn-primary" onclick="simulateRgpd()">Simular Cambios</button>

    <div id="previewBox" class="preview-box">
        <h3 style="margin-top:0; color: #334155;">Resultados de la Búsqueda</h3>
        <ul style="font-size: 1.1rem; color: #475569; line-height: 1.8;">
            <li>Cargos directivos encontrados: <strong id="adminCount">0</strong></li>
            <li>Publicaciones BORME encontradas: <strong id="bormeCount">0</strong></li>
        </ul>
        <p style="color: #ef4444; font-weight: 600; margin-top: 16px;">¿Confirmas que deseas anonimizar estos datos? Esta acción no se puede deshacer.</p>
        <button class="btn btn-danger" onclick="executeRgpd()">Ejecutar Supresión</button>
    </div>

    <div id="certBox" class="cert-box">
        <h3 style="margin-top:0; color: #166534;">✅ Proceso Completado</h3>
        <p style="color: #15803d; margin-bottom: 16px;">Los datos han sido anonimizados en la base de datos y la URL pública ahora devolverá un error HTTP 410 (Gone).</p>
        <label><strong>Plantilla de respuesta para el usuario:</strong></label>
        <textarea id="certText" readonly></textarea>
    </div>
</div>

<div class="rgpd-card">
    <h2 style="margin-top:0;">Historial de Opt-Outs</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Slug</th>
                <th>Fecha de Solicitud</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($optouts as $o): ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><strong><?= esc($o['slug']) ?></strong></td>
                <td><?= $o['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const csrfName = '<?= csrf_token() ?>';
    const csrfHash = '<?= csrf_hash() ?>';

    function simulateRgpd() {
        const name = document.getElementById('rgpdName').value;
        const slug = document.getElementById('rgpdSlug').value;
        
        if(!name || !slug) {
            Swal.fire('Atención', 'Por favor, rellena el nombre y el slug.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Buscando...',
            text: 'Analizando las bases de datos mercantiles y el BORME...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('name', name);
        formData.append(csrfName, csrfHash);

        fetch('<?= site_url('admin/rgpd/preview') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if(data.success) {
                document.getElementById('adminCount').innerText = data.adminCount;
                document.getElementById('bormeCount').innerText = data.bormeCount;
                document.getElementById('previewBox').style.display = 'block';
                document.getElementById('certBox').style.display = 'none';
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    }

    function executeRgpd() {
        Swal.fire({
            title: '¿Estás 100% seguro?',
            text: 'Esta acción va a anonimizar los datos y no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, ejecutar supresión',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Aplicando la anonimización en la base de datos (esto puede tardar unos segundos)...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const name = document.getElementById('rgpdName').value;
                const slug = document.getElementById('rgpdSlug').value;

                const formData = new FormData();
                formData.append('name', name);
                formData.append('slug', slug);
                formData.append(csrfName, csrfHash);

                fetch('<?= site_url('admin/rgpd/execute') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('¡Hecho!', data.message, 'success');
                        
                        document.getElementById('previewBox').style.display = 'none';
                        document.getElementById('certBox').style.display = 'block';
                        
                        const responseTemplate = `Estimado usuario,\n\nLe confirmamos que hemos procesado su solicitud de supresión de datos conforme al RGPD.\n\nSe han anonimizado sus datos en las bases de datos mercantiles y publicaciones del BORME asociadas, preservando el histórico de las empresas pero desvinculando su identidad personal.\n\nSu perfil público (https://apiempresas.es/administrador/${slug}) ha sido marcado con el código HTTP 410 (Gone) para forzar a los buscadores a eliminarlo permanentemente de sus índices.\n\nSus datos ya no son accesibles a través de nuestra API, exportaciones o herramientas de búsqueda.\n\nAtentamente,\nEl equipo de APIEmpresas`;
                        
                        document.getElementById('certText').value = responseTemplate;
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>
