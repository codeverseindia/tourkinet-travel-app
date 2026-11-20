<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Api;

use JsonException;
use RuntimeException;
use TourKinet\LightPHP\Core\Config;
use TourKinet\LightPHP\Http\HttpClient;

final class TourKinetClient
{
    public function __construct(
        private readonly Config $config,
        private readonly HttpClient $http,
    ) {
    }

    public function request(string $method, string $endpoint, array $query = [], ?array $payload = null): array
    {
        $base = trim((string) $this->config->get('tourkinet.api_url', ''));
        $key = trim((string) $this->config->get('tourkinet.api_key', ''));
        $domain = trim((string) $this->config->get('tourkinet.client_domain', ''));
        $parts = parse_url($base);
        if (!$this->validBase($base, $parts) || $key === '' || preg_match('/[\x00-\x20\x7f]/', $key)) {
            return [];
        }
        if ($domain !== '' && preg_match('/^(?=.{1,253}$)(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)(?:\.(?:[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?))*$/D', $domain) !== 1) {
            error_log('[travel-app] Invalid Tour Kinet client domain configuration.');
            return [];
        }
        if (preg_match('#^/[A-Za-z0-9._~/-]+$#D', $endpoint) !== 1 || str_starts_with($endpoint, '//') || str_contains($endpoint, '..')) {
            error_log('[travel-app] Invalid Tour Kinet endpoint configuration.');
            return [];
        }

        $url = rtrim($base, '/') . $endpoint;
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }
        $headers = ['Authorization: Bearer ' . $key, 'Accept: application/json'];
        if ($domain !== '') {
            $headers[] = 'X-Client-Domain: ' . $domain;
        }
        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
            try {
                $body = json_encode($payload, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
            } catch (JsonException) {
                error_log('[travel-app] Unable to encode an inquiry payload.');
                return [];
            }
        } else {
            $body = null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $localHost = $host === 'api.tourkinet.loc' ? $host : null;
        try {
            $response = $this->http->request(
                strtoupper($method),
                $url,
                $headers,
                $body,
                (int) $this->config->get('tourkinet.timeout', 8),
                $localHost,
            );
        } catch (\InvalidArgumentException $exception) {
            error_log('[travel-app] Tour Kinet API request configuration rejected.');
            return [];
        } catch (RuntimeException $exception) {
            error_log('[travel-app] Tour Kinet API request could not be completed.');
            return [];
        }
        if (!$response->isSuccessful() || $response->error !== null) {
            error_log('[travel-app] Tour Kinet API returned an unsuccessful response (HTTP ' . $response->status . ').');
            return [];
        }
        $decoded = json_decode($response->body, true);
        if (!is_array($decoded)) {
            error_log('[travel-app] Tour Kinet API returned invalid JSON.');
            return [];
        }
        if (($decoded['success'] ?? true) === false) {
            error_log('[travel-app] Tour Kinet API reported an unsuccessful operation.');
            return [];
        }

        return $decoded;
    }

    public function endpoint(string $name): string
    {
        $endpoint = $this->config->get('tourkinet.endpoints.' . $name);
        if (!is_string($endpoint) || $endpoint === '') {
            throw new RuntimeException('Tour Kinet API endpoint is not configured.');
        }
        return $endpoint;
    }

    private function validBase(string $base, array|false $parts): bool
    {
        if ($base === '' || !is_array($parts) || empty($parts['host'])
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])
            || str_contains(rawurldecode((string) ($parts['path'] ?? '')), '..')) {
            return false;
        }
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) $parts['host']);
        $local = $scheme === 'http'
            && $host === 'api.tourkinet.loc'
            && (int) ($parts['port'] ?? 80) === 80
            && (string) $this->config->get('app.env', 'local') === 'local';

        return $scheme === 'https' || $local;
    }
}
