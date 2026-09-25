<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Parámetros comerciales de Solvencia (perfil de riesgo).
 *
 * Existe para que las decisiones de negocio —qué se enseña gratis, qué cuesta
 * cada cosa, si hay garantía— vivan en un sitio y no repartidas por diez
 * vistas. Hasta ahora el precio del CSV aparecía como 14 € en un bloque y 16 €
 * en otro de la misma página, y cambiar el modelo freemium obligaba a tocar
 * parciales a mano.
 */
class Solvencia extends BaseConfig
{
    /**
     * Qué ve el visitante anónimo en la ficha.
     *
     *  'titular' — ve el score, el nivel y una línea con el motivo principal.
     *              El desglose de actos, las fechas y el dictamen siguen tras
     *              el registro. Es el modelo actual: cada ficha sirve para SEO,
     *              y la pregunta "¿por qué?" queda abierta en la primera visita.
     *
     *  'opaco'   — no se revela nada del resultado (comportamiento anterior).
     *              Convierte mejor por visita, pero deja la ficha inútil para
     *              quien llega de Google y no quiere registrarse.
     *
     * Cambiar este valor es la palanca del debate "profundidad vs. volumen".
     * No hay que tocar ninguna vista: el teaser se adapta solo.
     */
    public string $teaserModo = 'titular';

    /** Consultas completas al mes para un registrado gratuito. */
    public int $consultasGratis = 3;

    /**
     * Empresas que puede vigilar a la vez un usuario gratuito.
     *
     * Vigilar SÍ se le permite aunque no le queden consultas: cuesta una fila y
     * un correo, y ese correo es la mejor herramienta que hay para traerlo de
     * vuelta con una razón concreta. Pero sin tope competiría con Pro, porque
     * quien solo necesita "avísame si se mueve" no tendría por qué pagar nunca.
     *
     * 5 da para las empresas que de verdad importan y se queda corto para una
     * cartera — que es justo quien debe pasar a Pro. Y el momento en que se
     * llena es un upsell mucho mejor que el paywall de consultas: para entonces
     * ya ha recibido avisos y sabe lo que se lleva.
     */
    public int $vigilanciasGratis = 5;

    /**
     * Empresas que puede vigilar a la vez un suscriptor de Pro.
     *
     * Antes era ilimitado, y eso tenía dos problemas. El comercial: un cliente
     * que vigila 3 empresas y otro que vigila 400 pagaban lo mismo, así que la
     * cuenta no crecía nunca y todo el crecimiento tenía que venir de clientes
     * nuevos, que es la forma más cara de crecer. Y el competitivo: NADIE en
     * este mercado vende ilimitado —los planes de eInforma son de cupo, los de
     * InfoNIF también—, así que ofrecerlo a 29 € no leía como generosidad sino
     * como "el dato debe de ser peor".
     *
     * 25 cubre de sobra a quien lleva una cartera de clientes habituales y se
     * queda corto para quien gestiona una cartera de verdad — que es justo el
     * cliente que debe pasar al plan siguiente cuando exista.
     */
    public int $vigilanciasPro = 25;

    /**
     * Consultas distintas al mes de un suscriptor.
     *
     * Esto NO es una palanca comercial, es un tope técnico: con consultas
     * ilimitadas a 29 € cualquiera puede recorrer el catálogo y reconstruirse
     * el directorio que vendemos aparte (CSV a 14 €, listados, API). Informa
     * vende su rating a 0,65 €/registro al por mayor; nosotros lo estábamos
     * regalando.
     *
     * 300 al mes son diez empresas al día todos los días: ningún usuario real
     * lo nota, y quien lo note no es un usuario, es un extractor. Si alguien
     * legítimo lo alcanza, es la señal de que necesita el plan de cartera.
     */
    public int $consultasPro = 300;

    /**
     * Carga de cartera: de cuántas empresas ve el NIVEL un usuario gratuito.
     *
     * Antes el gratuito veía la puntuación de las 2.000 filas y se la podía
     * descargar en CSV: el tope de 3 consultas al mes y el CSV de 14 € no
     * servían de nada. Ahora ve el nivel (sin puntuación) de las N con más
     * riesgo —que es justo lo que le demuestra el valor— y el resto aparece sin
     * dato, con la salida a Pro. La puntuación y la exportación son de Pro.
     */
    public int $carteraNivelesGratis = 25;

    // -------------------------------------------------------------------
    // Cortes del nivel de riesgo
    // -------------------------------------------------------------------

    /**
     * Estos dos números TIENEN que coincidir con los del motor de scoring
     * (risk_profile_engine.py, RiskRulesEngine::evaluate_company):
     *
     *     if final_risk_score >= 60: risk_level = "ALTO"
     *     elif final_risk_score >= 30: risk_level = "MEDIO"
     *
     * Estaban en 70 en cada vista PHP por separado, así que una empresa con 65
     * llevaba la etiqueta "ALTO" que viene del motor pintada en naranja de
     * "MEDIO". Un cliente que compare dos fichas lo ve enseguida.
     */
    public int $umbralMedio = 30;
    public int $umbralAlto  = 60;

    // -------------------------------------------------------------------
    // Garantía
    // -------------------------------------------------------------------

    /**
     * Garantía de devolución del primer periodo facturado.
     *
     * Se eligió garantía en vez de "primer mes reducido" porque Solvencia se
     * demuestra el día que salta una alerta, no el día 1: cobrar 9 € por un mes
     * en el que estadísticamente no pasa nada y 29 € el día 30 compra una baja.
     * La garantía, en cambio, solo cuesta cuando el cliente la ejerce.
     */
    public bool $garantiaActiva = true;

    /** Días desde el cobro para pedir la devolución. 30 = un ciclo completo. */
    public int $garantiaDias = 30;

    /**
     * Planes a los que aplica (slug de api_plans). El PDF suelto y el pack de
     * créditos quedan fuera a propósito: son consumibles, no suscripciones.
     */
    public array $garantiaPlanes = ['risk_pro'];

    /**
     * Prueba social (App\Libraries\PruebaSocial): "Vigilamos N empresas · N avisos en
     * los últimos 30 días", calculada de la BD. Por debajo de este número de empresas
     * vigiladas no se enseña: una cifra pequeña resta confianza en vez de sumarla.
     */
    public int $pruebaSocialMinimo = 100;

    // -------------------------------------------------------------------
    // Lo que se COBRA por los pagos de un solo uso, en céntimos
    // -------------------------------------------------------------------

    /**
     * Importe que se le pasa a Stripe por cada PDF, en céntimos.
     *
     * Esto NO es un precio "mostrado": es el número que viaja a la pasarela en
     * `unit_amount`. Vivía escrito a mano en `Company::checkoutPremiumPdf()`
     * (`$unitAmount = 390` / `590`) mientras la ficha ya sacaba el precio de
     * `$precios`, así que quedaban dos fuentes para el mismo número y nada
     * obligaba a que coincidieran. Es exactamente el fallo que ya ocurrió una
     * vez —anunciar 3,90 € y cobrar 5,90 €—, sobreviviendo en el único sitio
     * donde equivocarse cuesta dinero de verdad.
     *
     * Ahora manda esto: los textos de `$precios['pdf']` y `$precios['dossier']`
     * se derivan de aquí en el constructor, así que solo hay un número por
     * producto y no se pueden desincronizar.
     *
     * Las suscripciones NO están aquí a propósito: su importe sale de
     * `api_plans` (`price_monthly` / `price_annual`), que es lo que Stripe
     * factura de forma recurrente.
     */
    public array $centimos = [
        'pdf'     => 390,
        'dossier' => 590,
        'pack5'   => 990,
    ];

    // -------------------------------------------------------------------
    // Precios mostrados (el cobro real lo fija Stripe / api_plans)
    // -------------------------------------------------------------------

    public array $precios = [
        'pro_mensual'         => '29 €',
        'pro_anual'           => '290 €',
        'pro_anual_mes'       => '24,16 €',
        'pro_anual_ahorro'    => '58 €',
        // 58 / 348 ≈ 16,7 %. Se escribe aquí para no calcularlo en la vista.
        'pro_anual_descuento' => '17 %',
        // Estos dos los PISA el constructor con el valor de $centimos. Se dejan
        // escritos para que la lista se lea entera de un vistazo, pero el que
        // manda es el céntimo, que es el que se le cobra al cliente.
        'pdf'                 => '3,90 €',
        'dossier'             => '5,90 €',
        'pack5'               => '9,90 €', // también lo pisa el constructor

        'csv'                 => '14 €',
        /**
         * Lo que cuesta un informe suelto en un proveedor tradicional.
         *
         * Estaba en "~25 €", inventado a ojo y con una tilde de aproximación
         * delante. Las cifras reales de eInforma, leídas de sus fichas de
         * producto en septiembre de 2026 (su página de tarifas no las publica):
         *
         *     Perfil de Empresa      5 €
         *     Informe de Riesgo     20 €
         *     Informe Comercial     28 €
         *     Informe Financiero    34 €
         *     Informe Completo      44 €
         *
         * InfoNIF, que sí publica tarifa, cobra 30 € su informe de riesgo.
         * O sea: el ancla se estaba quedando corta contra nosotros mismos.
         *
         * Se muestra el rango porque es lo verificable; para el cálculo del
         * punto de equilibrio se usa el extremo BAJO (ver $preciosNum), que es
         * la comparación más conservadora que se puede hacer.
         *
         * Conviene recapturar estas cifras antes de cada campaña: no son
         * públicas y pueden moverse.
         */
        'informe_tradicional' => '20–44 €',
    ];

    /**
     * Los mismos precios en número, solo para calcular.
     *
     * Los de arriba son texto ("29 €", "~25 €") porque es lo que se pinta;
     * parsearlos para hacer cuentas es pedir un fallo silencioso el día que uno
     * lleve un punto de millar.
     */
    public array $preciosNum = [
        'pro_mensual'         => 29,
        // El extremo BAJO del rango (Informe de Riesgo de eInforma, 20 €): si el
        // argumento se sostiene con la comparación más desfavorable, se sostiene.
        'informe_tradicional' => 20,
    ];

    /**
     * Deriva los precios de los PDF del importe que se cobra, no al revés.
     *
     * Así el texto de la ficha y el `unit_amount` de Stripe no pueden decir
     * cosas distintas: hay un solo número por producto, en `$centimos`.
     */
    public function __construct()
    {
        parent::__construct();

        foreach ($this->centimos as $clave => $importe) {
            $this->precios[$clave] = number_format(((int) $importe) / 100, 2, ',', '.') . ' €';
        }
    }

    // -------------------------------------------------------------------
    // Comparativa con la alternativa tradicional
    // -------------------------------------------------------------------

    /**
     * ¿Se nombra a los competidores en el ancla de precio?
     *
     * Estaba en `true` de facto: el upsell y el paywall decían "Informa D&B /
     * Axesor" con su precio ("~25 €/informe") y sus condiciones ("exige
     * permanencia o saldo prepago"). La publicidad comparativa en España exige
     * que lo que se afirma del competidor sea objetivo y verificable, y ahí se
     * estaban afirmando el precio Y las condiciones contractuales de un tercero
     * con una tilde de aproximación delante.
     *
     * El ancla funciona igual sin el nombre, así que por defecto va apagado.
     * Ponerlo en `true` solo tiene sentido con las cifras documentadas y
     * comprobadas — y es una decisión que conviene consultar con un abogado,
     * no con un programador.
     */
    public bool $nombrarCompetidores = false;

    /** Cómo se llama la alternativa cuando no se la nombra. */
    public string $referenciaTradicional = 'Un informe tradicional';

    /** Y los nombres, para cuando `$nombrarCompetidores` esté activo. */
    public string $competidores = 'Informa D&B / Axesor';

    /**
     * Atajo para las vistas: `solvencia('precios.csv')`, con valor por defecto.
     */
    public function get(string $ruta, $porDefecto = null)
    {
        $partes = explode('.', $ruta);
        $valor  = $this;

        foreach ($partes as $parte) {
            if (is_object($valor) && isset($valor->{$parte})) {
                $valor = $valor->{$parte};
            } elseif (is_array($valor) && array_key_exists($parte, $valor)) {
                $valor = $valor[$parte];
            } else {
                return $porDefecto;
            }
        }

        return $valor;
    }
}
