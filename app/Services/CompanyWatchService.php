<?php

namespace App\Services;

use Config\Database;

/**
 * Vigilancia de empresas (alertas de cambio en el BORME).
 *
 * La vigilancia se crea en el momento de una acción DELIBERADA del usuario
 * —desbloquear un dictamen, buscar un CIF, pulsar "Vigilar"— y nunca por el mero
 * hecho de que un bloque aparezca en pantalla.
 *
 * Esa distinción es la que evita el problema que tenía la versión anterior: como
 * la lista se derivaba de `user_events`, y a los suscriptores el dictamen se les
 * abre solo al hacer scroll, acumulaban decenas de empresas que nunca pidieron
 * seguir y acababan recibiendo correo casi a diario.
 */
class CompanyWatchService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Normaliza el CIF igual que el resto del flujo de riesgo.
     */
    protected function cleanCif(string $cif): string
    {
        return (new CompanyRiskService())->cleanCif($cif);
    }

    /**
     * ¿Tiene Solvencia Pro? Decide de qué cupo de vigilancia se tira.
     */
    public function esSuscriptor(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        try {
            $fila = $this->db->table('user_subscriptions us')
                ->select('us.id')
                ->join('api_plans ap', 'ap.id = us.plan_id')
                ->where('us.user_id', $userId)
                ->groupStart()
                    ->where('ap.product_type', 'risk')
                    ->orWhere('ap.product_type', 'bundle')
                    ->orWhere('ap.slug', 'risk_pro')
                ->groupEnd()
                ->groupStart()
                    ->where('us.status', 'active')
                    ->orGroupStart()
                        ->where('us.status', 'canceled')
                        ->where('us.current_period_end >', date('Y-m-d H:i:s'))
                    ->groupEnd()
                ->groupEnd()
                ->limit(1)
                ->get()->getRow();

            return $fila !== null;
        } catch (\Throwable $e) {
            log_message('error', '[CompanyWatchService] esSuscriptor: ' . $e->getMessage());
            return false;
        }
    }

    /** Empresas que este usuario vigila ahora mismo. */
    public function contarActivas(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        return $this->db->table('user_company_watch')
            ->where('user_id', $userId)
            ->where('active', 1)
            ->countAllResults();
    }

    /**
     * Estado del cupo de vigilancias.
     *
     * @return array{usadas:int,tope:int,es_pro:bool,ilimitado:bool,quedan:int,lleno:bool}
     */
    public function estadoCupo(int $userId): array
    {
        helper('company');

        // Los suscriptores también tienen tope: la cartera vigilada es el eje por
        // el que crece la cuenta. Sin él, quien vigila 400 empresas y quien vigila
        // 3 pagan lo mismo, y no hay nada que vender al que se queda pequeño.
        $esPro  = $this->esSuscriptor($userId);
        $tope   = $esPro
            ? (int) solvencia('vigilanciasPro', 25)
            : (int) solvencia('vigilanciasGratis', 5);
        $usadas = $this->contarActivas($userId);

        return [
            'usadas'    => $usadas,
            'tope'      => $tope,
            'es_pro'    => $esPro,
            // Se mantiene la clave para no romper a quien la lea, pero ya nunca
            // es true: ningún plan es ilimitado.
            'ilimitado' => false,
            'quedan'    => max(0, $tope - $usadas),
            'lleno'     => $usadas >= $tope,
        ];
    }

    /** ¿Puede añadir una empresa más a su lista? */
    public function puedeVigilarMas(int $userId): bool
    {
        $cupo = $this->estadoCupo($userId);
        return $cupo['ilimitado'] || !$cupo['lleno'];
    }

    /**
     * Empieza a vigilar una empresa.
     *
     * La marca de agua arranca en el último acto YA publicado: de lo contrario el
     * primer aviso sería un volcado del histórico que el usuario acaba de ver.
     *
     * @param string $source 'unlock' | 'search' | 'manual' | 'backfill'
     */
    public function watch(int $userId, string $cif, string $source = 'manual', bool $respetarTope = true): bool
    {
        $cleanCif = $this->cleanCif($cif);
        if ($userId <= 0 || $cleanCif === '') {
            return false;
        }

        // El tope se comprueba aquí y no solo en el botón: si no, el alta
        // automática al desbloquear o al buscar se lo saltaría y el límite no
        // sería tal. `$respetarTope = false` queda para el backfill.
        if ($respetarTope && !$this->isWatching($userId, $cleanCif) && !$this->puedeVigilarMas($userId)) {
            return false;
        }

        $company = $this->db->table('companies')->select('id')->where('cif', $cleanCif)->get()->getRow();
        $companyId = $company ? (int) $company->id : null;

        if (!$companyId) {
            // Sin company_id no hay forma de cruzar con borme_posts: no sirve de nada
            return false;
        }

        $tope = $this->db->table('borme_posts')
            ->selectMax('id', 'max_id')
            ->where('company_id', $companyId)
            ->get()->getRow();
        $ultimoActo = (int) ($tope->max_id ?? 0);

        $ahora    = date('Y-m-d H:i:s');
        $existente = $this->db->table('user_company_watch')
            ->where('user_id', $userId)
            ->where('cif', $cleanCif)
            ->get()->getRow();

        if ($existente) {
            if ((int) $existente->active === 1) {
                return true; // ya la sigue, nada que hacer
            }

            // Reactivación: la marca de agua vuelve al presente para que no le
            // lleguen de golpe los actos publicados mientras no la seguía.
            $this->db->table('user_company_watch')
                ->where('id', $existente->id)
                ->update([
                    'active'        => 1,
                    'company_id'    => $companyId,
                    'last_borme_id' => $ultimoActo,
                    'source'        => $source,
                    'updated_at'    => $ahora,
                ]);

            return true;
        }

        try {
            $this->db->table('user_company_watch')->insert([
                'user_id'       => $userId,
                'cif'           => $cleanCif,
                'company_id'    => $companyId,
                'source'        => $source,
                'last_borme_id' => $ultimoActo,
                'active'        => 1,
                'created_at'    => $ahora,
                'updated_at'    => $ahora,
            ]);
        } catch (\Throwable $e) {
            // La clave única (user_id, cif) puede saltar en una carrera: no es un error
            log_message('info', 'CompanyWatchService::watch duplicado: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Deja de vigilar. No se borra la fila: así se conserva la marca de agua y no
     * llega una avalancha si vuelve a activarla.
     */
    public function unwatch(int $userId, string $cif): bool
    {
        $cleanCif = $this->cleanCif($cif);
        if ($userId <= 0 || $cleanCif === '') {
            return false;
        }

        $this->db->table('user_company_watch')
            ->where('user_id', $userId)
            ->where('cif', $cleanCif)
            ->update(['active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

        return true;
    }

    public function isWatching(int $userId, string $cif): bool
    {
        $cleanCif = $this->cleanCif($cif);
        if ($userId <= 0 || $cleanCif === '') {
            return false;
        }

        return $this->db->table('user_company_watch')
            ->where('user_id', $userId)
            ->where('cif', $cleanCif)
            ->where('active', 1)
            ->countAllResults() > 0;
    }

    /**
     * ¿Los avisos del BORME llegarían de verdad a este usuario?
     *
     * Vigilar una empresa y que el sistema nunca te escriba es la peor promesa
     * posible: el usuario cree que está cubierto. Misma regla de tres estados que
     * usa EmailService: alerts_borme manda; si es NULL, decide `unsuscribe`.
     */
    public function alertasActivas(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $fila = $this->db->table('users')
            ->select('unsuscribe, alerts_borme')
            ->where('id', $userId)
            ->get()->getRow();

        if (!$fila) {
            return false;
        }

        if ($fila->alerts_borme !== null) {
            return (int) $fila->alerts_borme === 1;
        }

        return (int) ($fila->unsuscribe ?? 0) === 0;
    }

    /**
     * Invierte el estado y devuelve el resultante.
     */
    /**
     * @param string $source de dónde sale el alta ('manual' el botón de la ficha,
     *                       'teaser' la vuelta de "Avísame si cambia"): es lo que deja
     *                       medir qué entrada funciona.
     */
    public function toggle(int $userId, string $cif, string $source = 'manual'): bool
    {
        if ($this->isWatching($userId, $cif)) {
            $this->unwatch($userId, $cif);
            return false;
        }

        $this->watch($userId, $cif, $source);
        return $this->isWatching($userId, $cif);
    }
}
