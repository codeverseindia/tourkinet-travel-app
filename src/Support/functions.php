<?php
declare(strict_types=1);

use TourKinet\LightPHP\Support\Helpers;

if (!function_exists('api_items')) {
    function api_items(array $response): array
    {
        foreach (['data', 'items', 'results'] as $key) {
            if (isset($response[$key]) && is_array($response[$key])) {
                if (array_is_list($response[$key])) {
                    return $response[$key];
                }
                return isset($response[$key]['slug']) ? [$response[$key]] : array_values($response[$key]);
            }
        }

        return array_is_list($response) ? $response : (isset($response['slug']) ? [$response] : []);
    }
}

if (!function_exists('api_single_item')) {
    function api_single_item(array $response, string $slug): array
    {
        if (isset($response['data']) && is_array($response['data']) && !array_is_list($response['data'])) {
            $item = $response['data'];
            if (isset($item['package']) && is_array($item['package'])) {
                $package = $item['package'];
                return ($package['slug'] ?? null) === $slug || ($package['package_hash'] ?? null) === $slug ? $item : [];
            }
            return !isset($item['slug']) || $item['slug'] === $slug ? $item : [];
        }
        foreach (api_items($response) as $item) {
            if (is_array($item) && ($item['slug'] ?? null) === $slug) {
                return $item;
            }
        }
        return [];
    }
}

if (!function_exists('is_published')) {
    function is_published(array $item): bool
    {
        $status = $item['status'] ?? '';
        return ($item['published'] ?? false) === true
            || ($item['is_published'] ?? false) === true
            || (is_string($status) && in_array(strtolower($status), ['published', 'active'], true));
    }
}

if (!function_exists('safe_image_url')) {
    function safe_image_url(mixed $value): ?string
    {
        if (Helpers::safeHttpsUrl($value) !== null) {
            return $value;
        }
        if (!is_string($value)) {
            return null;
        }
        $parts = parse_url($value);
        if (!is_array($parts)) {
            return null;
        }
        $path = rawurldecode((string) ($parts['path'] ?? ''));
        if (isset($parts['path']) && !isset($parts['scheme'], $parts['host'])
            && str_starts_with($value, '/') && !str_starts_with($value, '//')
            && !preg_match('/[\x00-\x20\\\\]/', $value)
            && preg_match('#^/(?:assets|storage)/#', $path) === 1
            && !preg_match('#(?:^|/)\.\.(?:/|$)#', $path)) {
            return $value;
        }
        return filter_var($value, FILTER_VALIDATE_URL) !== false
            && is_local_app_http_url($parts)
            && preg_match('#^/(?:assets|storage)/#', $path) === 1
            && !preg_match('#(?:^|/)\.\.(?:/|$)#', $path)
            ? $value
            : null;
    }
}

if (!function_exists('safe_canonical_url')) {
    function safe_canonical_url(mixed $value): ?string
    {
        return Helpers::safeHttpsUrl($value) !== null
            || (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false
                && is_array($parts = parse_url($value))
                && is_local_app_http_url($parts))
            ? $value
            : null;
    }
}

if (!function_exists('is_local_app_http_url')) {
    function is_local_app_http_url(array $parts): bool
    {
        return (string) env('APP_ENV', 'local') === 'local'
            && strtolower((string) ($parts['scheme'] ?? '')) === 'http'
            && strtolower((string) ($parts['host'] ?? '')) === 'app.tourkinet.loc'
            && (!isset($parts['port']) || (int) $parts['port'] === 80)
            && !isset($parts['user'])
            && !isset($parts['pass']);
    }
}

if (!function_exists('page_title')) {
    function page_title(string $title): string
    {
        return $title . ' | ' . (string) config('website.company_name', config('app.name', 'Website'));
    }
}
