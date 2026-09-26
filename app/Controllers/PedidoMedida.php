<?php

namespace App\Controllers;

use App\Services\PedidoMedidaService;

/**
 * Enlace de compra de un pedido a medida (Config\PedidosMedida).
 *
 *   GET  pedido/{token}             resumen y botón de pago (o la descarga, si ya está pagado)
 *   POST pedido/{token}/pagar       abre Stripe Checkout
 *   GET  pedido/{token}/gracias     vuelta de Stripe: confirma el cobro y enseña la descarga
 *   GET  pedido/{token}/descargar   CSV (pide la sesión de Stripe cobrada; un admin lo baja sin pagar)
 *   POST pedido/{token}/generar     solo admin: genera el CSV y lo deja guardado (writable/pedidos)
 *
 * No pide iniciar sesión: el cliente llega desde un ticket o un correo y no
 * tiene por qué recordar la contraseña. La factura y el pago van a su usuario
 * (user_id del pedido) porque viaja en los metadatos de Stripe.
 */
class PedidoMedida extends BaseController
{
    private PedidoMedidaService $svc;

    public function __construct()
    {
        $this->svc = new PedidoMedidaService();
    }

    public function index(string $token)
    {
        $pedido = $this->svc->pedido($token);
        if (!$pedido) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $pago = $this->svc->pago($token);

        return $this->renderView('billing/pedido_medida', [
            'pedido'    => $pedido,
            'usuario'   => (new \App\Models\UserModel())->find((int) $pedido['user_id']),
            'pago'      => $pago,
            'descarga'  => $pago ? $this->urlDescarga($token, $pago['session_id']) : null,
            'caducado'  => $this->svc->caducado($pedido),
            'total'     => $this->contarConCache($pedido),
            'columnas'  => $this->svc->columnasDisponibles(),
            'fichero'   => $this->svc->fichero($token),
            'es_admin'  => (bool) session('is_admin'),
        ]);
    }

    public function pagar(string $token)
    {
        $pedido = $this->svc->pedido($token);
        if (!$pedido) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ya pagado: no se cobra dos veces
        if ($this->svc->pago($token)) {
            return redirect()->to(site_url("pedido/{$token}"));
        }

        if ($this->svc->caducado($pedido)) {
            return redirect()->to(site_url("pedido/{$token}"))
                ->with('error', 'Este enlace de pago ha caducado. Escríbenos a soporte@apiempresas.es y te mandamos uno nuevo.');
        }

        // Entorno local: sin pasar por Stripe
        if (env('BILLING_MODE') === 'simulator') {
            $sid = 'sim_' . bin2hex(random_bytes(8));
            $this->svc->registrarPago($token, $sid);
            return redirect()->to(site_url("pedido/{$token}/gracias") . '?session_id=' . $sid);
        }

        try {
            return redirect()->to($this->svc->crearCheckout($pedido));
        } catch (\Throwable $e) {
            log_message('error', '[PedidoMedida::pagar] ' . $pedido['referencia'] . ' · ' . $e->getMessage());
            return redirect()->to(site_url("pedido/{$token}"))->with(
                'error',
                'No hemos podido abrir la pasarela de pago. Vuelve a intentarlo en un momento; si sigue fallando, escríbenos a soporte@apiempresas.es.'
            );
        }
    }

    public function gracias(string $token)
    {
        $pedido = $this->svc->pedido($token);
        if (!$pedido) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $sid    = (string) $this->request->getGet('session_id');
        $sesion = $this->svc->sesionCobrada($token, $sid);

        if (!$sesion) {
            // Pago aún no confirmado (o cancelado): de vuelta al resumen
            return redirect()->to(site_url("pedido/{$token}"))->with(
                'error',
                'Todavía no nos consta el pago. Si lo acabas de completar, recarga esta página en un minuto; si lo cancelaste, no se te ha cobrado nada.'
            );
        }

        if (is_object($sesion)) {
            $this->svc->registrarPago(
                $token,
                $sid,
                (string) ($sesion->customer_details->email ?? ''),
                isset($sesion->amount_total) ? (int) $sesion->amount_total : null
            );
        }

        return redirect()->to(site_url("pedido/{$token}"))->with('pagado', true);
    }

    public function descargar(string $token)
    {
        $pedido = $this->svc->pedido($token);
        if (!$pedido) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $sid = (string) $this->request->getGet('session_id');
        $permitido = (bool) session('is_admin') || $this->svc->sesionCobrada($token, $sid);

        if (!$permitido) {
            return redirect()->to(site_url("pedido/{$token}"))
                ->with('error', 'El enlace de descarga no es válido. Usa el del correo de confirmación o escríbenos a soporte@apiempresas.es.');
        }

        // Lo normal es que el archivo ya esté generado (botón de admin o
        // `php spark pedido:generar`). Si no lo está, se genera ahora y se guarda:
        // la siguiente descarga ya es inmediata.
        $fichero = $this->svc->fichero($token);
        if (!$fichero) {
            try {
                $fichero = $this->svc->generarFichero($pedido);
            } catch (\Throwable $e) {
                log_message('error', '[PedidoMedida::descargar] ' . $e->getMessage());
                return redirect()->to(site_url("pedido/{$token}"))
                    ->with('error', 'No hemos podido preparar el archivo. Escríbenos a soporte@apiempresas.es y te lo enviamos enseguida.');
            }
        }

        log_message('info', "[PedidoMedida] Descarga {$pedido['referencia']}: {$fichero['filas']} empresas" . (session('is_admin') ? ' (admin)' : ''));

        return $this->response
            ->download($fichero['ruta'], null)
            ->setFileName($this->svc->nombreFichero($pedido))
            ->setContentType('text/csv; charset=utf-8')
            ->setHeader('X-Robots-Tag', 'noindex');
    }

    /** POST pedido/{token}/generar — solo admin: genera (o regenera) el archivo. */
    public function generar(string $token)
    {
        $pedido = $this->svc->pedido($token);
        if (!$pedido) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        if (!session('is_admin')) {
            return redirect()->to(site_url("pedido/{$token}"));
        }

        try {
            $f = $this->svc->generarFichero($pedido);
            return redirect()->to(site_url("pedido/{$token}"))->with(
                'aviso',
                'Archivo generado: ' . number_format($f['filas'], 0, ',', '.') . ' empresas, '
                . number_format($f['bytes'] / 1048576, 1, ',', '.') . ' MB.'
            );
        } catch (\Throwable $e) {
            log_message('error', '[PedidoMedida::generar] ' . $e->getMessage());
            return redirect()->to(site_url("pedido/{$token}"))->with('error', 'No se pudo generar el archivo: ' . $e->getMessage());
        }
    }

    private function urlDescarga(string $token, string $sid): string
    {
        return site_url("pedido/{$token}/descargar") . '?session_id=' . urlencode($sid);
    }

    /** El recuento cuesta una consulta sobre companies: 1 h de caché. */
    private function contarConCache(array $pedido): int
    {
        $clave = 'pedido_medida_total_' . $pedido['token'];
        $n = cache($clave);
        if ($n === null) {
            try {
                $n = $this->svc->contar($pedido);
                cache()->save($clave, $n, 3600);
            } catch (\Throwable $e) {
                log_message('error', '[PedidoMedida] recuento: ' . $e->getMessage());
                $n = (int) $pedido['empresas_acordadas'];
            }
        }
        return (int) $n;
    }
}
