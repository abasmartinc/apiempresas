<?php

namespace App\Filters;

use CodeIgniter\Filters\Honeypot;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;

/**
 * Honeypot sin excepción (14-10-2026).
 *
 * El filtro de CodeIgniter lanza HoneypotException cuando un bot rellena el campo oculto. Esa
 * excepción no se captura: el bot recibe un 500 y queda registrado como error crítico (13 en
 * /register/quick_store entre el 09-10 y el 11-10), aunque el filtro ha hecho bien su trabajo.
 *
 * Aquí se corta igual (el controlador no se ejecuta y no se crea ninguna cuenta), pero se
 * devuelve a la página anterior con un mensaje neutro y se registra como aviso. Si alguna vez es
 * una persona (autorrelleno del navegador), ve el mensaje y puede volver a intentarlo.
 * La parte "after" (añadir el campo oculto a los formularios) es la de CodeIgniter, sin cambios.
 */
class HoneypotFilter extends Honeypot
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        if (! service('honeypot')->hasContent($request)) {
            return null;
        }

        log_message('notice', '[honeypot] Envío descartado: {method} /{path} desde {ip} ({ua})', [
            'method' => strtoupper($request->getMethod()),
            'path'   => $request->getUri()->getPath(),
            'ip'     => $request->getIPAddress(),
            'ua'     => mb_substr((string) $request->getUserAgent(), 0, 120),
        ]);

        return redirect()->back()
            ->setStatusCode(303)
            ->with('error', 'No hemos podido procesar el formulario. Vuelve a intentarlo.');
    }
}
