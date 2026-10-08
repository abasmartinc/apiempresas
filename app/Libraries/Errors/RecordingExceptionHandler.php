<?php

namespace App\Libraries\Errors;

use CodeIgniter\Debug\ExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Exceptions as ExceptionsConfig;
use Throwable;

/**
 * El manejador de excepciones de CodeIgniter, igual que siempre (pantalla de error, codigo de salida), pero antes guarda el error
 * en el registro propio ({@see ErrorRecorder}). Envuelve al de CodeIgniter en vez de extenderlo: esa clase es final.
 * Los 4xx no se guardan (404, 400 por caracteres prohibidos en la URL, 405...): son peticiones malas del cliente -- enlaces viejos
 * y bots buscando ".env" y similares --, no fallos de la aplicacion. Solo los 5xx (y lo que no traiga un 4xx) llegan al registro.
 */
final class RecordingExceptionHandler implements ExceptionHandlerInterface
{
    public function __construct(private ExceptionsConfig $config)
    {
    }

    public function handle(Throwable $exception, RequestInterface $request, ResponseInterface $response, int $statusCode, int $exitCode): void
    {
        if (! ($statusCode >= 400 && $statusCode < 500) && ! $exception instanceof PageNotFoundException) {
            ErrorRecorder::exception($exception, $statusCode);
        }

        (new ExceptionHandler($this->config))->handle($exception, $request, $response, $statusCode, $exitCode);
    }
}
