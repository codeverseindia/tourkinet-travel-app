<?php
declare(strict_types=1);
$company = (string) ($website['company_name'] ?? config('website.company_name'));
$metaTitle = (string) ($meta_title ?? $title ?? config('website.meta_title'));
$metaDescription = (string) ($description ?? config('website.meta_description'));
$metaKeywords = is_array($keywords ?? null)
    ? implode(', ', array_values(array_filter(array_map(static fn($keyword): string => trim(text_value($keyword)), $keywords), static fn(string $keyword): bool => $keyword !== '')))
    : '';
$socialImage = safe_image_url($ogImage ?? null);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(page_title($metaTitle)) ?></title>
  <meta name="description" content="<?= e($metaDescription) ?>">
  <?php if ($metaKeywords !== ''): ?><meta name="keywords" content="<?= e($metaKeywords) ?>"><?php endif; ?>
  <link rel="canonical" href="<?= e($canonical ?? config('app.url')) ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e(page_title($metaTitle)) ?>">
  <meta property="og:description" content="<?= e($metaDescription) ?>">
  <meta property="og:url" content="<?= e($canonical ?? config('app.url')) ?>">
  <?php if ($socialImage !== null): ?><meta property="og:image" content="<?= e($socialImage) ?>"><meta name="twitter:card" content="summary_large_image"><meta name="twitter:image" content="<?= e($socialImage) ?>"><?php endif; ?>
  <?php
  $siteUrl = rtrim(text_value($website['url'] ?? config('app.url')), '/');
  $logoUrl = text_value($website['logo'] ?? '/assets/images/logo.svg');
  if (str_starts_with($logoUrl, '/')) {
      $logoUrl = $siteUrl . $logoUrl;
  }
  render_json_ld([
      '@context' => 'https://schema.org',
      '@type' => 'Organization',
      'name' => $company,
      'url' => $siteUrl,
      'logo' => $logoUrl,
  ]);
  ?>
  <link rel="icon" href="/assets/images/logo.svg" type="image/svg+xml">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
  <link rel="stylesheet" href="/assets/css/app.css">
  <link rel="stylesheet" href="/theme.css">
</head>
<body>
<header class="site-header">
  <nav class="navbar navbar-expand-lg bg-white" aria-label="Main navigation">
    <div class="container">
      <a class="navbar-brand" href="/" aria-label="<?= e($company) ?> home"><img src="<?= e($website['logo'] ?? '/assets/images/logo.svg') ?>" width="180" height="40" alt="<?= e($company) ?>"></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="main-menu">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="/destinations">Destinations</a></li>
          <li class="nav-item"><a class="nav-link" href="/packages">Packages</a></li>
          <li class="nav-item"><a class="nav-link" href="/blogs">Travel stories</a></li>
          <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>
</header>
<main>
