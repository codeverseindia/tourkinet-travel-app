<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Controllers;

use TourKinet\LightPHP\Application;
use TourKinet\LightPHP\Core\Request;
use TourKinet\LightPHP\Core\Response;
use TourKinet\TravelApp\Services\PackageService;

final class PageController
{
    public function __construct(
        private readonly Application $application,
        private readonly PackageService $packages,
    )
    {
    }

    public function home(Request $request): Response
    {
        $packages = $this->packages->getFeaturedPackages();
        $website = (array) $this->application->config()->get('website', []);
        return $this->page('pages/home', 'Find your next journey', $request, [
            'packages' => $packages,
            'destinations' => is_array($website['destinations'] ?? null) ? $website['destinations'] : [],
        ]);
    }

    public function about(Request $request): Response
    {
        return $this->page('pages/about', 'About us', $request);
    }

    public function destinations(Request $request): Response
    {
        $website = (array) $this->application->config()->get('website', []);
        return $this->page('pages/destinations', 'Destinations', $request, [
            'destinations' => is_array($website['destinations'] ?? null) ? $website['destinations'] : [],
        ]);
    }

    public function privacy(Request $request): Response
    {
        return $this->page('pages/privacy', 'Privacy policy', $request);
    }

    public function terms(Request $request): Response
    {
        return $this->page('pages/terms', 'Terms and conditions', $request);
    }

    public function page(string $template, string $title, Request $request, array $data = [], int $status = 200): Response
    {
        $website = (array) $this->application->config()->get('website', []);
        $data += [
            'website' => $website,
            'title' => $title,
            'description' => (string) ($website['meta_description'] ?? ''),
            'canonical' => rtrim((string) $this->application->config()->get('app.url', ''), '/') . $request->path,
        ];

        return Response::html($this->application->view()->renderPage($template, $data), $status);
    }
}
