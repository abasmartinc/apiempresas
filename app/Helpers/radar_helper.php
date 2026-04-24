<?php

if (!function_exists('getRadarRedirect')) {
    /**
     * Devuelve la URL de redirección correcta para el Radar B2B
     * basada en el estado de autenticación del usuario y el origen (source).
     */
    function getRadarRedirect($source = 'direct')
    {
        // El source nos permite trackear el punto exacto de conversión
        $params = [
            'source'   => $source,
            'utm_page' => 'leads_empresas_nuevas'
        ];

        if (session('logged_in')) {
            return site_url('radar') . '?' . http_build_query($params);
        }

        // Funnel optimizado: /radar/preview
        $url = site_url('radar/preview');

        return $url . '?' . http_build_query($params);
    }
}
