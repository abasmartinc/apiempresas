<?php

namespace App\Libraries;

/**
 * Trabajo que se hace DESPUÉS de responder al usuario (correos del alta, avisos).
 *
 * El alta enviaba dos o tres correos por SMTP antes de redirigir: el usuario
 * esperaba varios segundos en "Creando cuenta…". Con esto la respuesta sale primero
 * y los correos se envían a continuación, en la misma petición.
 *
 *   Despues::hacer(function () use ($datos) { ...enviar correos... });
 *
 * Cada tarea va en su propio try: un fallo queda en el log y no afecta a las demás.
 */
class Despues
{
    /** @var callable[] */
    private static array $tareas = [];

    private static bool $registrado = false;

    public static function hacer(callable $tarea): void
    {
        self::$tareas[] = $tarea;
        if (!self::$registrado) {
            self::$registrado = true;
            register_shutdown_function([self::class, 'ejecutar']);
        }
    }

    /** Lo llama PHP al terminar la petición, con la respuesta ya generada. */
    public static function ejecutar(): void
    {
        if (empty(self::$tareas)) {
            return;
        }
        $tareas       = self::$tareas;
        self::$tareas = [];

        try {
            // Soltar la sesión: si no, la página a la que se redirige al usuario se
            // queda esperando a que termine esta petición.
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            ignore_user_abort(true);
            @set_time_limit(120);

            // Cerrar la respuesta hacia el navegador
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();           // PHP-FPM
            } elseif (function_exists('litespeed_finish_request')) {
                litespeed_finish_request();         // LiteSpeed
            } else {
                while (ob_get_level() > 0) {        // mod_php
                    @ob_end_flush();
                }
                flush();
            }
        } catch (\Throwable $e) {
            // Si no se puede cerrar antes, las tareas se hacen igualmente
        }

        foreach ($tareas as $tarea) {
            try {
                $tarea();
            } catch (\Throwable $e) {
                log_message('error', '[Despues] Tarea fallida: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            }
        }
    }
}
