<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Controllers;

use TourKinet\LightPHP\Core\Request;
use TourKinet\LightPHP\Core\Response;
use TourKinet\TravelApp\Services\BlogService;

final class BlogController
{
    public function __construct(
        private readonly PageController $pages,
        private readonly BlogService $blogs,
    ) {
    }

    public function index(Request $request): Response
    {
        $filters = [];
        foreach (['search', 'category', 'tag', 'page', 'per_page'] as $field) {
            $value = $request->query[$field] ?? null;
            if (is_string($value) && trim($value) !== '') {
                $filters[$field] = trim($value);
            }
        }
        $filters = $this->blogs->normalizeFilters($filters);

        return $this->pages->page('pages/blogs', 'Travel stories', $request, [
            'blogs' => $this->blogs->getBlogs($filters),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, array $params): Response
    {
        $blog = $this->blogs->getBlog((string) ($params['slug'] ?? ''));
        if ($blog === []) {
            return Response::html($this->pages->page('errors/404', 'Page not found', $request, [], 404)->body(), 404);
        }
        $seo = is_array($blog['seo'] ?? null) ? $blog['seo'] : [];
        $title = text_value($seo['title'] ?? $blog['title'] ?? $blog['name'] ?? 'Travel story');
        $slug = valid_slug(text_value($blog['slug'] ?? '')) ?? '';
        $images = is_array($blog['images'] ?? null) ? $blog['images'] : [];
        $banner = is_array($images['banner'] ?? null) ? $images['banner'] : [];

        return $this->pages->page('pages/blog-detail', $title, $request, [
            'blog' => $blog,
            'description' => text_value($seo['description'] ?? $blog['short_intro'] ?? $blog['excerpt'] ?? config('website.meta_description')),
            'keywords' => is_array($seo['keywords'] ?? null) ? $seo['keywords'] : [],
            'canonical' => safe_canonical_url($seo['canonical_url'] ?? null)
                ?? rtrim((string) config('app.url'), '/') . '/blogs/' . rawurlencode($slug),
            'ogImage' => safe_image_url($seo['og_image_url'] ?? $banner['url'] ?? null),
        ]);
    }
}
