<?php
/**
 * /validar-cif-excel — Tutorial: validar CIF en Excel y Google Sheets (12-10-2026).
 *
 * Búsquedas: "validar CIF Excel", "fórmula CIF Excel", "comprobar CIF Google Sheets".
 * Fórmulas generadas y probadas con 141 CIF (válidos, erróneos, con guiones, puntos,
 * minúsculas, letras de control) en LibreOffice frente a la misma lógica que /validar-cif.
 * La versión LET no se pudo probar en LibreOffice 24.2 (no tiene LET): es la misma fórmula
 * con nombres, comprobada expandiendo los nombres.
 * Lleva a la API (home), a /validar-cif, a la guía de empresa activa y a la integración de Sheets.
 */
$faqs = [
    ['¿Excel tiene una función para validar CIF?',
     'No. Excel no trae ninguna función para el CIF, pero se puede comprobar con una fórmula que calcula el dígito de control, como la de esta página. Pégala en la celda de al lado de tu CIF y arrástrala hacia abajo.'],
    ['¿La fórmula dice si la empresa existe?',
     'No. Solo comprueba que el CIF está bien formado: letra válida, siete números y un dígito de control que cuadra. Un CIF inventado puede pasar la comprobación. Para saber si la empresa existe y si sigue activa hay que consultarla, una a una en el buscador o en bloque con la API.'],
    ['¿Funciona en Excel en inglés?',
     'Sí, usa la versión en inglés de la fórmula: es la misma con los nombres de función en inglés (IF, MID, MOD…) y comas en lugar de punto y coma.'],
    ['¿Funciona en Google Sheets y en LibreOffice?',
     'En Google Sheets, sí: usa la versión para Sheets. En LibreOffice, usa la plantilla o la versión en tres columnas, que no necesita la función LET.'],
    ['¿Sirve para el DNI o el NIE?',
     'No, esta fórmula es para el CIF de empresas. Para un DNI basta con comprobar la letra: =EXTRAE("TRWAGMYFPDXBNJZSQVHLCKE";RESIDUO(IZQUIERDA(A2;8);23)+1;1)=DERECHA(A2;1) devuelve VERDADERO si la letra es correcta.'],
    ['¿Cuántas consultas gasta comprobar mi lista con la API?',
     'Una por cada empresa que consultes. Las respuestas con error (CIF mal formado o empresa que no consta) no gastan. El plan gratuito trae 100 consultas, sin tarjeta; para listas grandes, la API consulta hasta 100 CIF por petición.'],
];
$ldFaq = [
    '@context'   => 'https://schema.org',
    '@type'      => 'FAQPage',
    'mainEntity' => array_map(static fn ($f) => [
        '@type' => 'Question',
        'name'  => $f[0],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
    ], $faqs),
];
$ldArticle = [
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => 'Validar CIF en Excel y Google Sheets',
    'datePublished' => '2026-10-12',
    'dateModified'  => '2026-10-12',
    'inLanguage'    => 'es-ES',
    'mainEntityOfPage' => site_url('validar-cif-excel'),
    'author'        => ['@type' => 'Organization', 'name' => 'APIEmpresas', 'url' => site_url()],
    'publisher'     => ['@type' => 'Organization', 'name' => 'APIEmpresas', 'url' => site_url()],
];
$registro  = site_url('register?intent=api&plan=free&source=validar_cif_excel');
$plantilla = base_url('public/docs/plantilla-validar-cif.xlsx');
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Validar CIF en Excel y Google Sheets: fórmula lista para copiar | APIEmpresas',
        'excerptText' => 'Fórmula para validar CIF en Excel y Google Sheets (dígito de control), plantilla gratis y cómo comprobar desde la hoja si las empresas existen y están activas.',
        'canonical'   => site_url('validar-cif-excel'),
    ]) ?>
    <script type="application/ld+json"><?= json_encode($ldArticle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <script type="application/ld+json"><?= json_encode($ldFaq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <style>
        .cx-wrap { background: #f8fafc; padding: 56px 16px 40px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .cx { max-width: 880px; margin: 0 auto; color: #0f172a; }
        .cx h1 { font-size: clamp(1.9rem, 4.6vw, 2.8rem); font-weight: 900; letter-spacing: -0.03em; line-height: 1.12; margin: 0 0 14px; text-align: center; }
        .cx h1 span { background: linear-gradient(135deg, #2563eb 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .cx-sub { font-size: 1.12rem; color: #475569; text-align: center; max-width: 690px; margin: 0 auto 30px; line-height: 1.6; }
        .cx-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; box-shadow: 0 4px 16px -6px rgba(15, 23, 42, 0.08); margin-bottom: 28px; }
        .cx h2 { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 14px; }
        .cx h3 { font-size: 1.08rem; font-weight: 800; margin: 22px 0 8px; }
        .cx p, .cx li { color: #334155; line-height: 1.65; }
        .cx a { color: #2563eb; }
        .cx code { font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; background: #f1f5f9; padding: 1px 6px; border-radius: 5px; font-size: .9em; }
        .cx-code { position: relative; margin: 10px 0 6px; }
        .cx-code pre { margin: 0; background: #0f172a; color: #e2e8f0; border-radius: 12px; padding: 16px 18px; padding-right: 92px; font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; font-size: .82rem; line-height: 1.55; white-space: pre-wrap; word-break: break-all; max-height: 260px; overflow: auto; }
        .cx-copy { position: absolute; top: 10px; right: 10px; background: #2563eb; color: #fff; border: 0; border-radius: 8px; padding: 6px 12px; font-weight: 800; font-size: .8rem; cursor: pointer; }
        .cx-copy:hover { background: #1d4ed8; }
        .cx-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin: 6px 0 4px; }
        .cx-tab { border: 2px solid #e2e8f0; background: #fff; color: #475569; border-radius: 99px; padding: 7px 14px; font-weight: 800; font-size: .85rem; cursor: pointer; }
        .cx-tab[aria-selected="true"] { border-color: #2563eb; background: #2563eb; color: #fff; }
        .cx-panel[hidden] { display: none; }
        .cx-note { font-size: .9rem; color: #64748b; }
        .cx table { width: 100%; border-collapse: collapse; font-size: .93rem; }
        .cx th, .cx td { text-align: left; padding: 9px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .cx th { background: #f1f5f9; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        .cx-ok { color: #15803d; font-weight: 800; } .cx-ko { color: #b91c1c; font-weight: 800; } .cx-fmt { color: #b45309; font-weight: 800; }
        .cx-btn { display: inline-block; background: #2563eb; color: #fff; border: 0; border-radius: 12px; padding: 13px 24px; font-weight: 800; font-size: 1rem; cursor: pointer; text-decoration: none; }
        .cx a.cx-btn, .cx a.cx-btn:hover { color: #fff; }
        .cx-btn:hover { background: #1d4ed8; }
        .cx-btn--green { background: #059669; } .cx-btn--green:hover { background: #047857; }
        .cx details { border-bottom: 1px solid #e2e8f0; padding: 14px 0; }
        .cx summary { cursor: pointer; font-weight: 700; font-size: 1.02rem; }
        .cx details p { margin: 10px 0 0; }
        .cx-cta { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border-radius: 20px; padding: 36px 28px; text-align: center; margin-bottom: 28px; }
        .cx-cta h2 { color: #fff; }
        .cx-cta p { color: #cbd5e1; max-width: 620px; margin: 0 auto 22px; }
        .cx-cta .cx-links { margin-top: 16px; font-size: .92rem; }
        .cx-cta .cx-links a { color: #93c5fd; font-weight: 700; }
        @media (max-width: 640px) { .cx-card { padding: 20px; } .cx-code pre { font-size: .75rem; } }
    </style>
</head>
<body>
<?= view('partials/header') ?>

<main class="cx-wrap">
<div class="cx">

    <h1>Validar CIF en Excel <span>y Google Sheets</span></h1>
    <p class="cx-sub">Una fórmula lista para copiar que comprueba si cada CIF de tu hoja está bien formado, una plantilla gratis y cómo saber desde la propia hoja si las empresas existen y siguen activas.</p>

    <!-- 1. Fórmula -->
    <section class="cx-card">
        <h2>La fórmula para validar un CIF</h2>
        <p>Con el CIF en la celda <code>A2</code>, pega esta fórmula en <code>B2</code> y arrástrala hacia abajo. Devuelve <span class="cx-ok">VÁLIDO</span>, <span class="cx-ko">NO VÁLIDO</span> (el dígito de control no cuadra) o <span class="cx-fmt">FORMATO INCORRECTO</span>. Acepta minúsculas, espacios, guiones y puntos.</p>

        <div class="cx-tabs" role="tablist">
            <button class="cx-tab" role="tab" aria-selected="true" data-panel="p-es">Excel en español</button>
            <button class="cx-tab" role="tab" aria-selected="false" data-panel="p-en">Excel en inglés</button>
            <button class="cx-tab" role="tab" aria-selected="false" data-panel="p-gs">Google Sheets</button>
        </div>
        <div class="cx-panel" id="p-es" role="tabpanel">
            <div class="cx-code"><pre id="f-es">=SI(A2="";"";SI.ERROR(LET(codigo;MAYUSC(SUSTITUIR(SUSTITUIR(SUSTITUIR(ESPACIOS(A2);" ";"");"-";"");".";""));control;RESIDUO(10-RESIDUO(EXTRAE(codigo;3;1)+EXTRAE(codigo;5;1)+EXTRAE(codigo;7;1)+EXTRAE("0246813579";EXTRAE(codigo;2;1)+1;1)+EXTRAE("0246813579";EXTRAE(codigo;4;1)+1;1)+EXTRAE("0246813579";EXTRAE(codigo;6;1)+1;1)+EXTRAE("0246813579";EXTRAE(codigo;8;1)+1;1);10);10);SI(Y(LARGO(codigo)=9;ESNUMERO(ENCONTRAR(IZQUIERDA(codigo;1);"ABCDEFGHJNPQRSUVW"));ESNUMERO(--EXTRAE(codigo;2;7)));SI(SI(ESNUMERO(ENCONTRAR(IZQUIERDA(codigo;1);"ABEH"));DERECHA(codigo;1)=control&amp;"";SI(ESNUMERO(ENCONTRAR(IZQUIERDA(codigo;1);"NPQRSW"));DERECHA(codigo;1)=EXTRAE("JABCDEFGHI";control+1;1);O(DERECHA(codigo;1)=control&amp;"";DERECHA(codigo;1)=EXTRAE("JABCDEFGHI";control+1;1))));"VÁLIDO";"NO VÁLIDO");"FORMATO INCORRECTO"));"FORMATO INCORRECTO"))</pre><button class="cx-copy" data-copy="f-es" data-label="excel_es">Copiar</button></div>
            <p class="cx-note">Para Excel 365 y Excel 2021 o posterior (usa la función LET). Para versiones anteriores, mira la versión en tres columnas más abajo.</p>
        </div>
        <div class="cx-panel" id="p-en" role="tabpanel" hidden>
            <div class="cx-code"><pre id="f-en">=IF(A2="","",IFERROR(LET(codigo,UPPER(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(TRIM(A2)," ",""),"-",""),".","")),control,MOD(10-MOD(MID(codigo,3,1)+MID(codigo,5,1)+MID(codigo,7,1)+MID("0246813579",MID(codigo,2,1)+1,1)+MID("0246813579",MID(codigo,4,1)+1,1)+MID("0246813579",MID(codigo,6,1)+1,1)+MID("0246813579",MID(codigo,8,1)+1,1),10),10),IF(AND(LEN(codigo)=9,ISNUMBER(FIND(LEFT(codigo,1),"ABCDEFGHJNPQRSUVW")),ISNUMBER(--MID(codigo,2,7))),IF(IF(ISNUMBER(FIND(LEFT(codigo,1),"ABEH")),RIGHT(codigo,1)=control&amp;"",IF(ISNUMBER(FIND(LEFT(codigo,1),"NPQRSW")),RIGHT(codigo,1)=MID("JABCDEFGHI",control+1,1),OR(RIGHT(codigo,1)=control&amp;"",RIGHT(codigo,1)=MID("JABCDEFGHI",control+1,1)))),"VÁLIDO","NO VÁLIDO"),"FORMATO INCORRECTO")),"FORMATO INCORRECTO"))</pre><button class="cx-copy" data-copy="f-en" data-label="excel_en">Copy</button></div>
            <p class="cx-note">Excel 365 y Excel 2021 o posterior con el idioma en inglés.</p>
        </div>
        <div class="cx-panel" id="p-gs" role="tabpanel" hidden>
            <div class="cx-code"><pre id="f-gs">=IF(A2="";"";IFERROR(LET(codigo;UPPER(SUBSTITUTE(SUBSTITUTE(SUBSTITUTE(TRIM(A2);" ";"");"-";"");".";""));control;MOD(10-MOD(MID(codigo;3;1)+MID(codigo;5;1)+MID(codigo;7;1)+MID("0246813579";MID(codigo;2;1)+1;1)+MID("0246813579";MID(codigo;4;1)+1;1)+MID("0246813579";MID(codigo;6;1)+1;1)+MID("0246813579";MID(codigo;8;1)+1;1);10);10);IF(AND(LEN(codigo)=9;ISNUMBER(FIND(LEFT(codigo;1);"ABCDEFGHJNPQRSUVW"));ISNUMBER(--MID(codigo;2;7)));IF(IF(ISNUMBER(FIND(LEFT(codigo;1);"ABEH"));RIGHT(codigo;1)=control&amp;"";IF(ISNUMBER(FIND(LEFT(codigo;1);"NPQRSW"));RIGHT(codigo;1)=MID("JABCDEFGHI";control+1;1);OR(RIGHT(codigo;1)=control&amp;"";RIGHT(codigo;1)=MID("JABCDEFGHI";control+1;1))));"VÁLIDO";"NO VÁLIDO");"FORMATO INCORRECTO"));"FORMATO INCORRECTO"))</pre><button class="cx-copy" data-copy="f-gs" data-label="sheets">Copiar</button></div>
            <p class="cx-note">Google Sheets acepta los nombres de función en inglés en cualquier idioma. Esta versión usa punto y coma, como las hojas con la configuración regional de España; si tu hoja usa comas para separar argumentos, cámbialos.</p>
        </div>

        <h3>Ejemplos</h3>
        <table>
            <thead><tr><th>CIF en A2</th><th>Resultado</th><th>Por qué</th></tr></thead>
            <tbody>
                <tr><td><code>A46103834</code></td><td class="cx-ok">VÁLIDO</td><td>El dígito de control (4) cuadra.</td></tr>
                <tr><td><code>a-46.103.834</code></td><td class="cx-ok">VÁLIDO</td><td>Se quitan guiones y puntos y se pasa a mayúsculas.</td></tr>
                <tr><td><code>Q2826000H</code></td><td class="cx-ok">VÁLIDO</td><td>Organismo público: el control va en letra.</td></tr>
                <tr><td><code>A46103835</code></td><td class="cx-ko">NO VÁLIDO</td><td>El control debería ser 4.</td></tr>
                <tr><td><code>B1234567</code></td><td class="cx-fmt">FORMATO INCORRECTO</td><td>Le falta un carácter.</td></tr>
                <tr><td><code>12345678Z</code></td><td class="cx-fmt">FORMATO INCORRECTO</td><td>Es un DNI, no un CIF.</td></tr>
            </tbody>
        </table>
    </section>

    <!-- 2. Plantilla -->
    <section class="cx-card">
        <h2>Plantilla de Excel para validar CIF (gratis)</h2>
        <p>Si prefieres no tocar fórmulas, descarga la plantilla: pega tus CIF en la columna A y la columna B te dice si son válidos. Viene preparada para 1.000 filas y funciona en cualquier versión de Excel, en LibreOffice y al subirla a Google Sheets.</p>
        <a class="cx-btn cx-btn--green" href="<?= $plantilla ?>" download data-track-click="cif_excel" data-track-element="plantilla">Descargar plantilla (.xlsx)</a>
    </section>

    <!-- 3. Versión en tres columnas -->
    <section class="cx-card">
        <h2>Versión para Excel 2019 o anterior (sin LET)</h2>
        <p>Si tu Excel no tiene la función LET, reparte el cálculo en tres columnas, con el CIF en <code>A2</code>:</p>
        <p style="margin-bottom:4px;"><strong>B2</strong>, el CIF limpio:</p>
        <div class="cx-code"><pre id="f-hb">=MAYUSC(SUSTITUIR(SUSTITUIR(SUSTITUIR(ESPACIOS(A2);" ";"");"-";"");".";""))</pre><button class="cx-copy" data-copy="f-hb" data-label="old_b">Copiar</button></div>
        <p style="margin-bottom:4px;"><strong>C2</strong>, el dígito de control que le corresponde:</p>
        <div class="cx-code"><pre id="f-hc">=SI.ERROR(RESIDUO(10-RESIDUO(EXTRAE(B2;3;1)+EXTRAE(B2;5;1)+EXTRAE(B2;7;1)+EXTRAE("0246813579";EXTRAE(B2;2;1)+1;1)+EXTRAE("0246813579";EXTRAE(B2;4;1)+1;1)+EXTRAE("0246813579";EXTRAE(B2;6;1)+1;1)+EXTRAE("0246813579";EXTRAE(B2;8;1)+1;1);10);10);"")</pre><button class="cx-copy" data-copy="f-hc" data-label="old_c">Copiar</button></div>
        <p style="margin-bottom:4px;"><strong>D2</strong>, el resultado:</p>
        <div class="cx-code"><pre id="f-hd">=SI(A2="";"";SI.ERROR(SI(Y(LARGO(B2)=9;ESNUMERO(ENCONTRAR(IZQUIERDA(B2;1);"ABCDEFGHJNPQRSUVW"));ESNUMERO(--EXTRAE(B2;2;7)));SI(SI(ESNUMERO(ENCONTRAR(IZQUIERDA(B2;1);"ABEH"));DERECHA(B2;1)=C2&amp;"";SI(ESNUMERO(ENCONTRAR(IZQUIERDA(B2;1);"NPQRSW"));DERECHA(B2;1)=EXTRAE("JABCDEFGHI";C2+1;1);O(DERECHA(B2;1)=C2&amp;"";DERECHA(B2;1)=EXTRAE("JABCDEFGHI";C2+1;1))));"VÁLIDO";"NO VÁLIDO");"FORMATO INCORRECTO");"FORMATO INCORRECTO"))</pre><button class="cx-copy" data-copy="f-hd" data-label="old_d">Copiar</button></div>
        <p class="cx-note">Selecciona B2:D2 y arrastra hacia abajo. Es la misma lógica que la plantilla.</p>
    </section>

    <!-- 4. Cómo funciona -->
    <section class="cx-card">
        <h2>Qué comprueba la fórmula</h2>
        <ol>
            <li>Que el CIF tiene 9 caracteres: una letra de forma jurídica (A, B, C, D, E, F, G, H, J, N, P, Q, R, S, U, V o W), siete números y un carácter de control.</li>
            <li>Calcula el control: suma los números en posición par, suma las cifras del doble de cada número en posición impar, y resta de 10 la última cifra del total.</li>
            <li>Compara con el último carácter. Las sociedades anónimas y limitadas (A, B) y las comunidades (E, H) llevan número; los organismos públicos y las entidades extranjeras o religiosas (N, P, Q, R, S, W), letra (<code>JABCDEFGHI</code>); el resto puede llevar cualquiera de los dos.</li>
        </ol>
        <p>Lo que <strong>no</strong> comprueba: que la empresa exista, que esté activa ni que el nombre que tienes en la hoja sea el suyo. Un CIF inventado puede salir VÁLIDO. Para comprobar uno concreto, usa el <a href="<?= site_url('validar-cif') ?>">validador de CIF</a> o mira <a href="<?= site_url('como-saber-si-una-empresa-esta-activa') ?>">cómo saber si una empresa está activa</a>.</p>
    </section>

    <!-- 5. Desde la hoja: existe y está activa -->
    <section class="cx-card">
        <h2>Comprobar desde la hoja si las empresas existen y están activas</h2>
        <p>Para eso hace falta consultar los datos de cada empresa. Con la <a href="<?= site_url() ?>">API de empresas</a> puedes hacerlo sin salir de tu hoja. Cada empresa consultada gasta una consulta; las respuestas con error no gastan. El plan gratuito trae 100, sin tarjeta.</p>

        <h3>En Google Sheets</h3>
        <p>Instala la función <code>APIEMPRESAS</code> siguiendo la <a href="<?= site_url('integraciones/google-sheets') ?>">guía de la integración con Google Sheets</a> (es un script que se pega en Extensiones &gt; Apps Script). Después, con el CIF en <code>A2</code> y el resultado de la fórmula de arriba en <code>B2</code>:</p>
        <div class="cx-code"><pre id="f-gs-api">=SI(B2="VÁLIDO"; APIEMPRESAS(A2; "status"); "")</pre><button class="cx-copy" data-copy="f-gs-api" data-label="sheets_api">Copiar</button></div>
        <p class="cx-note">Solo consulta los CIF bien formados, así no gastas consultas en los erróneos. Cambia <code>"status"</code> por <code>"name"</code>, <code>"province"</code> o <code>"cnae_label"</code> para traer la razón social, la provincia o la actividad.</p>

        <h3>En Excel, con Power Query</h3>
        <p>Convierte tu lista en una tabla llamada <code>CIFs</code> con una columna <code>CIF</code>. Ve a Datos &gt; Obtener datos &gt; De otras fuentes &gt; Consulta en blanco, abre el Editor avanzado y pega esto con tu API key:</p>
        <div class="cx-code"><pre id="f-pq">let
    ApiKey = "TU_API_KEY",
    Origen = Excel.CurrentWorkbook(){[Name = "CIFs"]}[Content],
    Consultar = (cif as text) as record =&gt;
        let
            Respuesta = Function.InvokeAfter(
                () =&gt; Web.Contents(
                    "https://apiempresas.es/api/v1/companies",
                    [
                        Query = [cif = cif],
                        Headers = [#"X-API-KEY" = ApiKey],
                        ManualStatusHandling = {400, 404, 429}
                    ]
                ),
                #duration(0, 0, 0, 0.5)
            ),
            Codigo = Value.Metadata(Respuesta)[Response.Status],
            Datos = if Codigo = 200 then Json.Document(Respuesta)[data] else null
        in
            [
                #"Razón social" = if Datos = null then null else Datos[name],
                Estado = if Codigo = 200 then Datos[status]
                         else if Codigo = 404 then "NO CONSTA"
                         else if Codigo = 400 then "CIF NO VÁLIDO"
                         else "REINTENTAR",
                Provincia = if Datos = null then null else Datos[province]
            ],
    ConDatos = Table.AddColumn(Origen, "Empresa", each Consultar(Text.From([CIF]))),
    Resultado = Table.ExpandRecordColumn(ConDatos, "Empresa", {"Razón social", "Estado", "Provincia"})
in
    Resultado</pre><button class="cx-copy" data-copy="f-pq" data-label="power_query">Copiar</button></div>
        <p class="cx-note">Si Excel pregunta por el acceso a la web, elige «Anónimo». La consulta espera medio segundo entre empresas para respetar el límite del plan gratuito (2 peticiones por segundo). Al pulsar Actualizar se vuelven a consultar todas.</p>
    </section>

    <section class="cx-cta">
        <h2>¿Listas de miles de CIF?</h2>
        <p>La API consulta hasta 100 CIF por petición y te devuelve razón social, estado, provincia, CNAE y, en Pro, administradores y domicilio. Empiezas con 100 consultas gratis, sin tarjeta.</p>
        <a class="cx-btn" href="<?= $registro ?>" data-track-click="cif_excel" data-track-element="cta_api">Obtener API key gratis</a>
        <div class="cx-links"><a href="<?= site_url() ?>">API de empresas</a> · <a href="<?= site_url('documentation') ?>">Documentación</a> · <a href="<?= site_url('validar-cif-php-python') ?>">Validar CIF en PHP, Python o JavaScript</a></div>
    </section>

    <section class="cx-card">
        <h2>Preguntas frecuentes</h2>
        <?php foreach ($faqs as [$q, $a]): ?>
            <details><summary><?= esc($q) ?></summary><p><?= esc($a) ?></p></details>
        <?php endforeach; ?>
    </section>

</div>
</main>

<script>
(function () {
    'use strict';
    function track(d) { try { if (window.trackEvent) window.trackEvent('cif_excel_copy', d); } catch (e) {} }
    document.querySelectorAll('.cx-tab').forEach(function (t) {
        t.addEventListener('click', function () {
            document.querySelectorAll('.cx-tab').forEach(function (o) {
                var on = o === t;
                o.setAttribute('aria-selected', on ? 'true' : 'false');
                document.getElementById(o.getAttribute('data-panel')).hidden = !on;
            });
        });
    });
    document.querySelectorAll('.cx-copy').forEach(function (b) {
        b.addEventListener('click', function () {
            var txt = document.getElementById(b.getAttribute('data-copy')).textContent;
            var label = b.textContent;
            var done = function () { b.textContent = label === 'Copy' ? 'Copied' : 'Copiado'; setTimeout(function () { b.textContent = label; }, 1800); };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(txt).then(done).catch(function () {});
            } else {
                var ta = document.createElement('textarea'); ta.value = txt; document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(ta);
            }
            track({ formula: b.getAttribute('data-label') });
        });
    });
})();
</script>

<?= view('partials/footer') ?>
</body>
</html>
