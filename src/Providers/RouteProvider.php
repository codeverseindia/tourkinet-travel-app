<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Providers;

use TourKinet\LightPHP\Core\Router;
use TourKinet\TravelApp\Controllers\BlogController;
use TourKinet\TravelApp\Controllers\InquiryController;
use TourKinet\TravelApp\Controllers\PackageController;
use TourKinet\TravelApp\Controllers\PageController;
use TourKinet\TravelApp\Controllers\SitemapController;

final class RouteProvider
{
    public function __construct(
        private readonly Router $router,
        private readonly PageController $pages,
        private readonly PackageController $packages,
        private readonly BlogController $blogs,
        private readonly InquiryController $inquiries,
        private readonly SitemapController $sitemap,
    ) {
    }

    public function register(): void
    {
        $this->router
            ->get('/', [$this->pages, 'home'], 'home')
            ->get('/about', [$this->pages, 'about'], 'about')
            ->get('/destinations', [$this->pages, 'destinations'], 'destinations')
            ->get('/packages', [$this->packages, 'index'], 'packages')
            ->get('/packages/{slug}', [$this->packages, 'show'], 'package.detail')
            ->get('/blogs', [$this->blogs, 'index'], 'blogs')
            ->get('/blogs/{slug}', [$this->blogs, 'show'], 'blog.detail')
            ->get('/contact', [$this->inquiries, 'form'], 'contact')
            ->post('/contact', [$this->inquiries, 'submit'], 'contact.submit')
            ->get('/privacy-policy', [$this->pages, 'privacy'], 'privacy')
            ->get('/terms-and-conditions', [$this->pages, 'terms'], 'terms')
            ->get('/privacy', [$this->pages, 'privacy'], 'privacy.legacy')
            ->get('/terms', [$this->pages, 'terms'], 'terms.legacy')
            ->get('/sitemap.xml', [$this->sitemap, 'show'], 'sitemap');
    }
}
