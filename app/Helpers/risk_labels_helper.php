<?php

/**
 * Etiquetas en español para los eventos canónicos del perfil de riesgo.
 *
 * Los códigos los emite el motor (D:\apiempresas\risk_profile_engine.py, v2.0.0)
 * y llegan en el JSON de company_risk_profiles. Antes se pintaban crudos con
 * ucwords(), así que en un "dictamen oficial" aparecían títulos en inglés con
 * pinta de constante de código.
 *
 * OJO: la primera versión de este mapa se escribió a ojo, con códigos que el
 * motor NO emite (GOVERNANCE_ADMIN_CHANGES, ACCOUNTS_NOT_DEPOSITED...). El
 * resultado es que TODOS los eventos caían al texto genérico por gravedad
 * ("Incidencia registral grave") y el dictamen no decía nada concreto. Este
 * mapa está sacado del propio motor; al ampliarlo, hay que mirar allí.
 *
 * Dónde se generan los códigos en el motor:
 *   - `LEGAL_STATE_{estado}`      → evaluate_company, a partir de LegalDistressStateGraph
 *   - `INCUMPLIMIENTO_CUENTAS_{N}Y` → paramétrico con los años de retraso (ver abajo)
 *   - `ROTACION_ADMINISTRADORES_ALTA`, `CAMBIO_DOMICILIO_RECURRENTE`,
 *     `DESCAPITALIZACION_RECURRENTE` → clústeres agregados
 *   - el resto                    → TaxonomyNormalizer.classify_borme_event
 */

if (!function_exists('risk_event_labels')) {
    /**
     * Mapa código → etiqueta. Los desconocidos quedan registrados en el log
     * (ver risk_event_label) para poder completarlo sin auditar la base.
     */
    function risk_event_labels(): array
    {
        return [
            // --- Estado legal (LegalDistressStateGraph) ---
            'LEGAL_STATE_EXTINTA'                                => 'Sociedad extinguida',
            'LEGAL_STATE_CONCURSO_ACTIVO'                        => 'Concurso de acreedores en curso',
            'LEGAL_STATE_REGISTRY_CLOSURE_NIF_REVOCATION'        => 'Cierre de hoja registral por revocación del NIF',
            'LEGAL_STATE_REGISTRY_CLOSURE_TAX_INDEX_PROVISIONAL' => 'Cierre de hoja registral por baja provisional en el Índice de Entidades',
            'LEGAL_STATE_REGISTRY_CLOSURE_JUDICIAL'              => 'Cierre de hoja registral por orden judicial',
            'LEGAL_STATE_REGISTRY_CLOSURE_ACCOUNTS'              => 'Cierre de hoja registral por cuentas no depositadas',
            'LEGAL_STATE_REGISTRY_CLOSURE_GENERICO'              => 'Cierre de la hoja registral',
            'LEGAL_STATE_LIQUIDACION'                            => 'Sociedad en fase de liquidación',
            'LEGAL_STATE_DISUELTA'                               => 'Sociedad disuelta',
            'LEGAL_STATE_RECOVERED_RESOLVED'                     => 'Incidencia registral ya subsanada',
            'LEGAL_STATE_NORMAL'                                 => 'Sin incidencias en el estado registral',

            // --- Actos del BORME (TaxonomyNormalizer) ---
            'REGISTRY_CLOSURE_NIF_REVOCATION'        => 'Cierre de hoja registral por revocación del NIF',
            'REGISTRY_CLOSURE_TAX_INDEX_PROVISIONAL' => 'Baja provisional en el Índice de Entidades (AEAT)',
            'REGISTRY_CLOSURE_ACCOUNTS'              => 'Cierre de hoja registral por cuentas no depositadas',
            'REGISTRY_CLOSURE_JUDICIAL'              => 'Cierre de hoja registral por orden judicial',
            'REGISTRY_CLOSURE_GENERICO'              => 'Cierre de la hoja registral',
            'CONCURSO_ACREEDORES'                    => 'Declaración de concurso de acreedores',
            'CONCURSO_CONCLUIDO'                     => 'Concurso de acreedores concluido',
            'FASE_LIQUIDACION'                       => 'Apertura de la fase de liquidación',
            'EXTINCION_SOCIEDAD'                     => 'Extinción de la sociedad',
            'DISOLUCION_SOCIEDAD'                    => 'Disolución de la sociedad',
            'REDUCCION_CAPITAL'                      => 'Reducción de capital social',
            'AMPLIACION_CAPITAL'                     => 'Ampliación de capital social',
            'CAMBIO_DOMICILIO'                       => 'Cambio de domicilio social',
            'CAMBIO_OBJETO_SOCIAL'                   => 'Cambio del objeto social',
            'CAMBIO_ADMINISTRADOR'                   => 'Cambio en el órgano de administración',
            'CONSTITUCION_SOCIEDAD'                  => 'Constitución de la sociedad',
            'OTROS_INFORMATIVO'                      => 'Otros actos registrales',

            // --- Clústeres agregados ---
            'ROTACION_ADMINISTRADORES_ALTA' => 'Rotación alta en el órgano de administración',
            'CAMBIO_DOMICILIO_RECURRENTE'   => 'Cambios de domicilio social recurrentes',
            'DESCAPITALIZACION_RECURRENTE'  => 'Reducciones de capital repetidas',
        ];
    }
}

if (!function_exists('risk_event_label_dynamic')) {
    /**
     * Códigos paramétricos, que no caben en un mapa fijo.
     *
     * `INCUMPLIMIENTO_CUENTAS_5Y` lleva dentro los años de retraso, y ese número
     * es justo lo que le interesa a quien lee el dictamen: no es lo mismo un año
     * que ocho. Devuelve null si el código no encaja con ningún patrón.
     */
    function risk_event_label_dynamic(string $code): ?string
    {
        if (preg_match('/^INCUMPLIMIENTO_CUENTAS_(\d+)Y$/', $code, $m)) {
            $anios = (int) $m[1];

            if ($anios <= 1) {
                return 'Cuentas anuales sin depositar del último ejercicio';
            }

            return 'Cuentas anuales sin depositar desde hace ' . $anios . ' ejercicios';
        }

        return null;
    }
}

if (!function_exists('risk_event_severidad')) {
    /**
     * Gravedad de un evento como número: 4 crítica, 3 alta, 2 media, 1 baja.
     *
     * EXISTE POR UN FALLO CARO. Las vistas traían su propio mapa
     * `['high'=>3,'medium'=>2,'low'=>1]` con `?? 1` de reserva, y el motor emite
     * también `critical`. Al no estar en el mapa, ese valor caía al 1 —el MÁS BAJO—,
     * así que "Sociedad extinguida" quedaba por debajo de un retraso contable y la
     * ficha anunciaba como principal el problema menor de una empresa extinguida.
     *
     * La reserva de un valor desconocido no puede ser "lo más leve que existe": eso
     * es fallar hacia el lado peligroso. Cuando la etiqueta no se reconoce se deduce
     * de la familia del código, que sí controlamos.
     */
    function risk_event_severidad(array $flag): int
    {
        $sev = strtolower(trim((string) ($flag['severity'] ?? '')));

        $mapa = [
            'critical' => 4, 'critica' => 4, 'crítica' => 4, 'muy_alta' => 4,
            'high'     => 3, 'alta'    => 3,
            'medium'   => 2, 'media'   => 2, 'moderate' => 2,
            'low'      => 1, 'baja'    => 1, 'info'     => 1,
        ];

        if (isset($mapa[$sev])) {
            return $mapa[$sev];
        }

        // Etiqueta nueva o vacía: que mande el código, nunca el mínimo.
        $prio = risk_event_prioridad($flag);
        if ($prio >= 80) { return 4; }
        if ($prio >= 50) { return 3; }
        if ($prio >= 20) { return 2; }
        return 1;
    }
}

if (!function_exists('risk_event_es_grave')) {
    /** ¿Cuenta como alerta seria? Alta o crítica. */
    function risk_event_es_grave(array $flag): bool
    {
        return risk_event_severidad($flag) >= 3;
    }
}

if (!function_exists('risk_event_orden')) {
    /**
     * Clave única para ordenar eventos: primero la gravedad, y a igualdad la familia
     * del código. Un solo número para que todas las vistas ordenen igual.
     */
    function risk_event_orden(array $flag): int
    {
        return risk_event_severidad($flag) * 1000 + risk_event_prioridad($flag);
    }
}

if (!function_exists('risk_event_prioridad')) {
    /**
     * Cuánto pesa un evento a la hora de decidir cuál es "el principal".
     *
     * `severity` no basta: el motor marca como `high` tanto un concurso de acreedores
     * como un retraso largo en el depósito de cuentas, así que al ordenar solo por
     * gravedad quedan empatados y gana el que venga primero en el array — es decir,
     * el orden de inserción. En una empresa con concurso Y cuentas sin depositar, la
     * ficha podía anunciar como principal el retraso contable y dejar el concurso
     * dentro del desglose de pago.
     *
     * Esto NO cambia ninguna puntuación: solo decide cuál de los hechos que el motor
     * ya ha emitido se nombra primero. Se ordena por familia de código, que es
     * estable aunque aparezcan códigos nuevos.
     */
    function risk_event_prioridad(array $flag): int
    {
        $code = strtoupper(trim((string) ($flag['code'] ?? '')));
        if ($code === '') {
            return 10;
        }

        // El orden importa: "CONCURSO_CONCLUIDO" contiene "CONCURSO" y es buena
        // noticia, no la incidencia que debe encabezar el dictamen.
        $reglas = [
            ['peso' => 5,   'agujas' => ['CONCLUIDO', 'RECOVERED', 'RESUELTO', 'NORMAL']],
            ['peso' => 100, 'agujas' => ['EXTINTA', 'EXTINCION']],
            ['peso' => 95,  'agujas' => ['CONCURSO', 'SUSPENSION_PAGOS']],
            ['peso' => 90,  'agujas' => ['LIQUIDACION']],
            ['peso' => 85,  'agujas' => ['DISUELTA', 'DISOLUCION']],
            ['peso' => 80,  'agujas' => ['REGISTRY_CLOSURE', 'CIERRE', 'NIF_REVOCATION', 'TAX_INDEX']],
            ['peso' => 50,  'agujas' => ['INCUMPLIMIENTO_CUENTAS', 'FILING']],
            ['peso' => 40,  'agujas' => ['DESCAPITALIZACION', 'REDUCCION_CAPITAL']],
            ['peso' => 30,  'agujas' => ['ROTACION_ADMINISTRADORES', 'CAMBIO_ADMINISTRADOR']],
            ['peso' => 20,  'agujas' => ['CAMBIO_DOMICILIO', 'CAMBIO_OBJETO']],
        ];

        foreach ($reglas as $regla) {
            foreach ($regla['agujas'] as $aguja) {
                if (strpos($code, $aguja) !== false) {
                    return $regla['peso'];
                }
            }
        }

        // Red de seguridad: `legal_distress` es la única dimensión que llega a 100, así
        // que CUALQUIER estado legal pesa más que un incumplimiento contable, aunque su
        // código no esté en la lista de arriba. Sin esto, un estado nuevo o con otro
        // nombre caía al 10 por defecto y el retraso de cuentas le ganaba el titular a
        // una empresa que en realidad tiene la hoja cerrada.
        if (strpos($code, 'LEGAL_STATE') === 0) {
            return 75;
        }

        return 10;
    }
}

if (!function_exists('risk_event_label')) {
    /**
     * Título legible de un evento del dictamen.
     *
     * Orden: etiqueta explícita del propio evento → mapa de códigos →
     * texto genérico según gravedad. Nunca devuelve el código en inglés.
     */
    function risk_event_label(array $flag): string
    {
        foreach (['label', 'title', 'name'] as $key) {
            if (!empty($flag[$key]) && is_string($flag[$key])) {
                return $flag[$key];
            }
        }

        $code = strtoupper(trim((string)($flag['code'] ?? '')));
        $map  = risk_event_labels();

        if ($code !== '' && isset($map[$code])) {
            return $map[$code];
        }

        if ($code !== '') {
            $dinamica = risk_event_label_dynamic($code);
            if ($dinamica !== null) {
                return $dinamica;
            }

            // Deja rastro para poder completar el mapa sin tener que auditar la BD.
            log_message('info', 'Perfil de riesgo: código de evento sin etiqueta en español → ' . $code);
        }

        switch (strtolower((string)($flag['severity'] ?? ''))) {
            case 'high':
                return 'Incidencia registral grave';
            case 'medium':
                return 'Observación de estabilidad societaria';
            case 'low':
                return 'Observación registral menor';
            default:
                return 'Factor analizado';
        }
    }
}
