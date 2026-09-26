<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * PEDIDOS A MEDIDA
 *
 * Listados que se pactan por soporte (un ticket) y que no salen de la tienda
 * normal: precio cerrado, filtros propios y columnas que la descarga estándar
 * no trae.
 *
 * Cada pedido tiene un token aleatorio: es lo único que va en el enlace que se
 * le manda al cliente (https://apiempresas.es/pedido/{token}). Quien tiene el
 * enlace puede pagar; para DESCARGAR hace falta además la sesión de Stripe
 * cobrada de ese pedido (ver App\Services\PedidoMedidaService).
 *
 * El importe va SIN IVA: Stripe suma el 21 % con STRIPE_TAX_RATE_ID, igual que
 * en el resto de compras sueltas.
 *
 * Para dar otro pedido de alta: copiar un bloque, token nuevo
 * (php -r "echo bin2hex(random_bytes(12));"), y ajustar usuario, importe y filtros.
 */
class PedidosMedida extends BaseConfig
{
    public array $pedidos = [

        // Ticket de Jose Luis Bejar (08-10/09/2026). Pactado: 77 € + IVA = 93,17 €,
        // pago a finales de septiembre. Empresas activas de la División 62 con la
        // equivalencia CNAE-2025 y el tramo de facturación.
        '07210e361f63379bdb3464ef' => [
            'user_id'            => 544,
            'referencia'         => 'PED-544-DIV62',
            'titulo'             => 'Empresas activas de la División 62 · Programación, consultoría y otras actividades informáticas',
            // Esto es lo que Stripe imprime en el recibo y lo que sale en la factura.
            'producto'           => 'Listado a medida: empresas activas División 62 (CSV)',
            'descripcion'        => 'Empresas ACTIVAS con CNAE-2009 6201, 6202, 6203, 6209 y CNAE-2025 6210, 6220, 6290, con equivalencia CNAE-2025 y tramo de facturación.',
            'importe'            => 77.00,
            'empresas_acordadas' => 32713,
            'filtros'            => [
                'cnae_prefijos' => ['62'],
                'solo_activas'  => true,
            ],
            'nombre_fichero'     => 'APIEmpresas_Division62_Activas',
            // A partir de esta fecha el enlace de PAGO deja de funcionar. La descarga
            // de quien ya pagó sigue funcionando.
            'caduca'             => '2026-10-31',
        ],

    ];

    /**
     * Columna de `companies` con el año de las últimas cuentas depositadas.
     *
     * Si se deja vacío se busca sola entre los nombres de $candidatosAnioCuentas.
     * Si no existe ninguna, la columna del CSV sale vacía y queda en el log.
     */
    public string $columnaAnioCuentas = 'ult_cuentas_anio';

    public array $candidatosAnioCuentas = [
        'ult_cuentas_anio',
        'ult_cuentas_year',
        'ultimas_cuentas_year',
        'ultimo_ejercicio_depositado',
        'ultimo_ejercicio',
        'ult_cuentas',
        'ultimas_cuentas',
        'ventas_year',
        'ventas_anio',
        'ejercicio_ventas',
        'anio_cuentas',
    ];
}
