<?php

namespace App\Log\Handlers;

use App\Libraries\Errors\ErrorRecorder;
use CodeIgniter\Log\Handlers\BaseHandler;

/**
 * Manejador del logger: los log_message de nivel error o mas grave van tambien al registro propio ({@see ErrorRecorder}).
 * Son los errores que el codigo capturo y siguio (no llegan al manejador de excepciones).
 *
 * Las excepciones sin capturar NO se guardan aqui: CodeIgniter las escribe en el log con un formato fijo justo antes de llamar al
 * manejador de excepciones, y alli se guardan con todo el detalle. Si se guardaran tambien aqui, cada fallo saldria dos veces.
 * El fichero de log no cambia: este manejador va despues del FileHandler y siempre deja seguir.
 */
final class ErrorTrackingHandler extends BaseHandler
{
    public function handle($level, $message): bool
    {
        $texto = (string) $message;

        // Formato de Exceptions::exceptionHandler(): "Clase\De\Excepcion: mensaje\n[Method: ..., Route: ...]" y los "[Caused by] ...".
        $deExcepcion = str_starts_with($texto, '[Caused by] ')
            || preg_match('/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]*: .*\n\[Method: [^\]]*, Route: [^\]]*\]/s', $texto) === 1
            // BaseConnection vuelca el mysqli_sql_exception antes de lanzar la DatabaseException (con DBDebug): esa ya se guarda.
            || (str_starts_with($texto, 'mysqli_sql_exception: ') && self::dbDebug());

        if (! $deExcepcion) {
            ErrorRecorder::log((string) $level, $texto);
        }

        return true;
    }

    private static function dbDebug(): bool
    {
        try {
            return (bool) (config(\Config\Database::class)->default['DBDebug'] ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
