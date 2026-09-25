<?php

namespace App\Libraries;

/**
 * Prueba social de Solvencia, calculada de la base de datos: cuántas empresas se
 * vigilan hoy y cuántos avisos del BORME se han enviado en los últimos 30 días.
 *
 * Nada de testimonios ni cifras redondeadas a mano: si los números reales todavía
 * son pequeños (por debajo de Config\Solvencia::$pruebaSocialMinimo) no se enseña
 * nada, porque "12 empresas vigiladas" resta en vez de sumar. Se guarda en caché
 * una hora: se pinta en páginas de mucho tráfico.
 */
class PruebaSocial
{
    /** @return array{vigiladas:int,avisos:int}|null */
    public static function datos(): ?array
    {
        helper('company');
        $minimo = (int) solvencia('pruebaSocialMinimo', 100);

        try {
            $datos = cache()->get('solvencia_prueba_social');
            if (!is_array($datos)) {
                $db = \Config\Database::connect();
                $vigiladas = (int) ($db->table('user_company_watch')
                    ->select('COUNT(DISTINCT cif) AS n', false)
                    ->where('active', 1)
                    ->get()->getRowArray()['n'] ?? 0);
                $avisos = (int) $db->table('user_email_automation')
                    ->where('email_type', 'borme_alert')
                    ->where('sent_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))
                    ->countAllResults();
                $datos = ['vigiladas' => $vigiladas, 'avisos' => $avisos];
                cache()->save('solvencia_prueba_social', $datos, 3600);
            }
        } catch (\Throwable $e) {
            log_message('error', '[PruebaSocial] ' . $e->getMessage());
            return null;
        }

        return ($datos['vigiladas'] ?? 0) >= $minimo ? $datos : null;
    }

    /** Frase lista para pintar, o cadena vacía si no hay cifras que enseñar. */
    public static function linea(): string
    {
        $d = self::datos();
        if ($d === null) {
            return '';
        }
        $n = static fn (int $x) => number_format($x, 0, ',', '.');
        $frase = 'Vigilamos ' . $n($d['vigiladas']) . ' empresas para nuestros usuarios';
        if ($d['avisos'] > 0) {
            $frase .= ' · ' . $n($d['avisos']) . ' ' . ($d['avisos'] === 1 ? 'aviso enviado' : 'avisos enviados') . ' en los últimos 30 días';
        }
        return $frase;
    }
}
