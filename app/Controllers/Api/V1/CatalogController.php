<?php

namespace App\Controllers\Api\V1;

use OpenApi\Attributes as OA;

/**
 * Catálogo de valores válidos para los filtros (13-10-2026). Ruta nueva, gratis en todos
 * los planes (ApiKeyFilter::getEndpointCost la deja a 0). No cambia ninguna otra ruta.
 *
 * Sin consultas a la base de datos: son valores estables. Las provincias son los valores de
 * companies.registro_mercantil y los tipos de acto los de company_radar_scores.main_act_type,
 * comprobados el 13-10-2026 (calcularlos en cada petición tardaba ~48 s). Si cambian, se
 * actualizan aquí. No hay lista de CNAE: la columna cnae_label tiene texto del objeto social y
 * códigos que no son CNAE; los recuentos, con /companies/filter?count_only=true (gratis).
 */
class CatalogController extends BaseApiController
{
    public const ACTUALIZADO = '2026-10-13';

    public const PROVINCIAS = [
        'A CORUÑA',
        'ÁLAVA',
        'ALBACETE',
        'ALICANTE',
        'ALMERÍA',
        'ASTURIAS',
        'ÁVILA',
        'BADAJOZ',
        'BARCELONA',
        'BURGOS',
        'CÁCERES',
        'CÁDIZ',
        'CANTABRIA',
        'CASTELLÓN',
        'CEUTA',
        'CIUDAD REAL',
        'CÓRDOBA',
        'CUENCA',
        'GIRONA',
        'GRANADA',
        'GUADALAJARA',
        'GUIPÚZCOA',
        'HUELVA',
        'HUESCA',
        'ISLAS BALEARES',
        'JAÉN',
        'LA RIOJA',
        'LAS PALMAS',
        'LEÓN',
        'LLEIDA',
        'LUGO',
        'MADRID',
        'MÁLAGA',
        'MELILLA',
        'MURCIA',
        'NAVARRA',
        'OURENSE',
        'PALENCIA',
        'PONTEVEDRA',
        'SALAMANCA',
        'SANTA CRUZ DE TENERIFE',
        'SEGOVIA',
        'SEVILLA',
        'SORIA',
        'TARRAGONA',
        'TERUEL',
        'TOLEDO',
        'VALENCIA',
        'VALLADOLID',
        'VIZCAYA',
        'ZAMORA',
        'ZARAGOZA',
    ];

    public const TIPOS_ACTO_RADAR = [
        'Constitución',
        'Nombramientos',
        'Sin Actos Recientes',
        'Otros',
        'Ampliación de Capital',
        'Cambio de Domicilio Social',
        'Cambio de Objeto Social',
        'Declaración de Unipersonalidad',
        'Situación Concursal',
        'Extinción',
        'Situación Concursal/Liquidación',
        'Ceses/Dimisiones',
        'Transformación',
        'Cambio de Domicilio',
        'Revocaciones',
        'Reelecciones',
        'Fusión',
    ];

    #[OA\Get(
        path: "/api/v1/catalog",
        summary: "Catálogo de valores de los filtros",
        description: "Valores válidos de province, size_band, status, range, priority y main_act_type, más los eventos de webhook y los tipos de evento de la vigilancia. Todos los planes. **Coste:** 0.",
        tags: ["1. Plan Free"]
    )]
    #[OA\Response(response: 200, description: "Catálogo")]
    public function index()
    {
        return $this->respond([
            'success' => true,
            'data'    => self::datos(),
            'meta'    => ['updated_at' => self::ACTUALIZADO, 'cost' => 0],
        ]);
    }

    public static function datos(): array
    {
        return [
            'province' => self::PROVINCIAS,
            'cnae' => [
                'format'  => 'Prefijos de la CNAE-2009 (INE) de 1 a 4 dígitos, separados por comas: 62 o 4711,4719.',
                'count'   => '/companies/filter?cnae=62&count_only=true (gratis) dice cuántas empresas hay.',
            ],
            'size_band' => [
                ['value' => 'NO_REVENUE', 'label' => 'Sin ventas'],
                ['value' => 'LT_500K', 'label' => 'Menos de 0,5 M€'],
                ['value' => '500K_1M', 'label' => 'Entre 0,5 y 1 M€'],
                ['value' => 'GT_1M', 'label' => 'Más de 1 M€'],
            ],
            'status' => \App\Services\ApiSegmentService::STATUSES,
            'radar' => [
                'range'         => ['hoy', '7', '30'],
                'range_note'    => "'hoy' o cualquier número de días atrás",
                'priority'      => ['muy_alta', 'alta', 'media', 'baja', 'muy_baja'],
                'main_act_type' => self::TIPOS_ACTO_RADAR,
            ],
            'score_priority'        => ['muy_alta', 'alta', 'media', 'baja', 'muy_baja'],
            'webhook_events'        => \App\Services\WebhookEvents::all(),
            'watchlist_event_types' => \App\Services\ApiWatchlistService::TIPOS,
        ];
    }
}
