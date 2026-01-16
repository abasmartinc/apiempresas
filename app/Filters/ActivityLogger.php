<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ActivityLogger implements FilterInterface
{
    /**
     * Do nothing before the request
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // ...
    }

    /**
     * Log activity after the request is complete
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Check if user is logged in
        if (!session('logged_in')) {
            return;
        }

        // Ignore AJAX requests / debug / profiler to avoid spam
        if ($request->isAJAX() || strpos($request->getUri()->getPath(), 'wdt') !== false) {
            return;
        }

        // Ignore admin pages if you don't want to track admin actions as 'page_view' (optional)
        // For now we log everything as requested.

        $uri = (string) $request->getUri();
        $path = $request->getUri()->getPath();

        // Avoid logging the activity log page itself to prevent infinite loop of confusion
        if (strpos($path, 'admin/activity-logs') !== false) {
            return;
        }

        $method = $request->getMethod();
        
        // Use the helper to log
        helper('activity');
        
        // We use a generic 'page_view' action, but we can be more specific if needed.
        // Details will contain the full URL.
        log_activity('page_view', [
            'url'    => $uri,
            'path'   => $path,
            'method' => $method
        ]);
    }
}
