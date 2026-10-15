<?php

namespace App\Controllers;

use App\Libraries\Valoraciones as V;

/**
 * Valoraciones de clientes (14-10-2026). Ver App\Libraries\Valoraciones.
 *
 *  POST dashboard/valoracion  tarjeta del panel (sesión iniciada)
 *  GET  valorar               página del correo: ?u=ID&r=1..5&t=FIRMA (no guarda nada)
 *  POST valorar               guarda la valoración del correo
 *
 * El GET no guarda a propósito: los filtros antispam abren los enlaces de los correos
 * y, si guardara, cada correo dejaría una valoración que nadie ha dado.
 */
class Valoraciones extends BaseController
{
    public function panel()
    {
        if (!session('logged_in')) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false]);
        }
        $uid = (int) session('user_id');

        if ($this->request->getPost('accion') === 'descartar') {
            V::descartar($uid);
            return $this->response->setJSON(['ok' => true]);
        }

        $rating = (int) $this->request->getPost('rating');
        if ($rating < 1 || $rating > 5) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false]);
        }

        $ok = V::guardar(
            $uid,
            $rating,
            (string) $this->request->getPost('comment'),
            (string) $this->request->getPost('citar') === '1',
            'panel',
            $this->request->getIPAddress()
        );

        return $this->response->setJSON(['ok' => $ok]);
    }

    public function formulario()
    {
        $uid    = (int) $this->request->getGet('u');
        $firma  = (string) $this->request->getGet('t');
        $rating = max(0, min(5, (int) $this->request->getGet('r')));

        return $this->pagina($uid, $firma, $rating, V::firmaValida($uid, $firma) ? 'form' : 'invalido');
    }

    public function enviar()
    {
        $uid    = (int) $this->request->getPost('u');
        $firma  = (string) $this->request->getPost('t');
        $rating = (int) $this->request->getPost('rating');

        if (!V::firmaValida($uid, $firma)) {
            return $this->pagina($uid, $firma, 0, 'invalido');
        }
        if ($rating < 1 || $rating > 5) {
            return $this->pagina($uid, $firma, 0, 'form');
        }

        V::guardar(
            $uid,
            $rating,
            (string) $this->request->getPost('comment'),
            (string) $this->request->getPost('citar') === '1',
            'correo',
            $this->request->getIPAddress()
        );

        return $this->pagina($uid, $firma, $rating, 'gracias');
    }

    private function pagina(int $uid, string $firma, int $rating, string $estado)
    {
        $lang = 'es';
        $nombre = '';
        if ($estado !== 'invalido') {
            $u = \Config\Database::connect()->table('users')->select('name, lang')
                ->where('id', $uid)->get()->getRow();
            if ($u) {
                $lang   = ($u->lang ?? '') === 'en' ? 'en' : 'es';
                $nombre = trim((string) ($u->name ?? ''));
            }
        }

        $this->response->setHeader('Cache-Control', 'no-store, private');
        $this->response->setHeader('X-Robots-Tag', 'noindex, nofollow');

        return view('valorar', [
            'estado' => $estado,
            'uid'    => $uid,
            'firma'  => $firma,
            'rating' => $rating,
            'lang'   => $lang,
            'nombre' => $nombre,
        ]);
    }
}
