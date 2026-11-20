<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Controllers;

use TourKinet\LightPHP\Application;
use TourKinet\LightPHP\Core\Request;
use TourKinet\LightPHP\Core\Response;
use TourKinet\TravelApp\Services\BlogService;
use TourKinet\TravelApp\Services\PackageService;

final class SitemapController
{
    public function __construct(
        private readonly Application $application,
        private readonly PackageService $packages,
        private readonly BlogService $blogs,
    ) {
    }

    public function show(Request $request): Response
    {
        $root = rtrim((string) $this->application->config()->get('app.url', ''), '/');
        $urls = ['/', '/about', '/destinations', '/packages', '/blogs', '/contact', '/privacy-policy', '/terms-and-conditions'];
        foreach ([
            ['/packages/', $this->packages->getPackages()],
            ['/blogs/', $this->blogs->getBlogs()],
        ] as [$prefix, $response]) {
            foreach (api_items($response) as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $slug = valid_slug(text_value($item['slug'] ?? ''));
                if ($slug !== null) {
                    $urls[] = $prefix . rawurlencode($slug);
                }
            }
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach (array_unique($urls) as $url) {
            $xml .= '<url><loc>' . e($root . $url) . "</loc></url>\n";
        }
        return Response::xml($xml . "</urlset>\n");
    }
}
