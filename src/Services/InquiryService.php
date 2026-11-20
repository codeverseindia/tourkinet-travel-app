<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Services;

use TourKinet\LightPHP\Core\Request;
use TourKinet\LightPHP\Security\RateLimiter;
use TourKinet\TravelApp\Api\TourKinetClient;

final class InquiryService
{
    public function __construct(
        private readonly TourKinetClient $client,
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    public function submit(array $payload, Request $request): bool
    {
        if (!$this->rateLimiter->allow('contact', $request->clientIp(), 5, 600)
            || !$this->rateLimiter->allow('api-inquiry', $request->clientIp(), 5, 600)) {
            return false;
        }
        $result = $this->client->request('POST', $this->client->endpoint('inquiry'), [], $payload);
        return $result !== [] && ($result['success'] ?? true) !== false;
    }
}
