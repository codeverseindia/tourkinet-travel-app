<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Controllers;

use TourKinet\LightPHP\Core\Request;
use TourKinet\LightPHP\Core\Response;
use TourKinet\TravelApp\Services\PackageService;

final class PackageController
{
    public function __construct(
        private readonly PageController $pages,
        private readonly PackageService $packages,
    ) {
    }

    public function index(Request $request): Response
    {
        $filters = [];
        foreach (['theme', 'type', 'destination', 'is_featured', 'duration_min', 'duration_max', 'page', 'per_page'] as $field) {
            $value = $request->query[$field] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $filters[$field] = trim($value);
            }
        }
        $filters = $this->packages->normalizeFilters($filters);

        return $this->pages->page('pages/packages', 'Travel packages', $request, [
            'packages' => $this->packages->getPackages($filters),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $package = $this->packages->getPackage((string) ($params['slug'] ?? ''));
        if ($package === []) {
            return Response::html($this->pages->page('errors/404', 'Page not found', $request, [], 404)->body(), 404);
        }
        $summary = is_array($package['package'] ?? null) ? $package['package'] : $package;
        $seo = is_array($package['seo'] ?? null) ? $package['seo'] : [];
        $slug = valid_slug(text_value($summary['slug'] ?? '')) ?? '';
        $name = text_value($seo['title'] ?? $summary['title'] ?? $summary['name'] ?? 'Travel package');
        $canonical = safe_canonical_url($seo['canonical_url'] ?? null)
            ?? rtrim((string) config('app.url'), '/') . '/packages/' . rawurlencode($slug);
        $images = is_array($package['images'] ?? null) ? $package['images'] : [];
        $banner = is_array($images['banner'] ?? null) ? $images['banner'] : [];

        return $this->pages->page('pages/package-detail', $name, $request, [
            'package' => $package,
            'description' => text_value($seo['description'] ?? $summary['overview'] ?? config('website.meta_description')),
            'keywords' => is_array($seo['keywords'] ?? null) ? $seo['keywords'] : [],
            'canonical' => $canonical,
            'ogImage' => safe_image_url($banner['url'] ?? $summary['thumbnail']['url'] ?? null),
        ]);
    }
}
