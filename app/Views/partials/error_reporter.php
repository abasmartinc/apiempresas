<?php
/**
 * Errores de JavaScript al registro propio (el mismo que abasmart): POST errors/js -> ErrorRecorder::js().
 * Solo con sesion iniciada (las paginas publicas las ve todo el mundo y bots: no se recogen). Sin grabar la sesion ni el
 * contenido de la pagina: manda el mensaje, el fichero, la linea y la pila del error, y el servidor los limpia antes de
 * guardarlos. Como mucho 10 por pagina y sin repetir el mismo. Se incluye desde partials/head.php.
 */
if (! session('logged_in')) {
    return;
}
?>
<script>
(function () {
  if (window.__aeErrorReporter) return;
  window.__aeErrorReporter = true;
  var url = <?= json_encode(site_url('errors/js')) ?>;
  var enviados = 0, vistos = {};
  function enviar(d) {
    try {
      if (enviados >= 10 || !d.message) return;
      // "Script error." es un error de otro dominio (extension, CDN) sin ningun dato: no sirve para arreglar nada.
      if (/^Script error\.?$/.test(d.message)) return;
      var clave = d.message + '|' + (d.source || '') + '|' + (d.line || '');
      if (vistos[clave]) return;
      vistos[clave] = 1;
      enviados++;
      var f = new FormData();
      Object.keys(d).forEach(function (k) { if (d[k] !== undefined && d[k] !== null) f.append(k, String(d[k]).slice(0, 4000)); });
      f.append('page', location.pathname);
      f.append(<?= json_encode(csrf_token()) ?>, <?= json_encode(csrf_hash()) ?>);
      if (navigator.sendBeacon) navigator.sendBeacon(url, f);
      else fetch(url, { method: 'POST', body: f, credentials: 'same-origin', keepalive: true });
    } catch (e) {}
  }
  // Los fragmentos que se cargan por AJAX traen <script src>: jQuery los baja y los ejecuta (_evalUrl) y, si algo falla ahi, la pila
  // solo enseña jQuery. Se apunta que script estaba cargando (sin query string) para que el error diga cual fue.
  var cargando = null;
  function vigilarJQuery() {
    try {
      var jq = window.jQuery;
      if (!jq || !jq._evalUrl || jq._evalUrl.__pyrVigilado) return;
      var original = jq._evalUrl;
      jq._evalUrl = function (u) {
        var antes = cargando;
        cargando = String(u || '').split('?')[0];
        try { return original.apply(this, arguments); }
        catch (e) { try { if (e && typeof e === 'object' && !e.__pyrScript) e.__pyrScript = cargando; } catch (x) {} throw e; }
        finally { cargando = antes; }
      };
      jq._evalUrl.__pyrVigilado = true;
    } catch (e) {}
  }
  document.addEventListener('DOMContentLoaded', vigilarJQuery);
  window.addEventListener('load', vigilarJQuery);
  window.addEventListener('error', function (ev) {
    if (!ev || !ev.message) return;
    var pila = ev.error && ev.error.stack;
    var script = (ev.error && ev.error.__pyrScript) || cargando;
    if (script) pila = 'While jQuery was loading the script ' + script + '\n' + (pila || '');
    enviar({ message: ev.message, source: ev.filename, line: ev.lineno, column: ev.colno, stack: pila });
  });
  window.addEventListener('unhandledrejection', function (ev) {
    var r = ev && ev.reason;
    enviar({ message: 'Unhandled promise rejection: ' + (r && r.message ? r.message : String(r)), stack: r && r.stack });
  });
})();
</script>
