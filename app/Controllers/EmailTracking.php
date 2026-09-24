<?php

namespace App\Controllers;

use App\Models\EmailLogModel;
use CodeIgniter\Controller;

class EmailTracking extends Controller
{
    /**
     * Track email opening
     */
    public function open($code)
    {
        log_message('debug', 'Email open tracking request. Code: ' . $code);
        $model = new EmailLogModel();
        $log = $model->where('tracking_code', $code)->first();

        if ($log && is_null($log->opened_at)) {
            $model->update($log->id, [
                'opened_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Return a 1x1 transparent GIF
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return $this->response
            ->setHeader('Content-Type', 'image/gif')
            ->setHeader('Content-Length', strlen($img))
            ->setBody($img);
    }

    /**
     * Track email link clicks
     */
    public function click($code)
    {
        log_message('debug', 'Email click tracking request. Code: ' . $code);
        $model = new EmailLogModel();
        $log = $model->where('tracking_code', $code)->first();

        if ($log) {
            log_message('debug', 'Log found for code: ' . $code);
            $updateData = [];
            if (is_null($log->opened_at)) {
                $updateData['opened_at'] = date('Y-m-d H:i:s');
            }
            if (is_null($log->clicked_at)) {
                $updateData['clicked_at'] = date('Y-m-d H:i:s');
            }
            
            if (!empty($updateData)) {
                $model->update($log->id, $updateData);
            }

            // Set session for login tracking
            session()->set('email_tracking_code', $code);
        }

        $url = (string) ($this->request->getGet('t') ?: site_url('enter'));

        // Solo se redirige a nuestra propia web. Antes cualquier ?t= valía: un enlace
        // /e/c/x?t=https://otra-web servía para mandar a alguien fuera con nuestro dominio.
        $host    = strtolower((string) parse_url($url, PHP_URL_HOST));
        $propios = array_filter([strtolower((string) parse_url(site_url(), PHP_URL_HOST)), 'apiempresas.es', 'www.apiempresas.es']);
        if ($host !== '' && !in_array($host, $propios, true)) {
            $url = site_url('enter');
        }

        // De qué correo viene: Billing lo usa como source del checkout si el
        // formulario no trae otro, para atribuir la compra al correo.
        parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
        if (!empty($q['source']) && str_starts_with((string) $q['source'], 'email_')) {
            session()->set('email_source', substr((string) $q['source'], 0, 100));
        }

        return redirect()->to($url);
    }
}
