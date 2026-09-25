<?php
/**
 * Aviso de cupo para clientes de pago de la API (Pro y Business).
 *
 * Antes un cliente de pago no veía nada en la app hasta recibir el 429. Se muestra a
 * partir del 80 % del cupo del mes, con una estimación de cuándo se agotará al ritmo
 * actual y las salidas: pasar a Business (desde Pro) o comprar un bono.
 *
 * Parámetros:
 *   $planId    int       2 = Pro, 3 = Business (con otro plan no se muestra nada)
 *   $planName  string    nombre del plan
 *   $quota     int       cupo mensual
 *   $used      int       consultas usadas este mes (como las cuenta el panel)
 *   $wallet    int|null  saldo del monedero; si no se pasa, se lee de user_wallets
 *   $userId    int       para leer el monedero si hace falta
 *   $source    string    prefijo del origen para medir los clics (dash / usage)
 */
$planId   = (int) ($planId ?? 0);
$quota    = (int) ($quota ?? 0);
$used     = (int) ($used ?? 0);
$source   = preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($source ?? 'dash')));

if (!in_array($planId, [2, 3], true) || $quota <= 0 || $used < $quota * 0.8) {
    return;
}

if (!isset($wallet) || $wallet === null) {
    $wallet = 0;
    try {
        $row = \Config\Database::connect()->table('user_wallets')
            ->select('balance')->where('user_id', (int) ($userId ?? 0))->get()->getRow();
        $wallet = (int) ($row->balance ?? 0);
    } catch (\Throwable $e) {
        $wallet = 0;
    }
}
$wallet = (int) $wallet;

$n     = static fn (int $x) => number_format($x, 0, ',', '.');
$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
          'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$mesActual  = $meses[(int) date('n')];
$renueva    = '1 de ' . $meses[(int) date('n', strtotime('first day of next month'))];
$pct        = (int) floor(($used / $quota) * 100);
$agotado    = $used >= $quota;
$nombrePlan = trim((string) ($planName ?? '')) ?: ($planId === 2 ? 'Pro' : 'Business');
$umbral     = $agotado ? '100' : '80';

$urlBono     = site_url('crear-bono-api?source=' . $source . '_quota_' . $umbral);
$urlBusiness = site_url('billing?plan=business&source=' . $source . '_quota_' . $umbral);

// Estimación al ritmo del mes: consultas por día transcurrido
$proyeccion = '';
if (!$agotado) {
    $dia      = max(1, (int) date('j'));
    $diasMes  = (int) date('t');
    $ritmo    = $used / $dia;
    $quedan   = $quota - $used;
    if ($ritmo > 0) {
        $diaFin = $dia + (int) ceil($quedan / $ritmo);
        $proyeccion = $diaFin <= $diasMes
            ? ' A este ritmo se agotarán hacia el <strong>' . $diaFin . ' de ' . $mesActual . '</strong>.'
            : ' A este ritmo te llegan justas hasta final de mes.';
    }
}

$alAgotar = $wallet > 0
    ? 'las consultas se cobrarán de tu monedero (saldo: <strong>' . $n($wallet) . ' créditos</strong>)'
    : 'la API devolverá error 429 hasta el <strong>' . $renueva . '</strong>, cuando se renueva tu cupo';

if ($agotado && $wallet > 0) {
    $tipo   = 'warning';
    $titulo = 'Has agotado las consultas de este mes: estás usando tu monedero';
    $texto  = 'Has usado ' . $n($used) . ' de las ' . $n($quota) . ' consultas del Plan ' . esc($nombrePlan)
        . '. Tu integración sigue funcionando y cada consulta se descuenta de tu monedero (saldo: <strong>'
        . $n($wallet) . ' créditos</strong>) hasta el ' . $renueva . '.';
} elseif ($agotado) {
    $tipo   = 'error';
    $titulo = 'Has agotado las consultas de tu plan este mes';
    $texto  = 'Has usado las ' . $n($quota) . ' consultas del Plan ' . esc($nombrePlan)
        . ' y la API devuelve error 429 hasta el <strong>' . $renueva . '</strong>.';
} else {
    $tipo   = 'warning';
    $titulo = 'Has usado el ' . $pct . ' % de tus consultas de ' . $mesActual;
    $texto  = 'Llevas ' . $n($used) . ' de ' . $n($quota) . ' consultas del Plan ' . esc($nombrePlan) . '.'
        . $proyeccion . ' Cuando se acaben, ' . $alAgotar . '.';
}

if ($planId === 2) {
    $texto     .= ' Pasa a Business (el cambio es inmediato y no tocas tu código) o <a href="' . esc($urlBono)
        . '" style="color:inherit;text-decoration:underline;">compra un bono</a> para cubrir este mes.';
    $actionUrl  = $urlBusiness;
    $actionText = 'Pasar a Business';
} else {
    $texto     .= ' Un bono cubre lo que queda de mes; si tu volumen va a ser estable, escríbenos y te preparamos un plan a medida.';
    $actionUrl  = $urlBono;
    $actionText = 'Comprar un bono';
}

echo view('components/ui/alert', [
    'type'       => $tipo,
    'title'      => $titulo,
    'text'       => $texto,
    'actionUrl'  => $actionUrl,
    'actionText' => $actionText,
]);
