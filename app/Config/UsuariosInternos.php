<?php

namespace Config;

/**
 * Cuentas internas: las de Adrian y las de pruebas. No son clientes, asi que no deben contar en ninguna estadistica ni
 * listado de analitica (crecimiento, correos, analitica de la API, embudo). Siguen apareciendo donde se gestionan cuentas
 * (usuarios, facturas) y en el registro de errores (un fallo que provoca una prueba es un fallo real).
 *
 * Para anadir otra cuenta, basta con poner su id aqui.
 */
class UsuariosInternos
{
    public const IDS = [
        229, // contacto@freelance-programador.com (Adrian)
        376, // status_monitor@apiempresas.es (monitor de estado: llama a la API para comprobar que funciona)
    ];

    /** Condicion SQL "no es una cuenta interna" para una columna de usuario (las filas sin usuario se quedan). */
    public static function sinInternos(string $col = 'user_id'): string
    {
        return '(' . $col . ' IS NULL OR ' . $col . ' NOT IN (' . implode(',', array_map('intval', self::IDS)) . '))';
    }

    public static function es(?int $userId): bool
    {
        return $userId !== null && in_array($userId, self::IDS, true);
    }
}
