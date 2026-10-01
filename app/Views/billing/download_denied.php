<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Descarga no disponible | APIEmpresas</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#f8fafc; --card:#ffffff; --ink:#0f172a; --muted:#475569; --line:#e2e8f0; --brand:#2152FF; --ok:#10b981; --warn-bg:#fff7ed; --warn:#c2410c; }
        * { box-sizing: border-box; }
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:16px; background:var(--bg); color:var(--ink); font-family:Inter, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
        .card { width:100%; max-width:520px; background:var(--card); border:1px solid var(--line); border-radius:16px; padding:32px 28px; box-shadow:0 10px 30px rgba(15,23,42,.06); text-align:center; }
        .icon { width:56px; height:56px; border-radius:14px; background:var(--warn-bg); color:var(--warn); display:inline-flex; align-items:center; justify-content:center; margin-bottom:18px; }
        h1 { font-size:1.35rem; font-weight:800; margin:0 0 10px; }
        p { color:var(--muted); line-height:1.6; margin:0 0 12px; font-size:.95rem; }
        .actions { display:flex; flex-direction:column; gap:10px; margin-top:22px; }
        .btn { display:block; padding:13px 18px; border-radius:12px; font-weight:800; text-decoration:none; font-size:.95rem; }
        .btn-primary { background:var(--ok); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1.5px solid #cbd5e1; }
        a.mail { color:var(--brand); font-weight:600; }
    </style>
</head>
<body>
    <main class="card">
        <div class="icon" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        </div>
        <h1>Este enlace de descarga no es válido</h1>
        <p>Las descargas de listados solo se abren desde la página de confirmación de una compra, y el enlace caduca a los 7 días.</p>
        <p>Si has pagado y no puedes descargar tu listado, escríbenos a <a class="mail" href="mailto:soporte@apiempresas.es">soporte@apiempresas.es</a> y te lo enviamos.</p>
        <div class="actions">
            <a class="btn btn-primary" href="<?= site_url('base-de-datos-de-empresas') ?>">Comprar un listado</a>
            <a class="btn btn-secondary" href="<?= site_url('listado-de-empresas') ?>">Ver el listado de empresas</a>
        </div>
    </main>
</body>
</html>
