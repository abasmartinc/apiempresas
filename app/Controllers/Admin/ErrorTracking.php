<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\ErrorTracking as Tracker;

/**
 * Errores de apiempresas agrupados en issues (como Sentry): listado, detalle y gestion. Igual que el gestor de abasmart_tools.
 * Los guarda App\Libraries\Errors\ErrorRecorder; aqui solo se leen y se cambia su estado (resolver, ignorar, reabrir, notas).
 * Rutas: /admin/errores (filtro admin).
 */
class ErrorTracking extends BaseController
{
    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $tracker = new Tracker();

        // Por defecto, lo que hay que mirar
        $statusInput = $this->request->getGet('status');
        $statuses = $statusInput === null ? ['open', 'regressed'] : array_values(array_filter((array) $statusInput));

        $filters = [
            'status' => $statuses,
            'type'   => (string) $this->request->getGet('type'),
            'group'  => (string) $this->request->getGet('group'),
            'q'      => trim((string) $this->request->getGet('q')),
            'from'   => (string) $this->request->getGet('from'),
            'to'     => (string) $this->request->getGet('to'),
        ];
        $page = max(1, (int) $this->request->getGet('page'));

        try {
            $result = $tracker->issues($filters, $page);
            $counters = $tracker->counters();
            $channels = $tracker->channels();
            $error = null;
        } catch (\Throwable $e) {
            // Sin las tablas (o sin base): la pagina explica que falta en vez de dar un 500.
            $result = ['rows' => [], 'total' => 0];
            $counters = ['open' => 0, 'regressed' => 0, 'new24h' => 0, 'events24h' => 0];
            $channels = Tracker::CHANNELS;
            $error = $e->getMessage();
        }

        return $this->renderView('admin/errores/index', [
            'title'    => 'Errores',
            'issues'   => $result['rows'],
            'total'    => $result['total'],
            'page'     => $page,
            'pages'    => max(1, (int) ceil($result['total'] / Tracker::ISSUES_PER_PAGE)),
            'filters'  => $filters,
            'counters' => $counters,
            'channels' => $channels,
            'dbError'  => $error,
        ]);
    }

    public function show(int $id)
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $tracker = new Tracker();
        $issue = $tracker->issue($id);
        if (!$issue) {
            return redirect()->to(site_url('admin/errores'))->with('error', 'No existe ese issue.');
        }

        $page = max(1, (int) $this->request->getGet('page'));
        $events = $tracker->events($id, $page);
        $eventId = (int) $this->request->getGet('event') ?: $tracker->latestEventId($id);
        $event = $eventId ? $tracker->event($id, $eventId) : null;

        return $this->renderView('admin/errores/show', [
            'title'    => 'Error #' . $id,
            'issue'    => $issue,
            'daily'    => $tracker->dailyCounts($id),
            'events'   => $events['rows'],
            'page'     => $page,
            'pages'    => max(1, (int) ceil($events['total'] / Tracker::EVENTS_PER_PAGE)),
            'event'    => $event,
            'activity' => $tracker->activity($id),
            'aiPrompt' => $tracker->aiPrompt($issue, $event),
        ]);
    }

    /** POST admin/errores/{id}/{accion}: resolve, ignore, reopen o note. */
    public function act(int $id, string $action)
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $error = (new Tracker())->act(
            $id,
            $action,
            $this->actor(),
            (string) $this->request->getPost('note'),
            (string) $this->request->getPost('release')
        );

        $messages = ['resolve' => 'Issue resuelto.', 'ignore' => 'Issue ignorado.', 'reopen' => 'Issue reabierto.', 'note' => 'Nota añadida.'];

        return redirect()->to(site_url('admin/errores/' . $id))->with($error === '' ? 'success' : 'error', $error === '' ? ($messages[$action] ?? 'Hecho.') : $error);
    }

    /** POST admin/errores/bulk: resolver o ignorar los issues marcados. */
    public function bulk()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $action = (string) $this->request->getPost('action');
        $ids = (array) $this->request->getPost('ids');
        // Vuelve al listado con los mismos filtros (solo el query string: nunca otra URL)
        $back = (string) $this->request->getPost('back');
        $back = site_url('admin/errores') . (str_starts_with($back, '?') ? $back : '');

        if (!in_array($action, ['resolve', 'ignore'], true) || !$ids) {
            return redirect()->to($back)->with('error', 'Marca algún issue y elige una acción.');
        }

        [$done, $skipped] = (new Tracker())->bulk($ids, $action, $this->actor(), (string) $this->request->getPost('note'));
        $verb = $action === 'resolve' ? ($done === 1 ? 'resuelto' : 'resueltos') : ($done === 1 ? 'ignorado' : 'ignorados');

        return redirect()->to($back)->with('success', $done . ' issue' . ($done === 1 ? '' : 's') . ' ' . $verb
            . ($skipped ? " ({$skipped} sin cambios: ya lo estaban o no existen)" : '') . '.');
    }

    /** Quien lo hizo, como se guarda en error_issue_activity.actor. */
    private function actor(): string
    {
        $name = trim((string) session('user_name'));
        $email = trim((string) session('user_email'));

        return $email !== '' ? trim($name . ' <' . $email . '>') : ($name ?: 'admin');
    }
}
