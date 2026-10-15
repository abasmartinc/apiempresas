<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Revisa los últimos textos guardados por la plantilla (company_enrichment.ai_origen = 'plantilla')
 * y busca los fallos que salieron en las pruebas del 15-10-2026. Solo lee.
 *
 *   php spark seo:revisar-plantilla                  últimos 2.000, 5 ejemplos por fallo, 15 textos completos
 *   php spark seo:revisar-plantilla --limite 10000 --ejemplos 10 --muestra 30
 *
 * Escribe además writable/revision_plantilla.txt con todo (todas las fichas con fallo y la muestra).
 */
class RevisarPlantilla extends BaseCommand
{
    protected $group       = 'SEO';
    protected $name        = 'seo:revisar-plantilla';
    protected $description = 'Busca fallos en los últimos textos generados por la plantilla (solo lee).';
    protected $usage       = 'seo:revisar-plantilla [--limite N] [--ejemplos N] [--muestra N]';

    /** [clave, descripción, función(texto plano, fila) => bool] */
    private function comprobaciones(): array
    {
        return [
            ['corto',        'Texto de menos de 150 caracteres',                         fn ($t, $r) => mb_strlen($t, 'UTF-8') < 150],
            ['largo',        'Texto de más de 1.600 caracteres',                         fn ($t, $r) => mb_strlen($t, 'UTF-8') > 1600],
            ['sin_p2',       'Sin segundo párrafo (actividad)',                          fn ($t, $r) => !str_contains((string) $r['ai_seo_text'], "\n\n")],
            ['codigo_cnae',  'Código de CNAE suelto ("5611 /", "46.62", "(4614)")',     fn ($t, $r) => (bool) preg_match('/\b\d{2}\.\d{1,2}\b(?!\s*(?:€|%|euros))|\b\d{4}\s*[\/\-–]\s*\p{L}|\(\s*[A-U]?\d{4}\s*\)/u', $this->p2($r))],
            ['palabra_cnae', 'La palabra CNAE / n.c.o.p. / Clasificación Nacional en el párrafo 2', fn ($t, $r) => (bool) preg_match('/\bc\.?n\.?a\.?e\b|n\.\s?c\.\s?o\.\s?p|clasificaci[oó]n nacional/iu', $this->p2sinCnae($r))],
            ['rotulo',       'Rótulo suelto ("actividad principal", "otras actividades:", "principal:")', fn ($t, $r) => (bool) preg_match('/actividad(?:es)? principal|(?<!entre )otras actividades\s*[:;]|\bprincipal\s*:|\bsecundari[oa]s?\s*:/iu', $this->p2($r))],
            ['objeto_dentro','"objeto social"/"estatutos"/"por objeto" dentro de la lista de actividades', fn ($t, $r) => (bool) preg_match('/:\s.*(?:objeto social|estatutos|por objeto|tiene por objeto)/iu', $this->p2($r))],
            ['coletilla',    'Coletilla legal ("podrán ser desarrolladas", "si alguna de", "excluid", "mediante la titularidad")', fn ($t, $r) => (bool) preg_match('/podr[aá]n? (?:ser )?(?:desarrollad|ejercid|realizad)|si alguna de las|quedan? excluid|se excluye|de modo (?:directo|indirecto)|a efectos de lo dispuesto|mediante la titularidad/iu', $t)],
            ['corte_palabra','Posible palabra cortada al final de una actividad (sin "…")', fn ($t, $r) => $this->palabraCortada($this->p2($r))],
            ['mayusculas',   'Palabras en MAYÚSCULAS seguidas (texto sin pasar a minúscula)', fn ($t, $r) => (bool) preg_match('/\b\p{Lu}{4,}\s+\p{Lu}{4,}\b/u', $this->p2SinNombre($r))],
            ['mayus_media',  'Mayúscula a mitad de frase en el párrafo 2 (posible errata o título)', fn ($t, $r) => (bool) preg_match('/(?<![.:;…?]\s)(?<![.:;…?])(?<!^)\s(?!(?:España|Estado|BORME|CNAE|CIF|S\.L|S\.A|UE|IVA|I\+D)\b)\p{Lu}\p{Ll}{3,}/u', $this->p2SinNombre($r))],
            ['caracter_roto','Carácter roto (�) o entidades HTML',                          fn ($t, $r) => (bool) preg_match('/\x{FFFD}|&[a-z]+;|&#\d+;/u', (string) $r['ai_seo_text'])],
            ['puntuacion',   'Puntuación rara (",:", ";;", " ,", "..", "…." , "( ", paréntesis sin cerrar)', fn ($t, $r) => (bool) preg_match('/,:|;;|\s,|(?<!\.)\.\.(?!\.)|…\.|\(\s|\s\)/u', $t) || substr_count($t, '(') !== substr_count($t, ')')],
            ['enumeracion',  'Restos de enumeración ("a)", "1.-", "(ii)", "b.-")',          fn ($t, $r) => (bool) preg_match('/(?:^|[\s:;,])(?:[a-z]\)|\d{1,2}\s?\.-|\([ivx]{1,4}\)|[a-z]\.-)\s/u', $this->p2($r))],
            ['repetida',     'Actividad repetida en la lista',                              fn ($t, $r) => $this->hayRepetidas($r)],
            ['sin_tags',     'Sin etiquetas',                                               fn ($t, $r) => empty(json_decode((string) $r['ai_tags'], true))],
            ['pitch_largo',  'Frase corta de más de 150 caracteres o vacía',                fn ($t, $r) => ($l = mb_strlen((string) $r['ai_pitch'], 'UTF-8')) === 0 || $l > 150],
            ['pitch_raro',   'Frase corta sin actividad ("…constituida en 2026." o "…de X.")', fn ($t, $r) => (bool) preg_match('/^(?:Sociedad|Entidad)[^.]*?(?:constituida en \d{4})?\.$/u', (string) $r['ai_pitch']) && !preg_match('/objeto social|actividad/u', (string) $r['ai_pitch'])],
            ['faqs',         'Menos de 2 preguntas frecuentes',                             fn ($t, $r) => count((array) json_decode((string) $r['ai_faqs'], true)) < 2],
            ['plantilla_rota','Restos de plantilla ({, }, "Array", "NULL")',                 fn ($t, $r) => (bool) preg_match('/[{}]|\bArray\b|\bNULL\b/u', $t . ' ' . $r['ai_pitch'])],
        ];
    }

    private function p2(array $r): string
    {
        $partes = explode("\n\n", strip_tags((string) $r['ai_seo_text']));
        return $partes[1] ?? '';
    }

    /** Párrafo 2 sin la frase de cuando no hay objeto social ("…en la Clasificación Nacional… (CNAE)…") */
    private function p2sinCnae(array $r): string
    {
        $p = $this->p2($r);
        if (preg_match('/^(?:Su actividad registrada|En la Clasificación|La actividad con la que)/u', $p)) {
            return '';
        }
        return $p;
    }

    private function p2SinNombre(array $r): string
    {
        $p = $this->p2($r);
        $nombre = trim((string) ($r['name'] ?? ''));
        $p = str_replace('Clasificación Nacional de Actividades Económicas', '', $p);
        return $nombre !== '' ? str_ireplace($nombre, '', $p) : $p;
    }

    /** Actividad que acaba en una palabra de 1-3 letras que no es una palabra corriente ("…de video y d.") */
    private function palabraCortada(string $p): bool
    {
        if (!preg_match_all('/\s(\p{Ll}{1,3})[.;](?=\s|$)/u', $p, $m)) {
            return false;
        }
        $normales = ['de', 'del', 'el', 'la', 'los', 'las', 'y', 'e', 'o', 'u', 'en', 'al', 'etc', 'ia', 'ti', 'mar', 'sol', 'gas', 'pan', 'vino', 'bar', 'té', 'web', 'led', 'iot', 'luz', 'red', 'oro', 'fin', 'uso', 'fe'];
        foreach ($m[1] as $w) {
            if (!in_array($w, $normales, true)) {
                return true;
            }
        }
        return false;
    }

    private function hayRepetidas(array $r): bool
    {
        $p = $this->p2($r);
        if (!preg_match('/:\s(.+?)\.(?:\s|$)/u', $p, $m)) {
            return false;
        }
        $items = preg_split('/;\s*(?:y\s+|e\s+)?|,\s+(?=\p{Ll})/u', $m[1]);
        $vistos = [];
        foreach ($items as $i) {
            $k = mb_strtolower(trim($i), 'UTF-8');
            if (mb_strlen($k, 'UTF-8') > 15) {
                if (isset($vistos[$k])) {
                    return true;
                }
                $vistos[$k] = true;
            }
        }
        return false;
    }

    public function run(array $params)
    {
        $limite   = max(1, (int) (CLI::getOption('limite') ?: 2000));
        $ejemplos = max(1, (int) (CLI::getOption('ejemplos') ?: 5));
        $muestra  = max(0, (int) (CLI::getOption('muestra') ?: 15));
        $db = \Config\Database::connect();

        if (!$db->fieldExists('ai_origen', 'company_enrichment')) {
            CLI::error('No existe la columna company_enrichment.ai_origen: ejecuta scripts/sql/textos_plantilla.sql');
            return;
        }

        $total = (int) $db->query("SELECT COUNT(*) n FROM company_enrichment WHERE ai_origen = 'plantilla'")->getRow()->n;
        $hoy   = (int) $db->query("SELECT COUNT(*) n FROM company_enrichment WHERE ai_origen = 'plantilla' AND updated_at >= CURDATE()")->getRow()->n;
        $filas = $db->query(
            "SELECT e.company_id, e.ai_seo_text, e.ai_faqs, e.ai_tags, e.ai_pitch, e.ai_borme_summary, e.updated_at, c.cif, c.company_name AS name
               FROM company_enrichment e JOIN companies c ON c.id = e.company_id
              WHERE e.ai_origen = 'plantilla'
              ORDER BY e.updated_at DESC, e.company_id DESC
              LIMIT {$limite}"
        )->getResultArray();

        $n = count($filas);
        $out = [];
        $out[] = 'REVISIÓN DE TEXTOS DE PLANTILLA · ' . date('Y-m-d H:i');
        $out[] = "Total con ai_origen = 'plantilla': {$total} (hoy: {$hoy}). Revisados: los últimos {$n}.";
        if (!$n) {
            CLI::write(implode("\n", $out));
            return;
        }

        // Métricas generales
        $largos = array_map(fn ($r) => mb_strlen(strip_tags((string) $r['ai_seo_text']), 'UTF-8'), $filas);
        sort($largos);
        $med = $largos[intdiv($n, 2)];
        $conObjeto = count(array_filter($filas, fn ($r) => preg_match('/objeto social|estatutos/u', $this->p2($r))));
        $conCnaeP1 = count(array_filter($filas, fn ($r) => preg_match('/CNAE/u', explode("\n\n", (string) $r['ai_seo_text'])[0])));
        $conMas    = count(array_filter($filas, fn ($r) => preg_match('/entre otras/u', $this->p2($r))));
        $etiq = [];
        foreach ($filas as $r) {
            foreach ((array) json_decode((string) $r['ai_tags'], true) as $t) {
                $etiq[$t] = ($etiq[$t] ?? 0) + 1;
            }
        }
        arsort($etiq);
        $inicios = [];
        foreach ($filas as $r) {
            $k = mb_substr(strip_tags((string) $r['ai_seo_text']), 0, 0) . (preg_match('/^Constituida/u', (string) $r['ai_seo_text']) ? 'Constituida en…, X es' : (preg_match('/ es una [^.]+ constituida en/u', (string) $r['ai_seo_text']) ? 'X es … constituida en … con domicilio' : 'X es … con domicilio …'));
            $inicios[$k] = ($inicios[$k] ?? 0) + 1;
        }

        $pct = fn ($x) => round(100 * $x / $n, 1) . ' %';
        $out[] = '';
        $out[] = '— MÉTRICAS';
        $out[] = "Largo del texto: mín {$largos[0]} · mediana {$med} · máx {$largos[$n - 1]} caracteres";
        $out[] = "Párrafo 2 con objeto social: {$conObjeto} ({$pct($conObjeto)}) · solo CNAE: " . ($n - $conObjeto) . " ({$pct($n - $conObjeto)})";
        $out[] = "CNAE citado en el párrafo 1: {$conCnaeP1} ({$pct($conCnaeP1)}) · con \"entre otras\": {$conMas} ({$pct($conMas)})";
        $out[] = 'Arranque del párrafo 1: ' . implode(' · ', array_map(fn ($k, $v) => "{$k}: {$v}", array_keys($inicios), $inicios));
        $out[] = 'Etiquetas más usadas: ' . implode(', ', array_map(fn ($k, $v) => "{$k} ({$v})", array_slice(array_keys($etiq), 0, 25), array_slice($etiq, 0, 25)));
        $out[] = 'Etiquetas distintas: ' . count($etiq);

        // Comprobaciones
        $fallos = [];
        foreach ($this->comprobaciones() as [$clave, $desc, $fn]) {
            $fallos[$clave] = ['desc' => $desc, 'filas' => []];
        }
        foreach ($filas as $r) {
            $t = strip_tags((string) $r['ai_seo_text']);
            foreach ($this->comprobaciones() as [$clave, , $fn]) {
                try {
                    if ($fn($t, $r)) {
                        $fallos[$clave]['filas'][] = $r;
                    }
                } catch (\Throwable $e) {
                }
            }
        }
        uasort($fallos, fn ($a, $b) => count($b['filas']) <=> count($a['filas']));

        $out[] = '';
        $out[] = '— POSIBLES FALLOS (fichas afectadas de las ' . $n . ' revisadas)';
        foreach ($fallos as $clave => $f) {
            $out[] = str_pad((string) count($f['filas']), 6, ' ', STR_PAD_LEFT) . '  ' . str_pad($pct(count($f['filas'])), 8) . "  {$f['desc']}";
        }

        $completo = $out;
        foreach ($fallos as $clave => $f) {
            if (!$f['filas']) {
                continue;
            }
            $bloque = ['', "— EJEMPLOS: {$f['desc']}"];
            foreach ($f['filas'] as $i => $r) {
                $linea = "  [{$r['company_id']}] {$r['name']} ({$r['cif']})\n    " . str_replace("\n\n", "\n    ", strip_tags((string) $r['ai_seo_text'])) . "\n    pitch: {$r['ai_pitch']} | tags: {$r['ai_tags']}";
                if ($i < $ejemplos) {
                    $bloque[] = $linea;
                }
                $completo[] = '';
            }
            $out = array_merge($out, $bloque);
            $completo[] = "— TODAS: {$f['desc']}";
            foreach ($f['filas'] as $r) {
                $completo[] = "  [{$r['company_id']}] {$r['name']} ({$r['cif']}) :: " . str_replace("\n\n", ' ¶ ', strip_tags((string) $r['ai_seo_text']));
            }
        }

        // Muestra al azar con todo
        if ($muestra) {
            $azar = $filas;
            shuffle($azar);
            $azar = array_slice($azar, 0, $muestra);
            $bloque = ['', "— MUESTRA AL AZAR ({$muestra} fichas completas)"];
            foreach ($azar as $r) {
                $bloque[] = str_repeat('=', 80);
                $bloque[] = "[{$r['company_id']}] {$r['name']} ({$r['cif']}) · {$r['updated_at']}";
                $bloque[] = 'pitch: ' . $r['ai_pitch'];
                $bloque[] = 'tags : ' . implode(' | ', (array) json_decode((string) $r['ai_tags'], true));
                $bloque[] = strip_tags((string) $r['ai_seo_text']);
                foreach ((array) json_decode((string) $r['ai_faqs'], true) as $f) {
                    $bloque[] = 'P: ' . ($f['q'] ?? '');
                    $bloque[] = 'R: ' . ($f['a'] ?? '');
                }
                $bloque[] = 'borme: ' . $r['ai_borme_summary'];
            }
            $out = array_merge($out, $bloque);
            $completo = array_merge($completo, $bloque);
        }

        CLI::write(implode("\n", $out));
        $ruta = WRITEPATH . 'revision_plantilla.txt';
        file_put_contents($ruta, implode("\n", $completo) . "\n");
        CLI::newLine();
        CLI::write("Informe completo: {$ruta}", 'green');
    }
}
