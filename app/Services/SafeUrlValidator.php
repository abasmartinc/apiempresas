<?php

namespace App\Services;

class SafeUrlValidator
{
    /**
     * Statically validate that a URL is safe for webhook registration.
     * Enforces HTTPS-only, no credentials, port 443 only, and blocks obvious private/loopback/reserved literal IPs.
     * 
     * NOTE: This performs strictly static parsing and validation. Zero DNS or network I/O is performed in this phase.
     *
     * @param string $url
     * @return bool
     */
    public static function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || strlen($url) > 2048) {
            return false;
        }

        // Basic syntax validation
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parsed = parse_url($url);
        if ($parsed === false || !is_array($parsed)) {
            return false;
        }

        // 1. Scheme must be strictly HTTPS
        $scheme = strtolower($parsed['scheme'] ?? '');
        if ($scheme !== 'https') {
            return false;
        }

        // 2. Credentials in URL are strictly forbidden
        if (isset($parsed['user']) || isset($parsed['pass'])) {
            return false;
        }

        // 3. Host is required
        $host = $parsed['host'] ?? '';
        if ($host === '') {
            return false;
        }

        // 4. Port must be default HTTPS (omitted) or explicitly 443
        if (isset($parsed['port']) && (int)$parsed['port'] !== 443) {
            return false;
        }

        // Normalize host (strip IPv6 brackets if present)
        $cleanHost = strtolower(trim($host, '[]'));

        // 5. Block known localhost / internal domain labels
        if (self::isInternalHostname($cleanHost)) {
            return false;
        }

        // 6. If the host is a literal IPv4 or IPv6 address, validate against private/reserved ranges
        if (filter_var($cleanHost, FILTER_VALIDATE_IP)) {
            if (!self::isPublicIp($cleanHost)) {
                return false;
            }
        }

        // 7. Reject abnormal dotted decimal representations (e.g. 127.1 or leading zeros)
        if (preg_match('/^[0-9.]+$/', $cleanHost)) {
            if (!filter_var($cleanHost, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return false;
            }
            $octets = explode('.', $cleanHost);
            if (count($octets) !== 4) {
                return false;
            }
            foreach ($octets as $octet) {
                if ($octet !== '0' && str_starts_with($octet, '0')) {
                    // Octal representation attempt
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if hostname matches common internal or loopback names.
     */
    public static function isInternalHostname(string $host): bool
    {
        $host = strtolower(trim($host));

        if ($host === 'localhost' || str_ends_with($host, '.localhost')) {
            return true;
        }

        if (str_ends_with($host, '.local') || str_ends_with($host, '.internal') || str_ends_with($host, '.lan')) {
            return true;
        }

        return false;
    }

    /**
     * Statically evaluate whether an IP string is a routable, public IP address.
     * Returns false for loopback, private, link-local, reserved, multicast, or unspecified IPs.
     */
    public static function isPublicIp(string $ip): bool
    {
        $ipBytes = @inet_pton($ip);
        if ($ipBytes === false) {
            return false;
        }

        // Validate standard public flags via filter_var
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        if (filter_var($ip, FILTER_VALIDATE_IP, $flags) === false) {
            return false;
        }

        if (strlen($ipBytes) === 4) {
            // IPv4 specific range checks
            return self::isPublicIpv4($ipBytes);
        }

        if (strlen($ipBytes) === 16) {
            // IPv6 specific range checks
            return self::isPublicIpv6($ipBytes);
        }

        return false;
    }

    private static function isPublicIpv4(string $ipBytes): bool
    {
        $b0 = ord($ipBytes[0]);
        $b1 = ord($ipBytes[1]);

        // 0.0.0.0/8 (Current network)
        if ($b0 === 0) return false;

        // 10.0.0.0/8 (Private)
        if ($b0 === 10) return false;

        // 100.64.0.0/10 (Carrier-grade NAT)
        if ($b0 === 100 && ($b1 >= 64 && $b1 <= 127)) return false;

        // 127.0.0.0/8 (Loopback)
        if ($b0 === 127) return false;

        // 169.254.0.0/16 (Link-Local / Cloud Metadata)
        if ($b0 === 169 && $b1 === 254) return false;

        // 172.16.0.0/12 (Private)
        if ($b0 === 172 && ($b1 >= 16 && $b1 <= 31)) return false;

        // 192.0.0.0/24 (IETF Protocol Assignments)
        if ($b0 === 192 && $b1 === 0 && ord($ipBytes[2]) === 0) return false;

        // 192.168.0.0/16 (Private)
        if ($b0 === 192 && $b1 === 168) return false;

        // 198.18.0.0/15 (Benchmarking)
        if ($b0 === 198 && ($b1 === 18 || $b1 === 19)) return false;

        // 224.0.0.0/4 (Multicast) & 240.0.0.0/4 (Reserved)
        if ($b0 >= 224) return false;

        return true;
    }

    private static function isPublicIpv6(string $ipBytes): bool
    {
        // ::/128 (Unspecified)
        if ($ipBytes === str_repeat("\x00", 16)) return false;

        // ::1/128 (Loopback)
        if ($ipBytes === (str_repeat("\x00", 15) . "\x01")) return false;

        // ::ffff:0:0/96 (IPv4-mapped IPv6)
        if (substr($ipBytes, 0, 12) === (str_repeat("\x00", 10) . "\xff\xff")) {
            return self::isPublicIpv4(substr($ipBytes, 12, 4));
        }

        $w0 = (ord($ipBytes[0]) << 8) | ord($ipBytes[1]);

        // 0100::/64 (Discard-only)
        if ($w0 === 0x0100) return false;

        // 2001:db8::/32 (Documentation)
        if ($w0 === 0x2001 && ord($ipBytes[2]) === 0x0d && ord($ipBytes[3]) === 0xb8) return false;

        // fc00::/7 (Unique Local Address - ULA)
        $ord0 = ord($ipBytes[0]);
        if (($ord0 & 0xfe) === 0xfc) return false;

        // fe80::/10 (Link-Local)
        if (($ord0 & 0xff) === 0xfe && (ord($ipBytes[1]) & 0xc0) === 0x80) return false;

        // ff00::/8 (Multicast)
        if ($ord0 === 0xff) return false;

        return true;
    }
}