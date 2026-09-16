<?php

namespace App\Controllers;

use App\Services\CompanyRiskService;
use App\Services\CompanyWatchService;
use Config\Database;

/**
 * Carga de cartera: subir un CSV de CIFs y ponerlos en vigilancia de golpe.
 *
 * Por qué existe: hasta ahora las empresas se añadían de una en una. Un
 * responsable de cobros tiene cuatrocientos clientes, así que de una en una la
 * herramienta no le sirve — la usa dos veces y no vuelve. Esto es lo que
 * convierte Solvencia en algo que se tiene abierto en vez de algo que se
 * consulta.
 *
 * Dos decisiones de producto que están aquí y no en la vista:
 *
 *  1. **Subir la cartera NO consume consultas.** Vigilar y consultar son cosas
 *     distintas: cobrar 400 consultas por subir un fichero mataría la función
 *     antes de nacer. Lo que gasta es cupo de vigilancia. El dictamen completo
 *     de cada empresa sigue siendo una consulta aparte.
 *
 *  2. **El plan gratuito también puede subir su lista**, con su tope de 5. Que
 *     vea sus 200 empresas ordenadas por riesgo y que solo le caben 5 es un
 *     argumento mucho mejor que el paywall: tiene el problema delante.
 */
class Cartera extends BaseController
{
    /** Máximo de filas que se leen del fichero. */
    private const MAX_FILAS = 2000;

    /** Tamaño máximo del CSV, en bytes. */
    private const MAX_BYTES = 1048576;   // 1 MB

    /** Cabeceras que delatan la columna del CIF. */
    private const CABECERAS_CIF = ['cif', 'nif', 'nif/cif', 'cif/nif', 'identificador', 'documento', 'vat', 'tax id'];

    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'))->with('error', 'Inicia sesión para subir tu cartera.');
        }

        return $this->renderView('risk_profile/cartera', [
            'title' => 'Subir mi cartera de clientes | APIEmpresas',
            'cupo'  => (new CompanyWatchService())->estadoCupo((int) session('user_id')),
            'maxFilas' => self::MAX_FILAS,
        ]);
    }

    /**
     * Paso 1: leer el CSV y enseñar qué hay dentro ANTES de tocar nada.
     *
     * No se guarda nada aquí a propósito. Dar de alta 200 vigilancias porque
     * alguien ha soltado un fichero por error es el tipo de cosa que hace que la
     * gente deje de usar una herramienta.
     */
    public function analizar()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $fichero = $this->request->getFile('cartera');

        if (!$fichero || !$fichero->isValid()) {
            return redirect()->to(site_url('cartera'))->with('error', 'No se ha recibido ningún fichero. Vuelve a intentarlo.');
        }
        if ($fichero->getSize() > self::MAX_BYTES) {
            return redirect()->to(site_url('cartera'))->with('error', 'El fichero pesa más de 1 MB. Sube solo la columna de CIF, o pártelo en dos.');
        }

        $extension = strtolower((string) $fichero->getClientExtension());
        if (!in_array($extension, ['csv', 'txt'], true)) {
            return redirect()->to(site_url('cartera'))
                ->with('error', 'Solo admitimos CSV o TXT. Si tienes un Excel, guárdalo como CSV desde «Guardar como».');
        }

        $contenido = @file_get_contents($fichero->getTempName());
        if ($contenido === false || trim($contenido) === '') {
            return redirect()->to(site_url('cartera'))->with('error', 'El fichero está vacío.');
        }

        $lectura = $this->extraerCifs($contenido);

        if (empty($lectura['cifs'])) {
            return redirect()->to(site_url('cartera'))
                ->with('error', 'No hemos encontrado ningún CIF en el fichero. Comprueba que hay una columna con los CIF (B12345678, A28017895…).');
        }

        $encontradas = $this->buscarEmpresas($lectura['cifs'], $userId);

        // Ordenado por riesgo: el valor de subir la cartera es ver de golpe
        // CUÁLES de tus clientes tienen un problema, no tener la lista.
        usort($encontradas, static fn ($a, $b) => $b['score'] <=> $a['score']);

        $cifsEncontrados = array_column($encontradas, 'cif');
        $noEncontrados   = array_values(array_diff($lectura['cifs'], $cifsEncontrados));

        return $this->renderView('risk_profile/cartera_resultado', [
            'title'         => 'Tu cartera analizada | APIEmpresas',
            'empresas'      => $encontradas,
            'noEncontrados' => $noEncontrados,
            'leidos'        => count($lectura['cifs']),
            'descartados'   => $lectura['descartados'],
            'truncado'      => $lectura['truncado'],
            'cupo'          => (new CompanyWatchService())->estadoCupo($userId),
        ]);
    }

    /**
     * Paso 2: dar de alta la vigilancia de lo que el usuario haya marcado.
     */
    public function vigilar()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $pedidos = (array) ($this->request->getPost('cifs') ?? []);

        if (empty($pedidos)) {
            return redirect()->to(site_url('cartera'))->with('error', 'No has marcado ninguna empresa.');
        }

        $servicio = new CompanyWatchService();
        $riesgo   = new CompanyRiskService();

        $altas = 0;
        $yaEstaban = 0;
        $sinSitio = 0;     // no caben en el cupo
        $fallidas = 0;     // la empresa no existe o no se puede cruzar con el BORME

        // El cupo se lee UNA vez y se lleva la cuenta en memoria. Preguntarle al
        // servicio empresa por empresa serían tres consultas por fila, y aquí
        // pueden llegar dos mil: el usuario se comería el timeout esperando a que
        // le dijéramos que no le caben.
        $huecos = max(0, (int) ($servicio->estadoCupo($userId)['quedan'] ?? 0));

        foreach (array_slice($pedidos, 0, self::MAX_FILAS) as $cifBruto) {
            $cif = $riesgo->cleanCif((string) $cifBruto);
            if ($cif === '') {
                continue;
            }

            if ($servicio->isWatching($userId, $cif)) {
                $yaEstaban++;
                continue;
            }

            if ($huecos <= 0) {
                $sinSitio++;
                continue;   // sin tocar la base de datos: ya sabemos que no cabe
            }

            if ($servicio->watch($userId, $cif, 'cartera')) {
                $altas++;
                $huecos--;
            } else {
                // La empresa no existe o no tiene company_id con el que cruzar el
                // BORME. No es un problema de cupo, así que no se descuenta.
                $fallidas++;
            }
        }

        $cupo = $servicio->estadoCupo($userId);

        $mensaje = $altas === 1
            ? 'Hemos puesto 1 empresa bajo vigilancia.'
            : 'Hemos puesto ' . $altas . ' empresas bajo vigilancia.';
        if ($yaEstaban > 0) {
            $mensaje .= ' ' . $yaEstaban . ($yaEstaban === 1 ? ' ya estaba' : ' ya estaban') . ' en tu lista.';
        }
        if ($sinSitio > 0) {
            $mensaje .= ' No caben ' . $sinSitio . ' más: tu plan permite ' . $cupo['tope'] . ' a la vez.';
        }
        // Se dicen por separado a propósito: "no caben" se arregla pasando a Pro
        // y "no hemos podido" no, así que mezclarlas sería vender mal.
        if ($fallidas > 0) {
            $mensaje .= ' ' . $fallidas . ($fallidas === 1 ? ' no ha podido darse' : ' no han podido darse')
                      . ' de alta porque no consta en nuestra base de datos.';
        }

        return redirect()->to(site_url('dashboard?view=risk'))->with('success', $mensaje);
    }

    /**
     * Descarga en CSV la cartera ya analizada.
     *
     * Es el momento de máximo valor de todo el producto: el usuario acaba de ver
     * cuáles de sus doscientos clientes tienen algo. Sin un botón aquí, esa
     * pantalla no sale de la pantalla — y lo que quiere hacer con ella es
     * pasársela a alguien o meterla en su Excel.
     *
     * No se guarda nada entre pasos, así que los CIF vuelven en un campo oculto
     * y se recalcula. Es una consulta más, pero evita inventarse una tabla de
     * "análisis guardados" que nadie ha pedido todavía.
     */
    public function exportar()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $riesgo = new CompanyRiskService();

        $brutos = (string) ($this->request->getPost('lista') ?? '');
        $cifs = [];
        foreach (explode(',', $brutos) as $trozo) {
            $cif = $riesgo->cleanCif($trozo);
            if ($cif !== '') {
                $cifs[$cif] = true;
            }
            if (count($cifs) >= self::MAX_FILAS) {
                break;
            }
        }
        $cifs = array_keys($cifs);

        if (empty($cifs)) {
            return redirect()->to(site_url('cartera'))->with('error', 'No hay nada que exportar.');
        }

        $empresas = $this->buscarEmpresas($cifs, $userId);
        usort($empresas, static fn ($a, $b) => $b['score'] <=> $a['score']);

        $encontrados = array_column($empresas, 'cif');
        $noEncontrados = array_values(array_diff($cifs, $encontrados));

        helper(['company', 'url']);

        // Punto y coma y BOM: es lo que hace que un Excel en español abra el
        // fichero en columnas y con los acentos bien. Con coma y sin BOM el
        // usuario ve una sola columna llena de símbolos raros y da por hecho que
        // la exportación está rota.
        $lineas = [];
        $lineas[] = $this->filaCsv(['CIF', 'Empresa', 'Provincia', 'Puntuacion', 'Nivel', 'Vigilando', 'Ficha']);

        foreach ($empresas as $e) {
            $lineas[] = $this->filaCsv([
                $e['cif'],
                company_display_name($e['nombre'], 'Empresa'),
                $e['provincia'],
                $e['score'] !== null ? (string) $e['score'] : '',
                $e['score'] !== null ? risk_level_visual((int) $e['score'])[0] : 'SIN CALCULAR',
                $e['vigilando'] ? 'Si' : 'No',
                site_url('empresa/' . $e['id'] . '-' . url_title($e['nombre'] ?: 'empresa', '-', true)),
            ]);
        }

        foreach ($noEncontrados as $cif) {
            $lineas[] = $this->filaCsv([$cif, 'No consta en la base de datos', '', '', '', 'No', '']);
        }

        $csv = "\xEF\xBB\xBF" . implode("\r\n", $lineas) . "\r\n";
        $nombre = 'cartera-riesgo-' . date('Y-m-d') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $nombre . '"')
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setBody($csv);
    }

    /**
     * Una fila de CSV con las comillas escapadas como manda el RFC.
     *
     * El `\t` delante de nada: los CIF que empiezan por letra no los toca Excel,
     * pero los NIF de ocho dígitos sí se los comería como número y perdería el
     * cero de la izquierda. Se entrecomillan todos y listo.
     *
     * @param list<string> $celdas
     */
    private function filaCsv(array $celdas): string
    {
        $escapadas = array_map(
            static fn ($c) => '"' . str_replace('"', '""', (string) $c) . '"',
            $celdas
        );

        return implode(';', $escapadas);
    }

    // ------------------------------------------------------------------
    // Lectura del CSV
    // ------------------------------------------------------------------

    /**
     * Saca los CIF de un CSV que puede venir de cualquier sitio.
     *
     * Los CSV que manda la gente vienen de Excel en español: separador punto y
     * coma, acentos en Windows-1252 y a veces un BOM delante. Si esto no lo
     * tolera, el usuario ve "no hemos encontrado ningún CIF" en un fichero que
     * está perfectamente bien y no vuelve a intentarlo.
     *
     * @return array{cifs:list<string>,descartados:int,truncado:bool}
     */
    private function extraerCifs(string $contenido): array
    {
        // 1. A UTF-8, venga como venga.
        $codificacion = mb_detect_encoding($contenido, ['UTF-8', 'Windows-1252', 'ISO-8859-15', 'ISO-8859-1'], true);
        if ($codificacion !== false && $codificacion !== 'UTF-8') {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', $codificacion);
        }
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido);       // BOM
        $contenido = str_replace(["\r\n", "\r"], "\n", $contenido);

        $lineas = array_values(array_filter(
            explode("\n", $contenido),
            static fn ($l) => trim($l) !== ''
        ));
        if (empty($lineas)) {
            return ['cifs' => [], 'descartados' => 0, 'truncado' => false];
        }

        $separador = $this->detectarSeparador($lineas);

        // 2. ¿Qué columna trae el CIF? Primero por el nombre de la cabecera;
        //    si no, por cuál tiene más valores con pinta de CIF.
        $primera = str_getcsv($lineas[0], $separador);
        $columna = $this->columnaPorCabecera($primera);
        $hayCabecera = $columna !== null;

        if ($columna === null) {
            $columna = $this->columnaPorContenido($lineas, $separador);
        }

        $cifs = [];
        $descartados = 0;
        $truncado = false;
        $servicio = new CompanyRiskService();

        foreach ($lineas as $i => $linea) {
            if ($i === 0 && $hayCabecera) {
                continue;
            }
            if (count($cifs) >= self::MAX_FILAS) {
                $truncado = true;
                break;
            }

            $celdas = str_getcsv($linea, $separador);
            // Con una sola columna da igual el separador: se coge la línea entera.
            $bruto = $columna !== null && isset($celdas[$columna])
                ? $celdas[$columna]
                : ($celdas[0] ?? '');

            $cif = $servicio->cleanCif((string) $bruto);

            if (!$this->pareceCif($cif)) {
                $descartados++;
                continue;
            }
            $cifs[$cif] = true;   // clave = deduplicado gratis
        }

        return [
            'cifs'        => array_keys($cifs),
            'descartados' => $descartados,
            'truncado'    => $truncado,
        ];
    }

    /** El separador es el que más veces aparece en las primeras líneas. */
    private function detectarSeparador(array $lineas): string
    {
        $muestra = implode("\n", array_slice($lineas, 0, 10));
        $conteo = [
            ';'  => substr_count($muestra, ';'),
            ','  => substr_count($muestra, ','),
            "\t" => substr_count($muestra, "\t"),
            '|'  => substr_count($muestra, '|'),
        ];
        arsort($conteo);
        $mejor = array_key_first($conteo);

        return $conteo[$mejor] > 0 ? $mejor : ';';
    }

    /** @param list<string> $cabecera */
    private function columnaPorCabecera(array $cabecera): ?int
    {
        foreach ($cabecera as $i => $celda) {
            $limpia = mb_strtolower(trim((string) $celda), 'UTF-8');
            $limpia = str_replace(['.', '_', '-'], ' ', $limpia);
            $limpia = trim(preg_replace('/\s+/', ' ', $limpia));

            if (in_array($limpia, self::CABECERAS_CIF, true)) {
                return (int) $i;
            }
        }

        return null;
    }

    /** Sin cabecera útil: gana la columna con más valores que parecen un CIF. */
    private function columnaPorContenido(array $lineas, string $separador): ?int
    {
        $servicio = new CompanyRiskService();
        $votos = [];

        foreach (array_slice($lineas, 0, 30) as $linea) {
            foreach (str_getcsv($linea, $separador) as $i => $celda) {
                if ($this->pareceCif($servicio->cleanCif((string) $celda))) {
                    $votos[$i] = ($votos[$i] ?? 0) + 1;
                }
            }
        }

        if (empty($votos)) {
            return null;
        }
        arsort($votos);

        return (int) array_key_first($votos);
    }

    /**
     * Forma de un identificador español: letra + 7 dígitos + control (sociedades)
     * o 8 dígitos + letra (NIF de persona física, que también puede ser empresario).
     */
    private function pareceCif(string $cif): bool
    {
        if ($cif === '') {
            return false;
        }

        return (bool) preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/', $cif)
            || (bool) preg_match('/^[0-9]{8}[A-Z]$/', $cif);
    }

    // ------------------------------------------------------------------
    // Cruce con la base de datos
    // ------------------------------------------------------------------

    /**
     * Busca las empresas y su score. Por trozos, porque un IN con dos mil
     * elementos es una consulta que ningún índice agradece.
     *
     * @param  list<string> $cifs
     * @return list<array<string,mixed>>
     */
    private function buscarEmpresas(array $cifs, int $userId): array
    {
        helper('company');

        $db = Database::connect();
        $salida = [];

        // Las vigilancias, de una vez. Preguntar empresa por empresa serían
        // cuatrocientas consultas para pintar una tabla.
        $yaVigiladas = [];
        foreach (array_chunk($cifs, 400) as $trozo) {
            $filas = $db->table('user_company_watch')
                ->select('cif')
                ->where('user_id', $userId)
                ->where('active', 1)
                ->whereIn('cif', $trozo)
                ->get()->getResultArray();
            foreach ($filas as $f) {
                $yaVigiladas[strtoupper(trim((string) $f['cif']))] = true;
            }
        }

        foreach (array_chunk($cifs, 400) as $trozo) {
            // Ojo con los nombres: en `companies` la provincia es
            // `registro_mercantil`, y `company_risk_profiles` NO tiene columna
            // `risk_level` — el nivel se deriva del score con los mismos cortes
            // que usa el resto del producto.
            $filas = $db->table('companies')
                ->select('id, cif, company_name, registro_mercantil AS province')
                ->whereIn('cif', $trozo)
                ->get()->getResultArray();

            if (empty($filas)) {
                continue;
            }

            $scores = [];
            $perfiles = $db->table('company_risk_profiles')
                ->select('cif, risk_score')
                ->whereIn('cif', array_column($filas, 'cif'))
                ->get()->getResultArray();
            foreach ($perfiles as $p) {
                $scores[strtoupper(trim((string) $p['cif']))] = (int) $p['risk_score'];
            }

            foreach ($filas as $f) {
                $cif   = strtoupper(trim((string) $f['cif']));
                $score = $scores[$cif] ?? null;

                $salida[$cif] = [
                    'id'        => (int) $f['id'],
                    'cif'       => $cif,
                    'nombre'    => (string) ($f['company_name'] ?? ''),
                    'provincia' => (string) ($f['province'] ?? ''),
                    'score'     => $score,
                    'nivel'     => $score !== null ? risk_level_visual($score)[0] : '',
                    'vigilando' => isset($yaVigiladas[$cif]),
                ];
            }
        }

        return array_values($salida);
    }
}
