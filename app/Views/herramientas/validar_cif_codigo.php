<?php
/**
 * /validar-cif-php-python — Tutorial para desarrolladores (12-10-2026).
 *
 * Búsquedas: "validar CIF PHP", "validar CIF Python", "validar CIF JavaScript", "validar CIF Laravel".
 * Las tres funciones se probaron con 426 CIF (válidos, erróneos, con guiones, puntos, minúsculas,
 * control con letra) frente a la misma lógica de /validar-cif: 0 diferencias. Los ejemplos de SDK
 * siguen los README de sdks/php, sdks/python y sdks/node (errorCode = campo "error" de la respuesta).
 */
$faqs = [
    ['¿Hay una librería para validar CIF en PHP o Python?',
     'No hace falta: la comprobación del dígito de control son veinte líneas, como las de esta página, sin dependencias. Una librería o una API solo tiene sentido si además quieres saber si la empresa existe, su razón social o su estado.'],
    ['¿La función valida también el DNI y el NIE?',
     'No. Es para el CIF de empresas (letra, siete números y control). Para un DNI basta con comprobar la letra: el resto de dividir el número entre 23 indica la posición en la cadena TRWAGMYFPDXBNJZSQVHLCKE. En el NIE, la X, Y o Z inicial se cambia por 0, 1 o 2 antes de calcular.'],
    ['¿Debo validar en el navegador o en el servidor?',
     'En los dos. En el navegador, para avisar al usuario mientras escribe; en el servidor, porque es lo único que no se puede saltar. La función de JavaScript sirve para lo primero y la de PHP o Python para lo segundo.'],
    ['¿Un CIF válido significa que la empresa existe?',
     'No. Solo significa que el número está bien formado. Para saber si hay una sociedad con ese CIF, cómo se llama y si sigue activa hay que consultarla, por ejemplo con la API de empresas.'],
    ['¿Cuánto cuesta consultar la empresa con la API?',
     'El plan gratuito trae 100 consultas, sin tarjeta. Las respuestas con error (CIF mal formado o empresa que no consta) no gastan consultas. El plan Pro cuesta 19 € al mes más IVA por 3.000 consultas.'],
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
    '@type'         => 'TechArticle',
    'headline'      => 'Validar CIF en PHP, Python y JavaScript',
    'datePublished' => '2026-10-12',
    'dateModified'  => '2026-10-12',
    'inLanguage'    => 'es-ES',
    'proficiencyLevel' => 'Beginner',
    'mainEntityOfPage' => site_url('validar-cif-php-python'),
    'author'        => ['@type' => 'Organization', 'name' => 'APIEmpresas', 'url' => site_url()],
    'publisher'     => ['@type' => 'Organization', 'name' => 'APIEmpresas', 'url' => site_url()],
];
$registro = site_url('register?intent=api&plan=free&source=validar_cif_codigo');
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Validar CIF en PHP, Python y JavaScript: función lista para copiar | APIEmpresas',
        'excerptText' => 'Función para validar el CIF (dígito de control) en PHP, Python y JavaScript, probada con más de 400 casos, regla para Laravel y cómo comprobar con la API si la empresa existe.',
        'canonical'   => site_url('validar-cif-php-python'),
    ]) ?>
    <script type="application/ld+json"><?= json_encode($ldArticle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <script type="application/ld+json"><?= json_encode($ldFaq, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <style>
        .dv-wrap { background: #f8fafc; padding: 56px 16px 40px; font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .dv { max-width: 880px; margin: 0 auto; color: #0f172a; }
        .dv h1 { font-size: clamp(1.9rem, 4.6vw, 2.8rem); font-weight: 900; letter-spacing: -0.03em; line-height: 1.12; margin: 0 0 14px; text-align: center; }
        .dv h1 span { background: linear-gradient(135deg, #2563eb 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dv-sub { font-size: 1.12rem; color: #475569; text-align: center; max-width: 690px; margin: 0 auto 30px; line-height: 1.6; }
        .dv-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 28px; box-shadow: 0 4px 16px -6px rgba(15, 23, 42, 0.08); margin-bottom: 28px; }
        .dv h2 { font-size: 1.5rem; font-weight: 900; letter-spacing: -0.02em; margin: 0 0 14px; }
        .dv h3 { font-size: 1.08rem; font-weight: 800; margin: 22px 0 8px; }
        .dv p, .dv li { color: #334155; line-height: 1.65; }
        .dv a { color: #2563eb; }
        .dv code { font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; background: #f1f5f9; padding: 1px 6px; border-radius: 5px; font-size: .9em; }
        .dv-code { position: relative; margin: 10px 0 6px; }
        .dv-code pre { margin: 0; background: #0f172a; color: #e2e8f0; border-radius: 12px; padding: 16px 18px; padding-right: 92px; font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; font-size: .82rem; line-height: 1.6; white-space: pre; overflow-x: auto; }
        .dv-copy { position: absolute; top: 10px; right: 10px; background: #2563eb; color: #fff; border: 0; border-radius: 8px; padding: 6px 12px; font-weight: 800; font-size: .8rem; cursor: pointer; }
        .dv-copy:hover { background: #1d4ed8; }
        .dv-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin: 6px 0 4px; }
        .dv-tab { border: 2px solid #e2e8f0; background: #fff; color: #475569; border-radius: 99px; padding: 7px 14px; font-weight: 800; font-size: .85rem; cursor: pointer; }
        .dv-tab[aria-selected="true"] { border-color: #2563eb; background: #2563eb; color: #fff; }
        .dv-panel[hidden] { display: none; }
        .dv-note { font-size: .9rem; color: #64748b; }
        .dv table { width: 100%; border-collapse: collapse; font-size: .93rem; }
        .dv th, .dv td { text-align: left; padding: 9px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .dv th { background: #f1f5f9; font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: #475569; }
        .dv-t { color: #15803d; font-weight: 800; } .dv-f { color: #b91c1c; font-weight: 800; }
        .dv details { border-bottom: 1px solid #e2e8f0; padding: 14px 0; }
        .dv summary { cursor: pointer; font-weight: 700; font-size: 1.02rem; }
        .dv details p { margin: 10px 0 0; }
        .dv-btn { display: inline-block; background: #2563eb; color: #fff; border: 0; border-radius: 12px; padding: 13px 24px; font-weight: 800; font-size: 1rem; text-decoration: none; }
        .dv a.dv-btn, .dv a.dv-btn:hover { color: #fff; }
        .dv-cta { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; border-radius: 20px; padding: 36px 28px; text-align: center; margin-bottom: 28px; }
        .dv-cta h2 { color: #fff; }
        .dv-cta p { color: #cbd5e1; max-width: 620px; margin: 0 auto 22px; }
        .dv-cta .dv-links { margin-top: 16px; font-size: .92rem; }
        .dv-cta .dv-links a { color: #93c5fd; font-weight: 700; }
        @media (max-width: 640px) { .dv-card { padding: 20px; } .dv-code pre { font-size: .74rem; } }
    </style>
</head>
<body>
<?= view('partials/header') ?>

<main class="dv-wrap">
<div class="dv">

    <h1>Validar CIF en PHP, Python <span>y JavaScript</span></h1>
    <p class="dv-sub">La función que comprueba el dígito de control de un CIF, lista para copiar en tu proyecto, y cómo saber con una llamada a la API si la empresa existe y sigue activa.</p>

    <!-- 1. Función -->
    <section class="dv-card">
        <h2>La función para validar un CIF</h2>
        <p>Devuelve <code>true</code> si el CIF está bien formado: letra de forma jurídica válida, siete números y un carácter de control que cuadra. Acepta minúsculas, espacios, guiones y puntos. Sin dependencias.</p>
        <div class="dv-tabs" role="tablist" data-group="fn">
            <button class="dv-tab" role="tab" aria-selected="true" data-panel="fn-php">PHP</button>
            <button class="dv-tab" role="tab" aria-selected="false" data-panel="fn-py">Python</button>
            <button class="dv-tab" role="tab" aria-selected="false" data-panel="fn-js">JavaScript</button>
        </div>
        <div class="dv-panel" id="fn-php" role="tabpanel"><div class="dv-code"><pre id="c-fn-php">&lt;?php
/**
 * Comprueba si un CIF español está bien formado (letra, 7 números y control).
 * No dice si la empresa existe: para eso hay que consultarla.
 */
function validarCif(string $cif): bool
{
    $cif = strtoupper(preg_replace('/[\s.\-]/', '', $cif));
    if (!preg_match('/^([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])$/', $cif, $m)) {
        return false;
    }
    [, $letra, $numeros, $control] = $m;

    $suma = 0;
    for ($i = 0; $i &lt; 7; $i++) {
        $n = (int) $numeros[$i];
        if ($i % 2 === 0) {          // posiciones impares: se dobla y se suman sus cifras
            $n *= 2;
            $n = $n &gt; 9 ? $n - 9 : $n;
        }
        $suma += $n;
    }
    $digito = (10 - $suma % 10) % 10;
    $letraControl = 'JABCDEFGHI'[$digito];

    if (strpos('ABEH', $letra) !== false) {
        return $control === (string) $digito;          // control numérico
    }
    if (strpos('NPQRSW', $letra) !== false) {
        return $control === $letraControl;             // control con letra
    }
    return $control === (string) $digito || $control === $letraControl;
}</pre><button class="dv-copy" data-copy="c-fn-php" data-label="fn_php">Copiar</button></div><p class="dv-note">PHP 7.4 o superior.</p></div>
        <div class="dv-panel" id="fn-py" role="tabpanel" hidden><div class="dv-code"><pre id="c-fn-py">import re

def validar_cif(cif: str) -&gt; bool:
    """Comprueba si un CIF español está bien formado (letra, 7 números y control).
    No dice si la empresa existe: para eso hay que consultarla."""
    cif = re.sub(r'[\s.\-]', '', cif or '').upper()
    m = re.fullmatch(r'([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])', cif)
    if not m:
        return False
    letra, numeros, control = m.groups()

    suma = 0
    for i, c in enumerate(numeros):
        n = int(c)
        if i % 2 == 0:               # posiciones impares: se dobla y se suman sus cifras
            n *= 2
            n = n - 9 if n &gt; 9 else n
        suma += n
    digito = (10 - suma % 10) % 10
    letra_control = 'JABCDEFGHI'[digito]

    if letra in 'ABEH':
        return control == str(digito)          # control numérico
    if letra in 'NPQRSW':
        return control == letra_control        # control con letra
    return control in (str(digito), letra_control)</pre><button class="dv-copy" data-copy="c-fn-py" data-label="fn_py">Copiar</button></div><p class="dv-note">Python 3.7 o superior.</p></div>
        <div class="dv-panel" id="fn-js" role="tabpanel" hidden><div class="dv-code"><pre id="c-fn-js">/**
 * Comprueba si un CIF español está bien formado (letra, 7 números y control).
 * No dice si la empresa existe: para eso hay que consultarla.
 */
function validarCif(cif) {
  cif = String(cif || '').replace(/[\s.\-]/g, '').toUpperCase();
  const m = cif.match(/^([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])$/);
  if (!m) return false;
  const [, letra, numeros, control] = m;

  let suma = 0;
  for (let i = 0; i &lt; 7; i++) {
    let n = Number(numeros[i]);
    if (i % 2 === 0) {               // posiciones impares: se dobla y se suman sus cifras
      n *= 2;
      if (n &gt; 9) n -= 9;
    }
    suma += n;
  }
  const digito = (10 - (suma % 10)) % 10;
  const letraControl = 'JABCDEFGHI'[digito];

  if ('ABEH'.includes(letra)) return control === String(digito);     // control numérico
  if ('NPQRSW'.includes(letra)) return control === letraControl;     // control con letra
  return control === String(digito) || control === letraControl;
}</pre><button class="dv-copy" data-copy="c-fn-js" data-label="fn_js">Copiar</button></div><p class="dv-note">Navegador o Node.js.</p></div>

        <h3>Casos de prueba</h3>
        <p style="margin-top:0;">Las tres versiones se han probado con más de 400 CIF y dan el mismo resultado que el <a href="<?= site_url('validar-cif') ?>">validador de CIF</a> de esta web. Puedes usar estos para tus tests:</p>
        <table>
            <thead><tr><th>Entrada</th><th>Resultado</th><th>Motivo</th></tr></thead>
            <tbody>
                <tr><td><code>A46103834</code></td><td class="dv-t">true</td><td>Control numérico correcto.</td></tr>
                <tr><td><code>a-46.103.834</code></td><td class="dv-t">true</td><td>Se limpian guiones, puntos y minúsculas.</td></tr>
                <tr><td><code>Q2826000H</code></td><td class="dv-t">true</td><td>Organismo público: control con letra.</td></tr>
                <tr><td><code>A46103835</code></td><td class="dv-f">false</td><td>El control debería ser 4.</td></tr>
                <tr><td><code>Q2826000I</code></td><td class="dv-f">false</td><td>La letra de control debería ser H.</td></tr>
                <tr><td><code>12345678Z</code></td><td class="dv-f">false</td><td>Es un DNI, no un CIF.</td></tr>
            </tbody>
        </table>
    </section>

    <!-- 2. Formularios -->
    <section class="dv-card">
        <h2>En un formulario y en Laravel</h2>
        <p>Valida en el navegador para avisar al usuario mientras escribe, y siempre otra vez en el servidor.</p>
        <h3>Aviso en el formulario (JavaScript)</h3>
        <div class="dv-code"><pre id="c-form">&lt;input id="cif" name="cif" autocomplete="off"&gt;
&lt;small id="cif-aviso"&gt;&lt;/small&gt;
&lt;script&gt;
  // validarCif(): la función de JavaScript de arriba
  document.getElementById('cif').addEventListener('blur', function () {
    const ok = validarCif(this.value);
    document.getElementById('cif-aviso').textContent =
      this.value &amp;&amp; !ok ? 'Revisa el CIF: el dígito de control no cuadra.' : '';
  });
&lt;/script&gt;</pre><button class="dv-copy" data-copy="c-form" data-label="form">Copiar</button></div>
        <h3>Regla de validación en Laravel</h3>
        <div class="dv-code"><pre id="c-laravel">// En el controlador o en un FormRequest de Laravel
$request-&gt;validate([
    'cif' =&gt; ['required', function (string $attribute, mixed $value, \Closure $fail) {
        if (!validarCif((string) $value)) {
            $fail('El CIF no es válido.');
        }
    }],
]);</pre><button class="dv-copy" data-copy="c-laravel" data-label="laravel">Copiar</button></div>
        <p class="dv-note">Pon la función <code>validarCif()</code> de PHP en un helper cargado por Composer o en una clase de regla propia.</p>
    </section>

    <!-- 3. API -->
    <section class="dv-card">
        <h2>Comprobar si la empresa existe y sigue activa</h2>
        <p>El dígito de control no dice si hay una sociedad con ese CIF: un número inventado puede cuadrar. Para saberlo, valida primero el formato (así no gastas consultas en CIF mal escritos) y después consulta la empresa con la <a href="<?= site_url() ?>">API de empresas</a>, que devuelve su razón social, su estado y el resto de datos del Registro Mercantil y el BORME.</p>
        <div class="dv-tabs" role="tablist" data-group="sdk">
            <button class="dv-tab" role="tab" aria-selected="true" data-panel="sdk-php">PHP</button>
            <button class="dv-tab" role="tab" aria-selected="false" data-panel="sdk-py">Python</button>
            <button class="dv-tab" role="tab" aria-selected="false" data-panel="sdk-js">Node.js</button>
            <button class="dv-tab" role="tab" aria-selected="false" data-panel="sdk-curl">curl</button>
        </div>
        <div class="dv-panel" id="sdk-php" role="tabpanel"><div class="dv-code"><pre id="c-sdk-php">&lt;?php
// composer require apiempresas/apiempresas-php
require 'vendor/autoload.php';

use ApiEmpresas\ApiEmpresas;
use ApiEmpresas\Exceptions\ApiException;

$api = new ApiEmpresas('TU_API_KEY');
$cif = 'A46103834';

if (!validarCif($cif)) {
    exit('El CIF no es válido: pide al cliente que lo revise.');
}

try {
    $empresa = $api-&gt;companies-&gt;get($cif);
    echo $empresa['name'] . ' · ' . $empresa['status'];   // MERCADONA SA · ACTIVA
} catch (ApiException $e) {
    if ($e-&gt;getErrorCode() === 'COMPANY_NOT_FOUND') {
        echo 'No consta ninguna sociedad con ese CIF.';
    } else {
        echo 'Error de la API: ' . $e-&gt;getMessage();
    }
}</pre><button class="dv-copy" data-copy="c-sdk-php" data-label="sdk_php">Copiar</button></div></div>
        <div class="dv-panel" id="sdk-py" role="tabpanel" hidden><div class="dv-code"><pre id="c-sdk-py"># pip install apiempresas
from apiempresas import ApiEmpresas, ApiError

api = ApiEmpresas('TU_API_KEY')
cif = 'A46103834'

if not validar_cif(cif):
    raise SystemExit('El CIF no es válido: pide al cliente que lo revise.')

try:
    empresa = api.companies.get(cif)
    print(empresa['name'], '·', empresa['status'])   # MERCADONA SA · ACTIVA
except ApiError as e:
    if e.error_code == 'COMPANY_NOT_FOUND':
        print('No consta ninguna sociedad con ese CIF.')
    else:
        print('Error de la API:', e)</pre><button class="dv-copy" data-copy="c-sdk-py" data-label="sdk_py">Copiar</button></div></div>
        <div class="dv-panel" id="sdk-js" role="tabpanel" hidden><div class="dv-code"><pre id="c-sdk-js">// npm install apiempresas
import { ApiEmpresas } from 'apiempresas';

const api = new ApiEmpresas({ apiKey: 'TU_API_KEY' });
const cif = 'A46103834';

if (!validarCif(cif)) {
  throw new Error('El CIF no es válido: pide al cliente que lo revise.');
}

try {
  const empresa = await api.companies.get(cif);
  console.log(`${empresa.name} · ${empresa.status}`);   // MERCADONA SA · ACTIVA
} catch (err) {
  if (err.errorCode === 'COMPANY_NOT_FOUND') {
    console.log('No consta ninguna sociedad con ese CIF.');
  } else {
    console.log('Error de la API:', err.message);
  }
}</pre><button class="dv-copy" data-copy="c-sdk-js" data-label="sdk_js">Copiar</button></div></div>
        <div class="dv-panel" id="sdk-curl" role="tabpanel" hidden><div class="dv-code"><pre id="c-sdk-curl">curl "https://apiempresas.es/api/v1/companies?cif=A46103834" \
  -H "X-API-KEY: TU_API_KEY"</pre><button class="dv-copy" data-copy="c-sdk-curl" data-label="curl">Copiar</button></div></div>
        <ul>
            <li><code>COMPANY_NOT_FOUND</code> (404): no hay ninguna sociedad con ese CIF. <code>INVALID_CIF_FORMAT</code> (400): el CIF no tiene formato de sociedad. Ninguna de las dos gasta consultas.</li>
            <li><code>status</code> trae el estado tal como consta y <code>status_code</code>, el normalizado (<code>ACTIVE</code>, <code>INSOLVENCY</code>, <code>DISSOLVED</code>, <code>EXTINCT</code>…).</li>
            <li>Con el plan Pro, <code>admin=true</code> añade los administradores vigentes, y <code>/companies/batch</code> consulta hasta 100 CIF por petición.</li>
        </ul>
        <p class="dv-note" style="margin-bottom:0;">Todos los endpoints y campos, en la <a href="<?= site_url('documentation') ?>">documentación</a>. Para probar sin gastar consultas, la base <code>https://apiempresas.es/api/sandbox/v1</code> admite los CIF de prueba A15075062 y B00000000.</p>
    </section>

    <section class="dv-cta">
        <h2>Consulta empresas desde tu código</h2>
        <p>Razón social, estado, CNAE, provincia y, en Pro, administradores y domicilio por CIF, en JSON. SDK para PHP, Python y Node.js. Empiezas con 100 consultas gratis, sin tarjeta.</p>
        <a class="dv-btn" href="<?= $registro ?>" data-track-click="validar_cif_codigo" data-track-element="cta_api">Obtener API key gratis</a>
        <div class="dv-links"><a href="<?= site_url() ?>">API de empresas</a> · <a href="<?= site_url('documentation') ?>">Documentación</a> · <a href="<?= site_url('validar-cif-excel') ?>">Validar CIF en Excel</a></div>
    </section>

    <section class="dv-card">
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
    function track(d) { try { if (window.trackEvent) window.trackEvent('cif_code_copy', d); } catch (e) {} }
    document.querySelectorAll('.dv-tabs').forEach(function (g) {
        var tabs = g.querySelectorAll('.dv-tab');
        tabs.forEach(function (t) {
            t.addEventListener('click', function () {
                tabs.forEach(function (o) {
                    var on = o === t;
                    o.setAttribute('aria-selected', on ? 'true' : 'false');
                    document.getElementById(o.getAttribute('data-panel')).hidden = !on;
                });
            });
        });
    });
    document.querySelectorAll('.dv-copy').forEach(function (b) {
        b.addEventListener('click', function () {
            var txt = document.getElementById(b.getAttribute('data-copy')).textContent;
            var done = function () { b.textContent = 'Copiado'; setTimeout(function () { b.textContent = 'Copiar'; }, 1800); };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(txt).then(done).catch(function () {});
            } else {
                var ta = document.createElement('textarea'); ta.value = txt; document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); done(); } catch (e) {}
                document.body.removeChild(ta);
            }
            track({ snippet: b.getAttribute('data-label') });
        });
    });
})();
</script>

<?= view('partials/footer') ?>
</body>
</html>
