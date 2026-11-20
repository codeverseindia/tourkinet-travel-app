<?php
declare(strict_types=1);

namespace TourKinet\TravelApp\Controllers;

use DateTimeImmutable;
use TourKinet\LightPHP\Application;
use TourKinet\LightPHP\Core\Request;
use TourKinet\LightPHP\Core\Response;
use TourKinet\LightPHP\Http\HttpClient;
use TourKinet\LightPHP\Security\Csrf;
use TourKinet\LightPHP\Security\RateLimiter;
use TourKinet\LightPHP\Security\Validator;
use TourKinet\TravelApp\Services\InquiryService;
use TourKinet\TravelApp\Services\PackageService;

final class InquiryController
{
    public function __construct(
        private readonly Application $application,
        private readonly PageController $pages,
        private readonly PackageService $packages,
        private readonly InquiryService $inquiries,
        private readonly RateLimiter $rateLimiter,
        private readonly HttpClient $http,
    ) {
    }

    public function form(Request $request): Response
    {
        $slug = valid_slug($request->queryString('package')) ?? '';
        $packageResponse = $slug === '' ? [] : $this->packages->getPackage($slug);
        $package = $packageResponse['package'] ?? $packageResponse;
        if (!is_array($package)) {
            $package = [];
        }

        return $this->pages->page('pages/contact', 'Plan your trip', $request, [
            'csrf' => Csrf::token(),
            'errors' => [],
            'old' => [],
            'package' => $package,
            'attribution' => $this->attribution($request),
            'turnstileSiteKey' => (string) $this->application->config()->get('tourkinet.turnstile.site_key', ''),
            'sent' => isset($request->query['sent']),
        ]);
    }

    public function submit(Request $request): Response
    {
        $data = [];
        foreach ([
            'inquiry_type', 'first_name', 'last_name', 'email', 'phone', 'destination',
            'travel_start_date', 'travel_end_date', 'adults', 'children', 'infants',
            'estimated_budget', 'message', 'package_hash', 'package_code', 'package_name',
            'utm_source', 'utm_medium', 'utm_campaign',
        ] as $field) {
            $data[$field] = $request->postString($field);
        }
        $data['inquiry_type'] = $data['inquiry_type'] !== '' ? $data['inquiry_type'] : 'regular';
        $data['adults'] = $data['adults'] !== '' ? $data['adults'] : '1';
        $data['children'] = $data['children'] !== '' ? $data['children'] : '0';
        $data['infants'] = $data['infants'] !== '' ? $data['infants'] : '0';
        $errors = [];
        if (!Csrf::verify($request->postString('_csrf'))) {
            $errors['_csrf'] = 'Your form session expired. Please try again.';
        }
        if ($request->postString('website') !== '') {
            $errors['form'] = 'Unable to submit your inquiry.';
        }
        if (!$this->rateLimiter->allow('contact', $request->clientIp(), 5, 600)) {
            $errors['form'] = 'Please wait before submitting another inquiry.';
        }
        $errors += Validator::fields($data, [
            'inquiry_type' => ['allowed' => ['regular', 'package']],
            'first_name' => ['max' => 100],
            'last_name' => ['max' => 100],
            'email' => ['email' => true, 'max' => 254],
            'phone' => ['phone' => true, 'max' => 30],
            'destination' => ['max' => 120],
            'travel_start_date' => ['max' => 10],
            'travel_end_date' => ['max' => 10],
            'adults' => ['max' => 2],
            'children' => ['max' => 2],
            'infants' => ['max' => 2],
            'estimated_budget' => ['max' => 24],
            'message' => ['max' => 5000],
            'package_hash' => ['max' => 255],
            'package_code' => ['max' => 120],
            'package_name' => ['max' => 200],
            'utm_source' => ['max' => 200],
            'utm_medium' => ['max' => 200],
            'utm_campaign' => ['max' => 200],
        ]);
        if ($data['email'] === '' && $data['phone'] === '') {
            $errors['contact_method'] = 'Enter an email address or phone number so we can reach you.';
        }
        foreach (['adults' => [1, 50], 'children' => [0, 50], 'infants' => [0, 50]] as $field => [$minimum, $maximum]) {
            if (filter_var($data[$field], FILTER_VALIDATE_INT) === false
                || (int) $data[$field] < $minimum
                || (int) $data[$field] > $maximum) {
                $errors[$field] = 'Enter a whole number between ' . $minimum . ' and ' . $maximum . '.';
            }
        }
        if ($data['estimated_budget'] !== '' && (!is_numeric($data['estimated_budget']) || !is_finite((float) $data['estimated_budget']) || (float) $data['estimated_budget'] < 0)) {
            $errors['estimated_budget'] = 'Enter a valid non-negative budget.';
        }
        foreach (['travel_start_date', 'travel_end_date'] as $field) {
            if ($data[$field] !== '' && !$this->validDate($data[$field])) {
                $errors[$field] = 'Enter a valid date.';
            }
        }
        if ($data['travel_start_date'] !== '' && $data['travel_end_date'] !== ''
            && $this->validDate($data['travel_start_date']) && $this->validDate($data['travel_end_date'])
            && $data['travel_end_date'] < $data['travel_start_date']) {
            $errors['travel_end_date'] = 'The end date must be on or after the start date.';
        }
        if ($data['inquiry_type'] === 'package'
            && $data['package_hash'] === '' && $data['package_code'] === '' && $data['package_name'] === '') {
            $errors['package_name'] = 'Enter the package name for a package inquiry.';
        }
        if (!$this->verifyTurnstile($request->postString('cf-turnstile-response'))) {
            $errors['form'] = 'Please complete the human verification challenge.';
        }

        if ($errors !== []) {
            return $this->formResponse($request, $data, $errors, 422);
        }

        $payload = array_intersect_key($data, array_flip([
            'inquiry_type', 'first_name', 'last_name', 'email', 'phone', 'destination',
            'travel_start_date', 'travel_end_date', 'adults', 'children', 'infants',
            'estimated_budget', 'message', 'package_hash', 'package_code', 'package_name',
            'utm_source', 'utm_medium', 'utm_campaign',
        ]));
        $payload['landing_page'] = rtrim((string) $this->application->config()->get('app.url', ''), '/') . $request->path;
        foreach (['adults', 'children', 'infants'] as $field) {
            $payload[$field] = (int) $payload[$field];
        }
        if ($payload['estimated_budget'] === '') {
            unset($payload['estimated_budget']);
        } else {
            $payload['estimated_budget'] = (float) $payload['estimated_budget'];
        }
        foreach ([
            'travel_start_date', 'travel_end_date', 'destination', 'email', 'phone',
            'first_name', 'last_name', 'message', 'package_hash', 'package_code',
            'package_name', 'utm_source', 'utm_medium', 'utm_campaign',
        ] as $field) {
            if ($payload[$field] === '') {
                unset($payload[$field]);
            }
        }
        if ($payload['inquiry_type'] !== 'package') {
            unset($payload['package_hash'], $payload['package_code'], $payload['package_name']);
        }
        if (!$this->inquiries->submit($payload, $request)) {
            return $this->formResponse($request, $data, [
                'form' => 'We could not send your inquiry right now. Please try again later.',
            ], 503);
        }

        return Response::redirect('/contact?sent=1');
    }

    private function formResponse(Request $request, array $data, array $errors, int $status): Response
    {
        $package = $data['inquiry_type'] === 'package' ? [
            'package_hash' => $data['package_hash'],
            'package_code' => $data['package_code'],
            'name' => $data['package_name'],
        ] : [];
        return $this->pages->page('pages/contact', 'Plan your trip', $request, [
            'csrf' => Csrf::token(),
            'errors' => $errors,
            'old' => $data,
            'package' => $package,
            'attribution' => $data,
            'turnstileSiteKey' => (string) $this->application->config()->get('tourkinet.turnstile.site_key', ''),
        ], $status);
    }

    private function attribution(Request $request): array
    {
        $result = [];
        foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $field) {
            $value = $request->queryString($field);
            if ($value !== '') {
                $result[$field] = function_exists('mb_substr') ? mb_substr($value, 0, 200) : substr($value, 0, 200);
            }
        }
        return $result;
    }

    private function verifyTurnstile(string $token): bool
    {
        $siteKey = (string) $this->application->config()->get('tourkinet.turnstile.site_key', '');
        $secret = (string) $this->application->config()->get('tourkinet.turnstile.secret_key', '');
        if ($siteKey === '' && $secret === '') {
            return true;
        }
        if ($siteKey === '' || $secret === '' || $token === '') {
            return false;
        }
        try {
            $response = $this->http->request(
                'POST',
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                ['Content-Type: application/x-www-form-urlencoded', 'Accept: application/json'],
                http_build_query(['secret' => $secret, 'response' => $token]),
                5,
            );
        } catch (\Throwable) {
            return false;
        }
        $decoded = json_decode($response->body, true);
        $expectedHost = strtolower((string) parse_url((string) $this->application->config()->get('app.url', ''), PHP_URL_HOST));
        return $response->status === 200
            && is_array($decoded)
            && ($decoded['success'] ?? false) === true
            && $expectedHost !== ''
            && strtolower((string) ($decoded['hostname'] ?? '')) === $expectedHost;
    }

    private function validDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
