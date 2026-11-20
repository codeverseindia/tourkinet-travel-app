<?php
declare(strict_types=1);

$root = dirname(__DIR__, 3);
$autoload = is_file($root . '/vendor/autoload.php')
    ? $root . '/vendor/autoload.php'
    : dirname(__DIR__) . '/vendor/autoload.php';
require $autoload;

use TourKinet\LightPHP\Cache\FileCache;
use TourKinet\LightPHP\Application;
use TourKinet\LightPHP\Core\Config;
use TourKinet\LightPHP\Http\HttpClient;
use TourKinet\TravelApp\Api\TourKinetClient;
use TourKinet\TravelApp\Services\PackageService;

$tests = 0;
$failures = [];
$assert = static function (bool $condition, string $name) use (&$tests, &$failures): void {
    $tests++;
    if (!$condition) {
        $failures[] = $name;
    }
};

$response = [
    'success' => true,
    'data' => [
        'package' => ['slug' => 'sample-trip', 'package_hash' => 'pkg_123'],
        'seo' => ['title' => 'Sample Tour'],
    ],
];
$assert(api_single_item($response, 'sample-trip') === $response['data'], 'Package response adapter preserves detail and SEO data');
$assert(count(api_items(['data' => [['slug' => 'trip-a'], ['slug' => 'trip-b']]])) === 2, 'List response adapter preserves list records');
$assert(api_single_item($response, 'wrong-slug') === [], 'Package detail adapter rejects mismatched slugs');
$assert(is_published(['status' => 'published']) && !is_published(['title' => 'Unpublished']), 'Publication checks require explicit status');

$_ENV['APP_ENV'] = 'local';
$assert(safe_image_url('https://images.example.test/photo.jpg') !== null, 'HTTPS media URL is accepted');
$assert(safe_image_url('/assets/images/destinations/highlands.svg') !== null, 'Site-local asset paths are accepted');
$assert(safe_image_url('http://app.tourkinet.loc/assets/photo.webp') !== null, 'Allowlisted local media URL is accepted in development');
$assert(safe_image_url('http://api.tourkinet.loc/assets/photo.webp') === null, 'Local API host is not accepted as a media host');
$assert(safe_image_url('http://app.tourkinet.loc/redirect') === null, 'Local media paths are restricted');
$assert(safe_image_url('javascript:alert(1)') === null, 'Unsafe media schemes are rejected');
$assert(safe_canonical_url('https://travel.example.test/package') !== null, 'HTTPS canonical URL is accepted');
$assert(safe_canonical_url('javascript:alert(1)') === null, 'Unsafe canonical URL is rejected');

$application = Application::boot(dirname(__DIR__), []);
\TourKinet\TravelApp\TravelApplication::register($application);
$website = [
    'meta_description' => 'Thoughtfully planned travel experiences.',
    'destinations' => [
        ['name' => 'Himalayan Highlands', 'filter' => 'Himalayan Highlands', 'summary' => 'Mountain journeys.', 'image' => '/assets/images/destinations/highlands.svg'],
        ['name' => 'Kerala Coast', 'filter' => 'Kerala Coast', 'summary' => 'Coastal journeys.', 'image' => '/assets/images/destinations/kerala.svg'],
        ['name' => 'Rajasthan Heritage', 'filter' => 'Rajasthan Heritage', 'summary' => 'Heritage journeys.', 'image' => '/assets/images/destinations/rajasthan.svg'],
    ],
];
$documentedPackage = [
    'package_hash' => 'pkg_sample',
    'package_code' => 'TOUR-SAMPLE',
    'slug' => 'sample-trip',
    'title' => 'Documented Tour',
    'overview' => 'A sample itinerary.',
    'duration' => ['days' => 4, 'nights' => 3],
    'destinations' => [['name' => 'Hill Town']],
    'thumbnail' => ['url' => 'https://images.example.test/tour.webp'],
    'theme' => ['name' => 'Nature'],
    'tour_type' => ['name' => 'Private'],
    'starting_price' => '12000.00',
    'currency' => 'INR',
    'is_featured' => true,
];
$packagesHtml = $application->view()->renderPage('pages/packages', [
    'website' => $website,
    'title' => 'Travel packages',
    'description' => 'Explore packages.',
    'packages' => ['success' => true, 'data' => [$documentedPackage], 'meta' => ['current_page' => 1, 'last_page' => 1]],
    'filters' => [],
]);
$assert(str_contains($packagesHtml, 'Documented Tour') && str_contains($packagesHtml, 'Hill Town') && str_contains($packagesHtml, 'INR 12000.00'), 'Documented package fields render in the default view');
$unsafePackage = $documentedPackage;
$unsafePackage['title'] = '<script>alert(1)</script>';
$unsafePackagesHtml = $application->view()->renderPage('pages/packages', [
    'website' => $website,
    'title' => 'Travel packages',
    'packages' => ['data' => [$unsafePackage]],
    'filters' => [],
]);
$assert(!str_contains($unsafePackagesHtml, '<script>alert(1)</script>')
    && str_contains($unsafePackagesHtml, '&lt;script&gt;alert(1)&lt;/script&gt;'), 'Package listing escapes API-provided text');
$destinations = is_array($website['destinations'] ?? null) ? $website['destinations'] : [];
$homeHtml = $application->view()->renderPage('pages/home', [
    'website' => $website,
    'title' => 'Find your next journey',
    'description' => 'Plan a trip.',
    'packages' => [],
    'destinations' => $destinations,
]);
$assert(str_contains($homeHtml, 'Featured destinations')
    && str_contains($homeHtml, 'Why travel with us')
    && str_contains($homeHtml, 'Talk to our team')
    && str_contains($homeHtml, '/assets/images/destinations/highlands.svg'), 'Homepage includes destinations, trust content, and contact CTA');
$destinationsHtml = $application->view()->renderPage('pages/destinations', [
    'website' => $website,
    'title' => 'Destinations',
    'description' => 'Explore.',
    'destinations' => $destinations,
]);
$assert(str_contains($destinationsHtml, 'Kerala Coast') && str_contains($destinationsHtml, 'Rajasthan Heritage'), 'Static destination cards are configurable');

$detailResponse = [
    'success' => true,
    'data' => [
        'package' => $documentedPackage,
        'seo' => ['title' => 'Sample Tour SEO', 'description' => 'An SEO summary.', 'keywords' => ['sample tour', 'Hill Town']],
        'images' => [
            'banner' => ['url' => 'http://app.tourkinet.loc/assets/tour-banner.webp', 'caption' => 'Mountain view'],
            'thumbnail' => ['url' => 'http://app.tourkinet.loc/assets/tour-thumb.webp', 'caption' => 'Tour thumbnail'],
            'gallery' => [['url' => 'http://app.tourkinet.loc/assets/tour-gallery.webp', 'caption' => 'Local village']],
        ],
        'itinerary' => [[
            'day' => 1,
            'title' => 'Arrive',
            'description' => 'Settle in.',
            'destinations' => [['name' => 'Hill Town']],
            'images' => [['url' => 'http://app.tourkinet.loc/assets/day-one.webp', 'caption' => 'Day one view']],
        ]],
        'inclusions' => ['Breakfast'],
        'exclusions' => ['Airfare'],
        'terms_conditions' => [],
    ],
];
$detailHtml = $application->view()->renderPage('pages/package-detail', [
    'website' => $website,
    'title' => 'Sample Tour SEO',
    'description' => 'An SEO summary.',
    'canonical' => 'http://app.tourkinet.loc/packages/sample-trip',
    'keywords' => ['sample tour', 'Hill Town'],
    'ogImage' => 'http://app.tourkinet.loc/assets/tour-banner.webp',
    'package' => api_single_item($detailResponse, 'sample-trip'),
]);
$assert(str_contains($detailHtml, 'Settle in.')
    && str_contains($detailHtml, 'name="keywords" content="sample tour, Hill Town"')
    && str_contains($detailHtml, 'Mountain view')
    && str_contains($detailHtml, 'Local village')
    && str_contains($detailHtml, 'Day one view')
    && str_contains($detailHtml, 'Theme: Nature')
    && str_contains($detailHtml, 'Tour type: Private'), 'Package detail SEO, media, itinerary, and package fields render');

$blogFixture = [
    'title' => 'Sample Story',
    'slug' => 'sample-story',
    'short_intro' => 'A sample introduction.',
    'content' => '<p>Story body.</p><script>alert(1)</script>',
    'published_at' => '2026-09-01T10:00:00Z',
    'category' => ['name' => 'Travel'],
    'author' => ['name' => 'Author Name', 'email' => 'author@example.test', 'bio' => 'Travel writer.'],
    'tags' => [['name' => 'Hiking']],
    'images' => [
        'banner' => ['url' => 'http://app.tourkinet.loc/assets/story-banner.webp'],
        'thumbnail' => ['url' => 'http://app.tourkinet.loc/assets/story-thumb.webp'],
    ],
    'seo' => [
        'title' => 'Story SEO Title',
        'description' => 'Story SEO description.',
        'keywords' => ['travel story'],
        'canonical_url' => 'https://travel.example.test/stories/sample-story',
        'og_image_url' => 'http://app.tourkinet.loc/assets/story-social.webp',
    ],
];
$blogHtml = $application->view()->renderPage('pages/blog-detail', [
    'website' => $website,
    'title' => 'Story SEO Title',
    'description' => 'Story SEO description.',
    'canonical' => 'https://travel.example.test/stories/sample-story',
    'keywords' => ['travel story'],
    'ogImage' => 'http://app.tourkinet.loc/assets/story-social.webp',
    'blog' => $blogFixture,
]);
$assert(str_contains($blogHtml, 'Story SEO Title')
    && str_contains($blogHtml, 'Story body.')
    && !str_contains($blogHtml, '<script>alert(1)</script>')
    && str_contains($blogHtml, 'Travel writer.')
    && str_contains($blogHtml, 'story-social.webp'), 'Blog content, metadata, author, and media render safely');

$config = new Config([
    'app' => ['env' => 'local'],
    'tourkinet' => [
        'api_url' => 'https://api.tourkinet.com/api/v1',
        'api_key' => '',
        'endpoints' => ['packages' => '/packages'],
    ],
]);
$client = new TourKinetClient($config, new HttpClient());
$assert($client->request('GET', '/packages') === [], 'Public API calls remain disabled when credentials are absent');
$assert($client->endpoint('packages') === '/packages', 'Endpoint mapping is configurable');

$cacheRoot = sys_get_temp_dir() . '/travel-app-test-' . bin2hex(random_bytes(6));
mkdir($cacheRoot, 0700);
$cache = new FileCache($cacheRoot);
$service = new PackageService($client, $cache, $config);
$filters = $service->normalizeFilters([
    'theme' => str_repeat('a', 130),
    'page' => '10000',
    'per_page' => '999',
    'is_featured' => 'true',
]);
$assert(strlen($filters['theme']) === 120
    && !isset($filters['page'])
    && $filters['per_page'] === '50'
    && $filters['is_featured'] === '1', 'Package filters are bounded and serialized for the API');
$assert($service->getPackage('../.env') === [], 'Package service rejects path traversal before any request');
rmdir($cacheRoot);

echo "{$tests} travel tests, " . count($failures) . " failures\n";
foreach ($failures as $failure) {
    fwrite(STDERR, "FAIL: {$failure}\n");
}
exit($failures === [] ? 0 : 1);
