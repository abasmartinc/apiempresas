<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\EmailService;

/**
 * Deja una vigilancia lista para que `alerts:borme` mande un correo de verdad.
 *
 * Probar la alerta a mano exige encadenar cuatro cosas que fallan por separado: que
 * el usuario pueda recibir correos, que tenga una vigilancia activa, que la empresa
 * vigilada tenga actos en `borme_posts`, y que la marca de agua esté por debajo de
 * ellos. Cada vez que una falla, el comando de alertas dice "Nada que avisar" y hay
 * que adivinar cuál de las cuatro era.
 *
 * Este comando comprueba las cuatro, arregla lo que se puede arreglar sin mentir
 * sobre los datos, y termina imprimiendo el comando exacto que hay que lanzar, ya
 * con la ventana de días calculada para que los actos pendientes entren dentro.
 *
 * DEJA PENDIENTES UNOS POCOS ACTOS, NO EL HISTÓRICO ENTERO. Poner la marca a 0 en
 * una empresa con veinte años de boletín genera un correo de 26 actos que no se
 * parece en nada al que va a recibir un usuario real, y así no se puede juzgar el
 * diseño.
 *
 * Uso:
 *   php spark alerts:borme-preparar --user=229
 *   php spark alerts:borme-preparar --user=229 --actos=5
 *   php spark alerts:borme-preparar --user=229 --grave      (busca concurso/disolución)
 *   php spark alerts:borme-preparar --user=229 --cif=B12345678
 */
class BormeAlertsPrepararCommand extends BaseCommand
{
    protected $group       = 'Alertas';
    protected $name        = 'alerts:borme-preparar';
    protected $description = 'Prepara una vigilancia del usuario para poder probar el envío real de la alerta BORME.';
    protected $usage       = 'alerts:borme-preparar --user ID [--cif CIF] [--actos N] [--grave] [--cualquiera] [--force]';

    /** Actos que se dejan pendientes por defecto: un correo realista, no un volcado. */
    private const ACTOS_POR_DEFECTO = 3;

    protected $db;

    public function run(array $params)
    {
        $this->db = \Config\Database::connect();

        if (ENVIRONMENT === 'production' && $this->opcion('force') === null) {
            CLI::error('Esto escribe en user_company_watch. En producción, solo con --force.');
            return;
        }

        $userId = (int) ($this->opcion('user') ?: 0);
        if ($userId <= 0) {
            CLI::error('Falta --user=ID.');
            return;
        }

        $nActos = max(1, (int) ($this->opcion('actos') ?: self::ACTOS_POR_DEFECTO));
        $cifFijo = strtoupper(trim((string) ($this->opcion('cif') ?: '')));

        CLI::write('=== Preparar prueba de alerta BORME ===', 'yellow');

        if (!$this->revisarUsuario($userId)) {
            return;
        }

        $empresa = $cifFijo !== ''
            ? $this->empresaPorCif($cifFijo)
            : $this->elegirEmpresa($userId);

        if ($empresa === null) {
            return;
        }

        $this->asegurarVigilancia($userId, $empresa);
        $this->colocarMarca($userId, $empresa, $nActos);
    }

    // -----------------------------------------------------------------------
    // 1) El usuario
    // -----------------------------------------------------------------------

    /**
     * Comprueba lo que haría que el correo se descartara en silencio. `alerts_borme`
     * se corrige, porque es una preferencia y estamos probando justo eso; `source_app`
     * NO se toca: identifica de qué producto viene el usuario y falsearlo aquí
     * escondería un problema real de alta.
     */
    private function revisarUsuario(int $userId): bool
    {
        $u = $this->db->table('users')
            ->select('id, email, name, is_admin, alerts_borme, unsuscribe, source_app')
            ->where('id', $userId)->get()->getRowArray();

        if ($u === null) {
            CLI::error("No existe el usuario {$userId}.");
            return false;
        }

        CLI::write("  Usuario: {$u['email']} (" . ($u['name'] ?: 'sin nombre') . ')');

        if ((int) $u['is_admin'] === 1) {
            CLI::write('  - Es admin: el cron diario lo excluye, pero --user sí lo incluye.', 'yellow');
        }

        if (($u['source_app'] ?? '') !== 'apiempresas') {
            CLI::error("  - source_app = '" . ($u['source_app'] ?? 'NULL') . "'. El envío exige 'apiempresas'.");
            CLI::write('    No lo cambio yo: eso identifica de qué producto viene el usuario.');
            return false;
        }

        $bloquea = $u['alerts_borme'] !== null
            ? (int) $u['alerts_borme'] === 0
            : (int) ($u['unsuscribe'] ?? 0) === 1;

        if ($bloquea) {
            $this->db->table('users')->where('id', $userId)->update(['alerts_borme' => 1]);
            CLI::write('  - Tenía las alertas desactivadas. Puesto alerts_borme = 1.', 'green');
        }

        return true;
    }

    // -----------------------------------------------------------------------
    // 2) La empresa
    // -----------------------------------------------------------------------

    /**
     * Prefiere, por este orden: una empresa que el usuario YA vigila y tenga actos,
     * y dentro de esas una con un acto grave si se pidió --grave. Solo si no hay
     * ninguna vigilada con actos se sale a buscar fuera, y aun así hay que pedirlo
     * con --cualquiera: dar de alta una vigilancia que el usuario nunca pidió es
     * justo lo que este producto dejó de hacer a propósito.
     */
    private function elegirEmpresa(int $userId): ?array
    {
        $vigiladas = $this->db->query("
            SELECT c.id, c.cif, c.company_name, COUNT(b.id) AS actos, MAX(b.borme_date) AS ultima
            FROM user_company_watch w
            JOIN companies c   ON c.id = w.company_id
            JOIN borme_posts b ON b.company_id = c.id
            WHERE w.user_id = ?
            GROUP BY c.id, c.cif, c.company_name
            ORDER BY actos DESC
        ", [$userId])->getResultArray();

        if ($vigiladas !== []) {
            CLI::write('  Empresas vigiladas con actos en el BORME: ' . count($vigiladas));
            $elegida = $this->preferirGrave($vigiladas);
            if ($elegida !== null) {
                return $elegida;
            }
        } else {
            CLI::write('  El usuario no vigila ninguna empresa que tenga actos cargados.', 'yellow');
        }

        if ($this->opcion('cualquiera') === null) {
            CLI::write('');
            CLI::error('Nada que preparar sin inventarse una vigilancia.');
            CLI::write('  Opciones:');
            CLI::write('   a) Pulsa "Vigilar" en la ficha de una empresa con actos y repite.');
            CLI::write('   b) Lanza esto con --cualquiera y te doy de alta la mejor candidata.');
            CLI::write('   c) Fíjala tú con --cif=XXXXXXXXX.');
            $this->sugerirCandidatas();
            return null;
        }

        $fuera = $this->db->query("
            SELECT c.id, c.cif, c.company_name, COUNT(b.id) AS actos, MAX(b.borme_date) AS ultima
            FROM companies c
            JOIN borme_posts b ON b.company_id = c.id
            GROUP BY c.id, c.cif, c.company_name
            ORDER BY actos DESC
            LIMIT 40
        ")->getResultArray();

        if ($fuera === []) {
            CLI::error('  La tabla borme_posts no tiene ningún acto ligado a una empresa.');
            return null;
        }

        return $this->preferirGrave($fuera) ?? $fuera[0];
    }

    /**
     * Con --grave busca una empresa cuyo texto de acto dispare el aviso rojo del
     * correo, usando el MISMO clasificador que usa EmailService. Si no la encuentra
     * lo dice y sigue con la primera: mejor un correo rutinario que ninguno.
     */
    private function preferirGrave(array $candidatas): ?array
    {
        if ($this->opcion('grave') === null) {
            return $candidatas[0] ?? null;
        }

        foreach ($candidatas as $c) {
            $actos = $this->db->table('borme_posts')
                ->select('act_types, description')
                ->where('company_id', (int) $c['id'])
                ->orderBy('id', 'DESC')->limit(60)->get()->getResultArray();

            foreach ($actos as $a) {
                $d = EmailService::actoDestacado(
                    trim((string) ($a['act_types'] ?? '') . ' ' . (string) ($a['description'] ?? ''))
                );
                if ($d !== null && $d['grave']) {
                    CLI::write("  Acto grave encontrado en {$c['company_name']}: {$d['label']}", 'green');
                    return $c;
                }
            }
        }

        CLI::write('  Ninguna candidata tiene actos graves: el correo saldrá sin recuadro rojo.', 'yellow');
        return $candidatas[0] ?? null;
    }

    private function empresaPorCif(string $cif): ?array
    {
        $c = $this->db->query("
            SELECT c.id, c.cif, c.company_name, COUNT(b.id) AS actos, MAX(b.borme_date) AS ultima
            FROM companies c
            LEFT JOIN borme_posts b ON b.company_id = c.id
            WHERE c.cif = ?
            GROUP BY c.id, c.cif, c.company_name
        ", [$cif])->getRowArray();

        if ($c === null) {
            CLI::error("  No hay ninguna empresa con CIF {$cif}.");
            return null;
        }
        if ((int) $c['actos'] === 0) {
            CLI::error("  {$c['company_name']} no tiene ningún acto en borme_posts: con ella no hay correo posible.");
            $this->sugerirCandidatas();
            return null;
        }

        return $c;
    }

    private function sugerirCandidatas(): void
    {
        $filas = $this->db->query("
            SELECT c.cif, c.company_name, COUNT(b.id) AS actos, MAX(b.borme_date) AS ultima
            FROM companies c
            JOIN borme_posts b ON b.company_id = c.id
            GROUP BY c.cif, c.company_name
            ORDER BY ultima DESC, actos DESC
            LIMIT 8
        ")->getResultArray();

        if ($filas === []) {
            return;
        }

        CLI::write('');
        CLI::write('  Empresas con movimiento más reciente:', 'yellow');
        foreach ($filas as $f) {
            CLI::write(sprintf('   %-12s %-42s %3d actos · último %s',
                $f['cif'], mb_substr((string) $f['company_name'], 0, 42), (int) $f['actos'], $f['ultima']));
        }
    }

    // -----------------------------------------------------------------------
    // 3) La vigilancia y la marca de agua
    // -----------------------------------------------------------------------

    private function asegurarVigilancia(int $userId, array $empresa): void
    {
        $fila = $this->db->table('user_company_watch')
            ->where('user_id', $userId)->where('company_id', (int) $empresa['id'])
            ->get()->getRowArray();

        $ahora = date('Y-m-d H:i:s');

        if ($fila === null) {
            $this->db->table('user_company_watch')->insert([
                'user_id'       => $userId,
                'cif'           => $empresa['cif'],
                'company_id'    => (int) $empresa['id'],
                'source'        => 'manual',
                'last_borme_id' => 0,
                'active'        => 1,
                'created_at'    => $ahora,
                'updated_at'    => $ahora,
            ]);
            CLI::write("  - Vigilancia creada sobre {$empresa['company_name']}.", 'green');
            return;
        }

        if ((int) $fila['active'] !== 1 || empty($fila['company_id'])) {
            $this->db->table('user_company_watch')->where('id', (int) $fila['id'])->update([
                'active'     => 1,
                'company_id' => (int) $empresa['id'],
                'updated_at' => $ahora,
            ]);
            CLI::write('  - Vigilancia reactivada y con company_id resuelto.', 'green');
            return;
        }

        CLI::write("  - Vigilancia ya existente sobre {$empresa['company_name']}.");
    }

    /**
     * Coloca la marca justo por debajo de los N actos más recientes.
     *
     * El cruce del comando de alertas es `id > last_borme_id` Y `borme_date >= corte`,
     * así que además de bajar la marca hay que decir cuántos días de ventana hacen
     * falta para que el acto más viejo de los pendientes siga contando como noticia.
     * Ese número es lo que casi siempre se olvida y deja "actos candidatos: 0".
     */
    private function colocarMarca(int $userId, array $empresa, int $nActos): void
    {
        $recientes = $this->db->table('borme_posts')
            ->select('id, borme_date')
            ->where('company_id', (int) $empresa['id'])
            ->orderBy('id', 'DESC')->limit($nActos)
            ->get()->getResultArray();

        if ($recientes === []) {
            CLI::error('  Sin actos con los que trabajar.');
            return;
        }

        $masAntiguo = end($recientes);
        // La marca va en el id inmediatamente inferior al más antiguo que queremos
        // que salga: así quedan pendientes exactamente esos N y ni uno más.
        $marca = max(0, (int) $masAntiguo['id'] - 1);

        $this->db->table('user_company_watch')
            ->where('user_id', $userId)->where('company_id', (int) $empresa['id'])
            ->update(['last_borme_id' => $marca, 'updated_at' => date('Y-m-d H:i:s')]);

        $fechaAntigua = (string) ($masAntiguo['borme_date'] ?? '');
        $dias = $fechaAntigua !== ''
            ? max(7, (int) ceil((time() - strtotime($fechaAntigua)) / 86400) + 1)
            : 7;

        CLI::write('');
        CLI::write('  LISTO', 'green');
        CLI::write("   Empresa ....... {$empresa['company_name']} ({$empresa['cif']})");
        CLI::write('   Actos pendientes ' . count($recientes) . " (marca puesta en {$marca})");
        CLI::write("   Más antiguo ... {$fechaAntigua}");
        CLI::write('');

        if ($dias > 400) {
            CLI::write('  Aviso: el acto más reciente de esta empresa es viejo, así que la ventana', 'yellow');
            CLI::write('  sale enorme. El correo será válido, pero un usuario real nunca lo recibiría así.');
        }

        CLI::write('  Ahora lanza:', 'yellow');
        CLI::write("   php spark alerts:borme --user {$userId} --days {$dias} --force --dry-run");
        CLI::write("   php spark alerts:borme --user {$userId} --days {$dias} --force");
    }

    /** Mismo lector de opciones que alerts:borme, para que --user=1 y --user 1 valgan igual. */
    private function opcion(string $nombre)
    {
        $valor = CLI::getOption($nombre);
        if ($valor !== null && $valor !== true) {
            return $valor;
        }

        foreach (array_keys(CLI::getOptions()) as $clave) {
            if (strpos((string) $clave, $nombre . '=') === 0) {
                return substr((string) $clave, strlen($nombre) + 1);
            }
        }

        return $valor;
    }
}
