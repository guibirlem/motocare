<?php

namespace App\Services;

class PushEndpoint
{
    public static function allowed(string $endpoint): bool
    {
        $url = parse_url($endpoint);
        if (! $url || ($url['scheme'] ?? '') !== 'https' || isset($url['user']) || isset($url['pass']) || isset($url['fragment']) || (isset($url['port']) && $url['port'] !== 443)) {
            return false;
        }
        $host = strtolower($url['host'] ?? '');
        foreach (config('webpush.allowed_hosts', []) as $allowed) {
            if ($host === $allowed || ($allowed === 'notify.windows.com' && str_ends_with($host, '.'.$allowed))) {
                return true;
            }
        }

        return false;
    }
}
