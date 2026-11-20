<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Services;

use TourKinet\LightPHP\Cache\FileCache;
use TourKinet\LightPHP\Core\Config;
use TourKinet\TravelApp\Api\TourKinetClient;

final class PackageService
{
    public function __construct(
        private readonly TourKinetClient $client,
        private readonly FileCache $cache,
        private readonly Config $config,
    ) {
    }

    public function getPackages(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        return $this->cached(
            'packages:list:' . hash('sha256', http_build_query($filters)),
            (int) $this->config->get('tourkinet.cache.packages_ttl', 900),
            fn(): array => $this->client->request('GET', $this->client->endpoint('packages'), $filters),
        );
    }

    public function normalizeFilters(array $filters): array
    {
        return $this->filterValues($filters, [
            'theme' => 120,
            'type' => 120,
            'destination' => 120,
            'is_featured' => 5,
            'duration_min' => 4,
            'duration_max' => 4,
            'page' => 4,
            'per_page' => 4,
        ]);
    }

    public function getPackage(string $slug): array
    {
        $slug = \valid_slug($slug) ?? '';
        if ($slug === '') {
            return [];
        }
        $endpoint = str_replace('{slug}', rawurlencode($slug), $this->client->endpoint('package'));

        return $this->cached('packages:detail:' . $slug, (int) $this->config->get('tourkinet.cache.packages_ttl', 900), fn(): array =>
            api_single_item($this->client->request('GET', $endpoint), $slug)
        );
    }

    public function getFeaturedPackages(): array
    {
        return $this->getPackages(['is_featured' => '1', 'per_page' => '3']);
    }

    private function cached(string $key, int $ttl, callable $fetch): array
    {
        $cached = $this->cache->get($key);
        if (is_array($cached)) {
            return $cached;
        }
        $response = $fetch();
        if ($response !== [] && ($response['success'] ?? true) !== false) {
            $this->cache->set($key, $response, $ttl);
            return $response;
        }

        $stale = $this->cache->getStale($key);
        return is_array($stale) ? $stale : [];
    }

    private function filterValues(array $filters, array $limits): array
    {
        $result = [];
        foreach ($filters as $field => $value) {
            if (!isset($limits[$field]) || (!is_string($value) && !is_int($value))) {
                continue;
            }
            $value = trim((string) $value);
            if (in_array($field, ['duration_min', 'duration_max', 'page', 'per_page'], true)
                && preg_match('/^[0-9]{1,4}$/D', $value) !== 1) {
                continue;
            }
            if ($value !== '') {
                $result[$field] = in_array($field, ['duration_min', 'duration_max', 'page', 'per_page'], true)
                    ? $value
                    : (function_exists('mb_substr')
                        ? mb_substr($value, 0, $limits[$field])
                        : substr($value, 0, $limits[$field]));
            }
        }
        if (isset($result['per_page'])) {
            $result['per_page'] = (string) min(50, max(1, (int) $result['per_page']));
        }
        foreach (['duration_min', 'duration_max', 'page'] as $field) {
            if (isset($result[$field]) && (int) $result[$field] < 1) {
                unset($result[$field]);
            }
        }
        if (isset($result['is_featured']) && !in_array($result['is_featured'], ['true', 'false', '1', '0'], true)) {
            unset($result['is_featured']);
        } elseif (isset($result['is_featured'])) {
            $result['is_featured'] = in_array($result['is_featured'], ['true', '1'], true) ? '1' : '0';
        }

        return $result;
    }
}
