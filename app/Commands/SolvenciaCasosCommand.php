<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Busca en la base de datos empresas que sirvan de caso de prueba para cada estado
 * de la ficha de riesgo.
 *
 * Existe porque probar "una empresa sin incidencias" o "una con cuentas sin depositar
 * hace años" a ojo, buscando por el nombre, es lento y además no garantiza el caso:
 * lo que decide qué texto sale no es el nombre ni el sector, son las dimensiones del
 * JSON de `company_risk_profiles.risk_profile`.
 *
 * El filtrado se hace en PHP y no en SQL a propósito: las dimensiones viven dentro de
 * un JSON y las funciones JSON de MySQL cambian de versión en versión. Leer y decodificar
 * es más lento pero funciona igual en MariaDB de Laragon y en el MySQL del hosting.
 *
 * Uso:
 *   php spark solvencia:casos
 *   php spark solvencia:casos --limite=5
 */
class SolvenciaCasosCommand extends BaseCommand
{
    protected $group       = 'Solvencia';
    protected $name        = 'solvencia:casos';
    protected $description = 'Lista CIF de ejemplo para cada caso de prueba de la ficha de riesgo.';
    protected $usage       = 'solvencia:casos [--limite N] [--muestra N] [--cif CIF]';

    /** Perfiles que se leen antes de parar. Suficiente para encontrar de todo. */
    private const MUESTRA_POR_DEFECTO = 4000;

    protected $db;

    public function run(array $params)
    {
        $this->db = \Config\Database::connect();

        $limite  = max(1, (int) ($this->opcion('limite') ?: 3));
        $muestra = max(200, (int) ($this->opcion('muestra') ?: self::MUESTRA_POR_DEFECTO));

        if (!$this->db->tableExists('company_risk_profiles')) {
            CLI::error('No existe la tabla company_risk_profiles.');
            return;
        }

        // Radiografía de UNA empresa. Existe porque discutir por qué la ficha elige un
        // motivo u otro mirando capturas es adivinar: aquí salen los códigos, la
        // gravedad que les pone el motor y la prioridad con que los ordena la vista.
        $unCif = strtoupper(trim((string) ($this->opcion('cif') ?: '')));
        if ($unCif !== '') {
            $this->radiografia($unCif);
            return;
        }

        CLI::write('Leyendo hasta ' . number_format($muestra, 0, ',', '.') . ' perfiles...', 'yellow');

        $filas = $this->db->table('company_risk_profiles p')
            ->select('p.cif, p.risk_score, p.risk_profile, c.company_name, c.fecha_constitucion')
            ->join('companies c', 'c.cif = p.cif', 'left')
            ->limit($muestra)
            ->get()->getResultArray();

        if (empty($filas)) {
            CLI::error('No hay ningún perfil calculado.');
            return;
        }

        $casos = [
            'limpia'        => [],
            'cuentas'       => [],
            'grave'         => [],
            'antigua_limpia'=> [],
            'baja_confianza'=> [],
            'conflicto'     => [],
        ];

        // Cuántos perfiles traen confidence_score. Si sale 0, la salvaguarda de
        // "sin datos ≠ sin incidencias" de la ficha está desarmada: no es que las
        // empresas tengan confianza alta, es que no hay campo que mirar.
        $conConfianza = 0;
        $analizados   = 0;

        foreach ($filas as $f) {
            $datos = json_decode((string) $f['risk_profile'], true);
            if (!is_array($datos)) {
                continue;
            }

            $dims  = is_array($datos['dimensions'] ?? null) ? $datos['dimensions'] : [];
            if ($dims === []) {
                continue;   // perfil de una versión anterior: no sirve para probar textos
            }
            $analizados++;

            $legal    = (float) ($dims['legal_distress'] ?? 0);
            $cuentas  = (float) ($dims['filing_compliance'] ?? 0);
            $score    = (int) $f['risk_score'];
            // La clave real es `confidence_score`, en escala 0-100. Antes buscaba
            // 'confidence'/'confianza' y caía al 1 por defecto, así que el bloque de
            // confianza baja no encontraba NUNCA nada y parecía que no había casos.
            $conf     = isset($datos['confidence_score']) ? (int) $datos['confidence_score'] : null;
            if ($conf !== null) {
                $conConfianza++;
            }
            $anios    = $this->anios((string) ($f['fecha_constitucion'] ?? ''));

            $fila = [
                'cif'     => (string) $f['cif'],
                'nombre'  => (string) ($f['company_name'] ?? ''),
                'score'   => $score,
                'cuentas' => $cuentas,
                'legal'   => $legal,
                'conf'    => $conf,
                'anios'   => $anios,
            ];

            // --- Sin incidencias: ninguna dimensión con valor. Es la que dispara la
            //     rama "limpia" del paywall, la que no debe decir que falta explicación.
            $todoCero = true;
            foreach ($dims as $clave => $valor) {
                if ($clave === 'stabilizing_credit') {
                    continue;   // el crédito es negativo por diseño, no es una incidencia
                }
                if ((float) $valor > 0) {
                    $todoCero = false;
                    break;
                }
            }
            if ($todoCero) {
                $casos['limpia'][] = $fila;
                if ($anios !== null && $anios >= 10) {
                    $casos['antigua_limpia'][] = $fila;
                }
            }

            // --- Cuentas sin depositar: cuanto más alta la dimensión, más años de retraso.
            if ($cuentas > 0) {
                $casos['cuentas'][] = $fila;
            }

            // --- Situación registral grave: concurso, disolución, cierre de hoja...
            if ($legal > 0) {
                $casos['grave'][] = $fila;
            }

            // --- Evidencia contradictoria: el motor marca esto cuando el estado oficial
            //     y los actos del BORME no concuerdan. La ficha enseña un aviso propio
            //     que no hemos visto nunca.
            if (!empty($datos['legal_evidence_conflict'])) {
                $casos['conflicto'][] = $fila;
            }

            // --- Confianza baja en una empresa que NO es reciente: es el caso que hacía
            //     que la ficha dijera "o la empresa es muy reciente" sobre una de 2003.
            if ($conf !== null && $conf < 60 && $anios !== null && $anios >= 10) {
                $casos['baja_confianza'][] = $fila;
            }
        }

        // Las de cuentas, por retraso descendente: la peor es la que mejor prueba el texto.
        usort($casos['cuentas'], static fn ($a, $b) => $b['cuentas'] <=> $a['cuentas']);
        usort($casos['grave'],   static fn ($a, $b) => $b['score']   <=> $a['score']);
        usort($casos['limpia'],  static fn ($a, $b) => $a['score']   <=> $b['score']);
        usort($casos['antigua_limpia'], static fn ($a, $b) => $b['anios'] <=> $a['anios']);

        CLI::write('');
        CLI::write(sprintf(
            'Perfiles con dimensiones: %d · con confidence_score: %d',
            $analizados, $conConfianza
        ), $conConfianza === 0 ? 'red' : 'green');
        if ($conConfianza === 0) {
            CLI::write('  AVISO: ningún perfil trae confidence_score. El aviso de cobertura baja', 'red');
            CLI::write('  de la ficha no puede saltar nunca: hay que emitirlo desde el motor.', 'red');
        }
        CLI::write('');
        $this->bloque('B4 · SIN INCIDENCIAS (rama limpia del paywall)',
            $casos['limpia'], $limite,
            'Ninguna dimensión con valor. No debe decir "falta lo que explica ese número".');

        $this->bloque('B5 · CUENTAS SIN DEPOSITAR (el texto que mentía)',
            $casos['cuentas'], $limite,
            'Debe NO decir "cuentas depositadas al día" entre los factores estabilizadores.');

        $this->bloque('B5 · ANTIGUA Y LIMPIA (el otro texto que mentía)',
            $casos['antigua_limpia'], $limite,
            'Diez años o más sin incidencias: debe decir "antigüedad de diez años o más".');

        $this->bloque('B5 · CONFIANZA BAJA EN EMPRESA VIEJA',
            $casos['baja_confianza'], $limite,
            'No puede justificarse diciendo que "la empresa es muy reciente".');

        $this->bloque('EVIDENCIA CONTRADICTORIA',
            $casos['conflicto'], $limite,
            'El estado oficial y los actos del BORME no concuerdan: la ficha saca un aviso propio.');

        $this->tendencia($limite);

        $this->bloque('A/B · SITUACIÓN REGISTRAL GRAVE',
            $casos['grave'], $limite,
            'Para ver el nivel ALTO, el icono de aviso y el correo con recuadro rojo.');
    }

    /**
     * Vuelca lo que la ficha usa para decidir qué enseña de una empresa concreta.
     */
    private function radiografia(string $cif): void
    {
        helper(['company', 'risk_labels']);

        $f = $this->db->table('company_risk_profiles p')
            ->select('p.cif, p.risk_score, p.risk_profile, c.company_name, c.fecha_constitucion')
            ->join('companies c', 'c.cif = p.cif', 'left')
            ->where('p.cif', $cif)->get()->getRowArray();

        if ($f === null) {
            CLI::error("No hay perfil calculado para {$cif}.");
            return;
        }

        $d = json_decode((string) $f['risk_profile'], true);
        if (!is_array($d)) {
            CLI::error('El JSON del perfil no se puede decodificar.');
            return;
        }

        CLI::write("{$f['company_name']} ({$cif})", 'yellow');
        CLI::write('  risk_score ....... ' . (int) $f['risk_score']);
        CLI::write('  risk_level ....... ' . (string) ($d['data']['risk_level'] ?? $d['risk_level'] ?? '—'));
        CLI::write('  confidence_score . ' . (isset($d['confidence_score']) ? $d['confidence_score'] : '—'));
        CLI::write('  fecha_constitucion ' . (string) ($f['fecha_constitucion'] ?? '—'));
        CLI::write('');

        CLI::write('  DIMENSIONES', 'yellow');
        foreach ((array) ($d['dimensions'] ?? []) as $clave => $valor) {
            CLI::write(sprintf('   %-26s %s', $clave, $valor));
        }
        CLI::write('');

        $eventos = (array) ($d['canonical_events'] ?? []);
        CLI::write('  EVENTOS CANÓNICOS (' . count($eventos) . ')', 'yellow');
        CLI::write('  El titular ordena por gravedad primero y por prioridad después.');

        $orden = ['high' => 3, 'medium' => 2, 'low' => 1];
        $tabla = [];
        foreach ($eventos as $ev) {
            $tabla[] = [
                'code' => (string) ($ev['code'] ?? '(sin code)'),
                'sev'  => strtolower((string) ($ev['severity'] ?? '')),
                'peso' => $orden[strtolower((string) ($ev['severity'] ?? ''))] ?? 1,
                'prio' => risk_event_prioridad($ev),
                'et'   => risk_event_label($ev),
            ];
        }

        usort($tabla, static function ($a, $b) {
            if ($a['peso'] !== $b['peso']) { return $b['peso'] <=> $a['peso']; }
            return $b['prio'] <=> $a['prio'];
        });

        foreach ($tabla as $t) {
            CLI::write(sprintf('   %-34s sev=%-7s prio=%3d  %s',
                mb_substr($t['code'], 0, 34), $t['sev'] !== '' ? $t['sev'] : '(vacío)', $t['prio'], $t['et']));
        }

        if ($tabla !== []) {
            CLI::write('');
            CLI::write('  => La ficha nombraría como principal: ' . $tabla[0]['et'], 'green');
            CLI::write('     (código ' . $tabla[0]['code'] . ')');
        }
    }

    /**
     * Empresas con más de un perfil archivado: son las únicas en las que la ficha
     * puede pintar la evolución del score, porque getScoreTrend() compara contra el
     * histórico. Con el motor recién estrenado no habrá ninguna.
     */
    private function tendencia(int $limite): void
    {
        CLI::write('TENDENCIA DEL SCORE (evolución en la ficha y en el PDF)', 'yellow');
        CLI::write('  Necesita 2+ perfiles archivados del mismo CIF con 25 días entre ellos.', 'dark_gray');

        if (!$this->db->tableExists('company_risk_profiles_history')) {
            CLI::write('  (no existe company_risk_profiles_history: la evolución no puede salir nunca)');
            CLI::write('');
            return;
        }

        $filas = $this->db->query("
            SELECT h.cif, COUNT(*) AS puntos, MIN(h.calculated_at) AS desde, MAX(h.calculated_at) AS hasta,
                   c.company_name
            FROM company_risk_profiles_history h
            LEFT JOIN companies c ON c.cif = h.cif
            GROUP BY h.cif, c.company_name
            HAVING COUNT(*) > 1
            ORDER BY puntos DESC
            LIMIT ?
        ", [$limite])->getResultArray();

        if ($filas === []) {
            CLI::write('  (ninguna: el motor aún no ha archivado dos versiones de ningún perfil)');
            CLI::write('');
            return;
        }

        foreach ($filas as $f) {
            CLI::write(sprintf('  %-11s  %2d puntos  de %s a %s  %s',
                $f['cif'], (int) $f['puntos'], substr((string) $f['desde'], 0, 10),
                substr((string) $f['hasta'], 0, 10), mb_substr((string) $f['company_name'], 0, 34)));
        }
        CLI::write('');
    }

    private function bloque(string $titulo, array $filas, int $limite, string $nota): void
    {
        CLI::write($titulo, 'yellow');
        CLI::write('  ' . $nota, 'dark_gray');

        if ($filas === []) {
            CLI::write('  (ninguna en la muestra: sube --muestra o revisa si el motor ha calculado ese caso)');
            CLI::write('');
            return;
        }

        foreach (array_slice($filas, 0, $limite) as $f) {
            CLI::write(sprintf(
                '  %-11s  score %3d  cuentas %5.1f  legal %5.1f  conf %3s  %s  %s',
                $f['cif'],
                $f['score'],
                $f['cuentas'],
                $f['legal'],
                $f['conf'] === null ? ' ?' : $f['conf'] . '%',
                $f['anios'] !== null ? str_pad($f['anios'] . 'a', 4, ' ', STR_PAD_LEFT) : '  ?a',
                mb_substr($f['nombre'], 0, 38)
            ));
        }
        CLI::write('');
    }

    private function anios(string $fecha): ?int
    {
        $fecha = trim($fecha);
        if ($fecha === '' || strpos($fecha, '0000') === 0) {
            return null;
        }
        $t = strtotime($fecha);
        return $t ? (int) floor((time() - $t) / 31557600) : null;
    }

    private function opcion(string $nombre)
    {
        $valor = CLI::getOption($nombre);
        if ($valor !== null && $valor !== true) {
            return $valor;
        }
        foreach (array_keys(CLI::getOptions()) as $clave) {
            if (strpos((string) $clave, $nombre . '=') === 0) {
                return substr((string) $clave, strlen($nombre) + 1);
            }
        }
        return $valor;
    }
}
