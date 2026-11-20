<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Services;

use TourKinet\LightPHP\Cache\FileCache;
use TourKinet\LightPHP\Core\Config;
use TourKinet\TravelApp\Api\TourKinetClient;

final class BlogService
{
    public function __construct(
        private readonly TourKinetClient $client,
        private readonly FileCache $cache,
        private readonly Config $config,
    ) {
    }

    public function getBlogs(array $filters = []): array
    {
        $filters = $this->normalizeFilters($filters);

        return $this->cached(
            'blogs:list:' . hash('sha256', http_build_query($filters)),
            (int) $this->config->get('tourkinet.cache.blogs_ttl', 1800),
            fn(): array => $this->client->request('GET', $this->client->endpoint('blogs'), $filters),
        );
    }

    public function getBlog(string $slug): array
    {
        $slug = \valid_slug($slug) ?? '';
        if ($slug === '') {
            return [];
        }
        $endpoint = str_replace('{slug}', rawurlencode($slug), $this->client->endpoint('blog'));

        return $this->cached('blogs:detail:' . $slug, (int) $this->config->get('tourkinet.cache.blogs_ttl', 1800), fn(): array =>
            api_single_item($this->client->request('GET', $endpoint), $slug)
        );
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

    public function normalizeFilters(array $filters): array
    {
        $result = [];
        foreach (['search' => 120, 'category' => 120, 'tag' => 120, 'page' => 4, 'per_page' => 4] as $field => $maximum) {
            $value = $filters[$field] ?? null;
            if (!is_string($value) && !is_int($value)) {
                continue;
            }
            $value = trim((string) $value);
            if (in_array($field, ['page', 'per_page'], true) && preg_match('/^[0-9]{1,4}$/D', $value) !== 1) {
                continue;
            }
            if ($value !== '') {
                $result[$field] = in_array($field, ['page', 'per_page'], true)
                    ? $value
                    : (function_exists('mb_substr') ? mb_substr($value, 0, $maximum) : substr($value, 0, $maximum));
            }
        }
        if (isset($result['page']) && (int) $result['page'] < 1) {
            unset($result['page']);
        }
        if (isset($result['per_page'])) {
            $result['per_page'] = (string) min(50, max(1, (int) $result['per_page']));
        }

        return $result;
    }
}
