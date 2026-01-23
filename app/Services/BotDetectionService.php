<?php

namespace App\Services;

use App\Models\BlockedIpModel;
use CodeIgniter\HTTP\RequestInterface;

class BotDetectionService
{
    protected $blockedIpModel;

    public function __construct()
    {
        $this->blockedIpModel = new BlockedIpModel();
    }

    /**
     * Get the real IP address, considering proxies
     * Loading reseller may use proxies, so we check X-Forwarded-For
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getRealIp(RequestInterface $request): string
    {
        // Check for X-Forwarded-For header (common in proxy/load balancer setups)
        $forwardedFor = $request->getServer('HTTP_X_FORWARDED_FOR');
        
        if ($forwardedFor) {
            // X-Forwarded-For can contain multiple IPs (client, proxy1, proxy2, ...)
            // The first one is usually the real client IP
            $ips = array_map('trim', explode(',', $forwardedFor));
            $realIp = $ips[0];
            
            // Validate it's a proper IP
            if (filter_var($realIp, FILTER_VALIDATE_IP)) {
                return $realIp;
            }
        }
        
        // Fallback to standard method
        return $request->getIPAddress();
    }

    /**
     * Check if an IP is currently blocked
     *
     * @param string $ip
     * @return bool
     */
    public function isIpBlocked(string $ip): bool
    {
        return $this->blockedIpModel->isBlocked($ip);
    }

    /**
     * Detect bot behavior and block the IP
     *
     * @param RequestInterface $request
     * @param string $reason
     * @param array $context Additional context information
     * @return bool True if IP was blocked, false if already blocked or error
     */
    public function detectAndBlock(RequestInterface $request, string $reason, array $context = []): bool
    {
        $ip = $this->getRealIp($request);
        
        // Don't block localhost/development IPs
        if ($this->isLocalIp($ip)) {
            log_message('info', "[BotDetection] Skipping block for local IP: {$ip}");
            return false;
        }

        // Prepare metadata
        $meta = array_merge([
            'user_agent' => $request->getUserAgent() ? $request->getUserAgent()->getAgentString() : '',
            'referer'    => $request->getServer('HTTP_REFERER') ?? '',
            'route'      => $request->getUri()->getPath() ?? '',
            'method'     => $request->getMethod() ?? 'GET',
            'timestamp'  => date('Y-m-d H:i:s'),
        ], $context);

        // Block the IP
        $blocked = $this->blockedIpModel->blockIp($ip, $reason, $meta);

        if ($blocked) {
            log_message('warning', "[BotDetection] IP blocked: {$ip} | Reason: {$reason}");
        }

        return $blocked;
    }

    /**
     * Record an attempt from a blocked IP
     *
     * @param string $ip
     * @param array $context
     * @return void
     */
    public function recordBlockedAttempt(string $ip, array $context = []): void
    {
        $this->blockedIpModel->recordAttempt($ip);
        
        log_message('info', "[BotDetection] Blocked IP attempt: {$ip} | Route: " . ($context['route'] ?? 'unknown'));
    }

    /**
     * Check if IP is a local/development IP that should not be blocked
     *
     * @param string $ip
     * @return bool
     */
    protected function isLocalIp(string $ip): bool
    {
        $localPatterns = [
            '127.0.0.1',
            '::1',
            'localhost',
        ];

        foreach ($localPatterns as $pattern) {
            if ($ip === $pattern || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect suspicious patterns in search behavior
     *
     * @param string $sessionId
     * @param string $query
     * @return bool True if suspicious pattern detected
     */
    public function detectSuspiciousSearchPattern(string $sessionId, string $query): bool
    {
        // This could be enhanced with more sophisticated detection
        // For now, we'll rely on rate limiting in the controller
        
        // Future enhancements could include:
        // - Detecting sequential CIF scanning (A00000001, A00000002, etc.)
        // - Detecting dictionary-based name searches
        // - Detecting searches with no user interaction time
        
        return false;
    }

    /**
     * Get blocked IP details
     *
     * @param string $ip
     * @return array|null
     */
    public function getBlockedIpDetails(string $ip): ?array
    {
        return $this->blockedIpModel->getBlockedIpDetails($ip);
    }

    /**
     * Manually unblock an IP (for admin use)
     *
     * @param string $ip
     * @return bool
     */
    public function unblockIp(string $ip): bool
    {
        $result = $this->blockedIpModel->unblockIp($ip);
        
        if ($result) {
            log_message('info', "[BotDetection] IP unblocked: {$ip}");
        }
        
        return $result;
    }

    /**
     * Get statistics about blocked IPs
     *
     * @return array
     */
    public function getStats(): array
    {
        return $this->blockedIpModel->getStats();
    }
}
