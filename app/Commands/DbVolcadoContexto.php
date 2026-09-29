<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Copia de la base de datos para análisis (solo lectura).
 *
 *   php spark db:volcado
 *   php spark db:volcado --max 100000      filas por tabla grande (por defecto 50.000)
 *
 * Usa la conexión del .env (la que tenga activa: producción o local) y escribe
 * writable/contexto/volcado.sql.gz con la estructura de TODAS las tablas y sus datos:
 *
 *  - Tablas normales: completas.
 *  - Tablas grandes (más filas que --max): la estructura y las últimas --max filas
 *    (por clave primaria). Con eso basta para entender el uso sin mover gigas.
 *  - Columnas sensibles (contraseñas, tokens, claves de API, secretos): se vacían
 *    (NULL). La copia sirve para analizar, no para entrar en cuentas.
 *
 * No modifica nada: solo SHOW y SELECT.
 */
class DbVolcadoContexto extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:volcado';
    protected $description = 'Copia de solo lectura de la BD (estructura + datos, sin secretos) en writable/contexto/volcado.sql.gz';
    protected $usage       = 'db:volcado [--max 50000]';

    /**
     * Columnas que nunca salen en la copia, por nombre. Ojo: no vale "cualquier *_hash":
     * event_hash o product_hash son huellas con índice único, no secretos, y vaciarlas
     * hacía que la copia no se pudiera cargar.
     */
    private const SENSIBLES = '/(pass(word)?|token|secret|api_?key|^key$|key_hash|^otp|2fa|totp|remember)/i';

    /** Tablas de las que solo se copia la estructura (sesiones: llevan datos de login) */
    private const SOLO_ESTRUCTURA = ['ci_sessions'];

    public function run(array $params)
    {
        $max = (int) (CLI::getOption('max') ?? 50000);
        if ($max <= 0) {
            $max = 50000;
        }

        $db      = \Config\Database::connect();
        $baseDir = WRITEPATH . 'contexto';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }
        $ruta = $baseDir . DIRECTORY_SEPARATOR . 'volcado.sql.gz';
        $gz   = gzopen($ruta, 'wb6');
        if (!$gz) {
            CLI::error('No se puede escribir ' . $ruta);
            return;
        }

        CLI::write('Base de datos: ' . $db->getDatabase() . ' en ' . $db->hostname, 'cyan');
        gzwrite($gz, "-- Volcado de contexto de " . $db->getDatabase() . " (" . date('Y-m-d H:i:s') . ")\n"
            . "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

        $tablas = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->getResultArray();
        $total  = count($tablas);

        foreach ($tablas as $i => $fila) {
            $tabla = (string) array_values($fila)[0];
            $n     = $i + 1;

            try {
                $crear = $db->query('SHOW CREATE TABLE `' . $tabla . '`')->getRowArray();
                gzwrite($gz, "DROP TABLE IF EXISTS `{$tabla}`;\n" . ($crear['Create Table'] ?? '') . ";\n\n");

                $filas = (int) ($db->query('SELECT COUNT(*) AS n FROM `' . $tabla . '`')->getRowArray()['n'] ?? 0);

                // Columnas y clave primaria
                $cols = $db->query('SHOW COLUMNS FROM `' . $tabla . '`')->getResultArray();
                $nombres = array_column($cols, 'Field');
                $pk = null;
                foreach ($cols as $c) {
                    if (($c['Key'] ?? '') === 'PRI') {
                        $pk = $pk === null ? $c['Field'] : false;   // false = clave compuesta
                    }
                }
                $ocultas = array_values(array_filter($nombres, static fn ($c) => preg_match(self::SENSIBLES, $c)));
                $select  = implode(', ', array_map(
                    static fn ($c) => in_array($c, $ocultas, true) ? 'NULL AS `' . $c . '`' : '`' . $c . '`',
                    $nombres
                ));

                $grande = $filas > $max;
                $copiar = in_array($tabla, self::SOLO_ESTRUCTURA, true) ? 0 : ($grande ? $max : $filas);
                CLI::write(sprintf('[%d/%d] %s: %s filas%s%s', $n, $total, $tabla, number_format($filas, 0, ',', '.'),
                    $grande ? ' → últimas ' . number_format($max, 0, ',', '.') : '',
                    $ocultas ? ' (sin ' . implode(', ', $ocultas) . ')' : ''));

                if ($copiar === 0) {
                    continue;
                }

                $orden   = is_string($pk) ? ' ORDER BY `' . $pk . '` DESC' : '';
                $lista   = '`' . implode('`, `', $nombres) . '`';
                $lote    = 2000;
                for ($desde = 0; $desde < $copiar; $desde += $lote) {
                    $cuantas = min($lote, $copiar - $desde);
                    $datos = $db->query("SELECT {$select} FROM `{$tabla}`{$orden} LIMIT {$desde}, {$cuantas}")->getResultArray();
                    if (empty($datos)) {
                        break;
                    }
                    $valores = [];
                    foreach ($datos as $d) {
                        $valores[] = '(' . implode(',', array_map(static fn ($v) => $v === null ? 'NULL' : $db->escape($v), $d)) . ')';
                    }
                    gzwrite($gz, "INSERT INTO `{$tabla}` ({$lista}) VALUES\n" . implode(",\n", $valores) . ";\n");
                }
                gzwrite($gz, "\n");
            } catch (\Throwable $e) {
                CLI::error("  {$tabla}: " . $e->getMessage());
                gzwrite($gz, "-- ERROR en {$tabla}: " . str_replace("\n", ' ', $e->getMessage()) . "\n\n");
            }
        }

        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);

        CLI::write('Listo: ' . $ruta . ' (' . number_format(filesize($ruta) / 1048576, 1, ',', '.') . ' MB)', 'green');
    }
}
