<?php

namespace App\Commands;

use App\Services\PedidoMedidaService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Genera por adelantado el CSV de un pedido a medida (Config\PedidosMedida)
 * y lo deja en writable/pedidos/{token}.csv, que es lo que descarga el cliente.
 *
 *   php spark pedido:generar 07210e361f63379bdb3464ef
 *   php spark pedido:generar            (todos los pedidos configurados)
 *
 * OJO: se ejecuta en el SERVIDOR. Si se lanza en local, el archivo se queda en
 * el writable local y producción no lo ve (allí se generaría en la primera
 * descarga). Desde el navegador, el botón "Generar archivo ahora" de la página
 * del pedido (con sesión de admin) hace lo mismo en el servidor.
 */
class GenerarPedidoMedida extends BaseCommand
{
    protected $group       = 'Background Tasks';
    protected $name        = 'pedido:generar';
    protected $description = 'Genera el CSV de un pedido a medida para que la descarga sea inmediata.';
    protected $usage       = 'pedido:generar [token]';

    public function run(array $params)
    {
        $svc    = new PedidoMedidaService();
        $tokens = !empty($params[0]) ? [$params[0]] : array_keys(config('PedidosMedida')->pedidos);

        foreach ($tokens as $token) {
            $pedido = $svc->pedido($token);
            if (!$pedido) {
                CLI::error("Pedido no encontrado: {$token}");
                continue;
            }

            CLI::write("Generando {$pedido['referencia']}…", 'yellow');
            $inicio = microtime(true);

            try {
                $f = $svc->generarFichero($pedido);
            } catch (\Throwable $e) {
                CLI::error('  Error: ' . $e->getMessage());
                continue;
            }

            CLI::write(sprintf(
                '  %s empresas (acordadas: %s) · %s MB · %.1f s',
                number_format($f['filas'], 0, ',', '.'),
                number_format((int) $pedido['empresas_acordadas'], 0, ',', '.'),
                number_format($f['bytes'] / 1048576, 1, ',', '.'),
                microtime(true) - $inicio
            ), 'green');
            CLI::write('  Columnas: facturación=' . ($f['columnas']['ventas'] ?: 'NO ENCONTRADA')
                . ' · año cuentas=' . ($f['columnas']['anio'] ?: 'NO ENCONTRADA')
                . ' · CP=' . ($f['columnas']['postal_code'] ?: 'NO ENCONTRADA'));
            CLI::write('  ' . $f['ruta']);
        }
    }
}
