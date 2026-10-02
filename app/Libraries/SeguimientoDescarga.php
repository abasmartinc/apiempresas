<?php

namespace App\Libraries;

use App\Services\ListadoPagadoService;

/**
 * Sigue una descarga de listado pagado de principio a fin.
 *
 *   $seg = new SeguimientoDescarga($sessionId, 'csv');
 *   … se escribe el archivo, llamando a $seg->filas($n) …
 *   $seg->ok();
 *
 * Si el script termina sin llegar a ok() —excepción, memoria agotada, tiempo máximo o el
 * cliente corta la conexión— lo detecta al cerrar (register_shutdown_function), lo
 * apunta en la compra y, si el fallo es nuestro, avisa por correo.
 */
class SeguimientoDescarga
{
    private bool $cerrada = false;
    private int $filas = 0;

    public function __construct(private string $sessionId, private string $formato)
    {
        register_shutdown_function([$this, 'alCerrar']);
    }

    public function filas(int $total): void
    {
        $this->filas = $total;
    }

    public function formato(string $formato): void
    {
        $this->formato = $formato;
    }

    /** Archivo entregado completo */
    public function ok(?int $filas = null): void
    {
        if ($this->cerrada) {
            return;
        }
        $this->cerrada = true;
        if ($filas !== null) {
            $this->filas = $filas;
        }
        $this->apuntar('ok', '');
    }

    /** Fallo detectado por quien llama (excepción capturada) */
    public function error(string $detalle): void
    {
        if ($this->cerrada) {
            return;
        }
        $this->cerrada = true;
        $this->apuntar('error', $detalle);
    }

    /** Lo llama PHP al terminar el script, haya pasado lo que haya pasado */
    public function alCerrar(): void
    {
        if ($this->cerrada) {
            return;
        }
        $this->cerrada = true;

        $e = error_get_last();
        if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR], true)) {
            $this->apuntar('error', $e['message'] . ' (' . basename((string) $e['file']) . ':' . $e['line'] . ')');
        } elseif (connection_status() & CONNECTION_TIMEOUT) {
            $this->apuntar('error', 'Tiempo máximo de ejecución agotado');
        } elseif (connection_aborted()) {
            $this->apuntar('cortada', 'El cliente cerró la conexión antes de terminar');
        } else {
            $this->apuntar('error', 'El script terminó sin completar el archivo');
        }
    }

    private function apuntar(string $estado, string $detalle): void
    {
        try {
            $svc = new ListadoPagadoService();
            $svc->registrarDescarga($this->sessionId, $estado, $this->formato, $this->filas, $detalle);
            if ($estado === 'error') {
                log_message('error', "[Descarga] Fallo en {$this->formato} de {$this->sessionId} tras {$this->filas} filas: {$detalle}");
                $svc->avisarFallo($this->sessionId, $this->formato, $this->filas, $detalle);
            }
        } catch (\Throwable $e) {
            // El seguimiento nunca debe romper una descarga
        }
    }
}
