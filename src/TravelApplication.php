<?php
declare(strict_types=1);

namespace TourKinet\TravelApp;

use TourKinet\LightPHP\Application;
use TourKinet\LightPHP\Http\HttpClient;
use TourKinet\TravelApp\Api\TourKinetClient;
use TourKinet\TravelApp\Controllers\BlogController;
use TourKinet\TravelApp\Controllers\InquiryController;
use TourKinet\TravelApp\Controllers\PackageController;
use TourKinet\TravelApp\Controllers\PageController;
use TourKinet\TravelApp\Controllers\SitemapController;
use TourKinet\TravelApp\Providers\RouteProvider;
use TourKinet\TravelApp\Services\BlogService;
use TourKinet\TravelApp\Services\InquiryService;
use TourKinet\TravelApp\Services\PackageService;

final class TravelApplication
{
    public static function register(Application $application): void
    {
        $packageRoot = dirname(__DIR__);
        $application->view()->addPath($packageRoot . '/resources/views', 100);
        $application->addPackageVersion('Travel App', 'codeverseindia/tourkinet-travel-app');
        $application->addDoctorCheck('Travel App autoload and routes', static fn(): bool =>
            class_exists(self::class) && count($application->router()->all()) >= 14
        );
        $application->addDoctorCheck('Tour Kinet endpoint configuration', static function () use ($application): bool {
            $config = $application->config();
            return is_string($config->get('tourkinet.api_url'))
                && is_array($config->get('tourkinet.endpoints'))
                && count($config->get('tourkinet.endpoints')) >= 5;
        });

        $http = new HttpClient();
        $client = new TourKinetClient($application->config(), $http);
        $packages = new PackageService($client, $application->cache(), $application->config());
        $blogs = new BlogService($client, $application->cache(), $application->config());
        $inquiries = new InquiryService($client, $application->rateLimiter());
        $pages = new PageController($application, $packages);

        (new RouteProvider(
            $application->router(),
            $pages,
            new PackageController($pages, $packages),
            new BlogController($pages, $blogs),
            new InquiryController($application, $pages, $packages, $inquiries, $application->rateLimiter(), $http),
            new SitemapController($application, $packages, $blogs),
        ))->register();
    }
}
