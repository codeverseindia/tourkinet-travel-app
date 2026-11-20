<?php
declare(strict_types=1);
$summary = is_array($package['package'] ?? null) ? $package['package'] : $package;
$name = text_value($summary['title'] ?? $summary['name'] ?? 'Travel package');
$seo = is_array($package['seo'] ?? null) ? $package['seo'] : [];
$title = text_value($seo['title'] ?? $name);
$description = text_value($seo['description'] ?? $summary['overview'] ?? config('website.meta_description'));
$keywords = is_array($seo['keywords'] ?? null) ? $seo['keywords'] : [];
$slug = valid_slug(text_value($summary['slug'] ?? '')) ?? '';
$canonical = safe_canonical_url($seo['canonical_url'] ?? null)
    ?? rtrim((string) config('app.url'), '/') . '/packages/' . rawurlencode($slug);
$images = is_array($package['images'] ?? null) ? $package['images'] : [];
$bannerImage = is_array($images['banner'] ?? null) ? $images['banner'] : [];
$thumbnailValue = $images['thumbnail'] ?? $summary['thumbnail'] ?? [];
$thumbnail = is_array($thumbnailValue) ? $thumbnailValue : [];
$banner = $bannerImage['url'] ?? $thumbnail['url'] ?? null;
$bannerUrl = safe_image_url($banner);
$ogImage = $bannerUrl;
$gallery = is_array($images['gallery'] ?? null) ? $images['gallery'] : [];
$imageItems = [];
$shownImageUrls = $bannerUrl !== null ? [$bannerUrl => true] : [];
$imageCandidates = $thumbnail !== [] ? [['label' => 'Thumbnail', 'image' => $thumbnail]] : [];
foreach ($gallery as $galleryImage) {
    if (is_array($galleryImage)) {
        $imageCandidates[] = ['label' => 'Gallery', 'image' => $galleryImage];
    }
}
foreach ($imageCandidates as $candidate) {
    $imageUrl = safe_image_url($candidate['image']['url'] ?? null);
    if ($imageUrl !== null && !isset($shownImageUrls[$imageUrl])) {
        $imageItems[] = $candidate + ['url' => $imageUrl];
        $shownImageUrls[$imageUrl] = true;
    }
}
$itinerary = is_array($package['itinerary'] ?? null) ? $package['itinerary'] : [];
$inclusions = is_array($package['inclusions'] ?? null) ? $package['inclusions'] : [];
$exclusions = is_array($package['exclusions'] ?? null) ? $package['exclusions'] : [];
$terms = is_array($package['terms_conditions'] ?? null) ? $package['terms_conditions'] : [];
$theme = is_array($summary['theme'] ?? null) ? text_value($summary['theme']['name'] ?? $summary['theme']['slug'] ?? '') : text_value($summary['theme'] ?? '');
$tourType = is_array($summary['tour_type'] ?? null) ? text_value($summary['tour_type']['name'] ?? $summary['tour_type']['slug'] ?? '') : text_value($summary['tour_type'] ?? '');
$packageCode = text_value($summary['package_code'] ?? $summary['code'] ?? '');
?>
<section class="py-5"><article class="container">
  <?php render_json_ld([
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      'itemListElement' => [
          ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim((string) config('app.url'), '/') . '/'],
          ['@type' => 'ListItem', 'position' => 2, 'name' => 'Packages', 'item' => rtrim((string) config('app.url'), '/') . '/packages'],
          ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $canonical],
      ],
  ]); ?>
  <p class="breadcrumbs small"><a href="/">Home</a> / <a href="/packages">Packages</a> / <?= e($name) ?></p>
  <div class="row g-5">
    <div class="col-lg-8">
      <p class="eyebrow">Travel package</p>
      <h1 class="display-5 fw-semibold"><?= e($name) ?></h1>
      <?php if ($bannerUrl !== null): ?>
        <figure class="my-3">
          <img class="detail-image img-fluid rounded w-100" src="<?= e($bannerUrl) ?>" width="1200" height="750" fetchpriority="high" alt="<?= e($bannerImage['caption'] ?? $name) ?>">
          <?php if (!empty($bannerImage['caption'])): ?><figcaption class="small text-secondary mt-2"><?= e($bannerImage['caption']) ?></figcaption><?php endif; ?>
        </figure>
      <?php endif; ?>
      <p class="lead"><?= e($summary['overview'] ?? $summary['short_description'] ?? '') ?></p>
      <?php if ($packageCode !== '' || $theme !== '' || $tourType !== ''): ?>
        <div class="d-flex flex-wrap gap-2 mb-4">
          <?php if ($packageCode !== ''): ?><span class="badge text-bg-light">Code: <?= e($packageCode) ?></span><?php endif; ?>
          <?php if ($theme !== ''): ?><span class="badge text-bg-light">Theme: <?= e($theme) ?></span><?php endif; ?>
          <?php if ($tourType !== ''): ?><span class="badge text-bg-light">Tour type: <?= e($tourType) ?></span><?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($itinerary !== []): ?>
        <section class="mt-5">
          <h2 class="h3 mb-3">Itinerary</h2>
          <div class="accordion" id="itinerary">
            <?php foreach ($itinerary as $index => $day): ?>
              <?php if (!is_array($day)) continue; $dayId = 'itinerary-day-' . (int) $index; ?>
              <div class="accordion-item">
                <h3 class="accordion-header" id="<?= e($dayId) ?>-heading">
                  <button class="accordion-button <?= $index === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($dayId) ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="<?= e($dayId) ?>">
                    Day <?= e($day['day'] ?? ($index + 1)) ?>: <?= e($day['title'] ?? 'Explore') ?>
                  </button>
                </h3>
                <div id="<?= e($dayId) ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="<?= e($dayId) ?>-heading" data-bs-parent="#itinerary">
                  <div class="accordion-body">
                    <p><?= e($day['description'] ?? '') ?></p>
                    <?php
                    $dayDestinations = is_array($day['destinations'] ?? null) ? $day['destinations'] : [];
                    $dayImages = is_array($day['images'] ?? null) ? $day['images'] : [];
                    ?>
                    <?php if ($dayDestinations !== []): ?>
                      <p class="small text-secondary">Stops: <?= e(implode(', ', array_map(static fn($place): string => is_array($place) ? text_value($place['name'] ?? '') : text_value($place), $dayDestinations))) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($day['overnight_location'])): ?><p class="small text-secondary mb-0">Overnight in <?= e($day['overnight_location']) ?></p><?php endif; ?>
                    <?php if ($dayImages !== []): ?>
                      <div class="row g-3 mt-2">
                        <?php foreach ($dayImages as $dayImage): ?>
                          <?php if (!is_array($dayImage) || ($dayImageUrl = safe_image_url($dayImage['url'] ?? null)) === null) continue; ?>
                          <figure class="col-sm-6 mb-0">
                            <img class="img-fluid rounded" src="<?= e($dayImageUrl) ?>" loading="lazy" alt="<?= e($dayImage['caption'] ?? $day['title'] ?? $name) ?>">
                            <?php if (!empty($dayImage['caption'])): ?><figcaption class="small text-secondary mt-1"><?= e($dayImage['caption']) ?></figcaption><?php endif; ?>
                          </figure>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($imageItems !== []): ?>
        <section class="mt-5">
          <h2 class="h3 mb-3">Gallery</h2>
          <div class="row g-3">
            <?php foreach ($imageItems as $item): ?>
            <?php $image = $item['image']; ?>
              <figure class="col-sm-6 mb-0">
              <img class="img-fluid rounded" src="<?= e($item['url']) ?>" loading="lazy" alt="<?= e($image['caption'] ?? $name) ?>">
                <figcaption class="small text-secondary mt-1"><?= e($image['caption'] ?? $image['purpose'] ?? $item['label']) ?></figcaption>
              </figure>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php foreach ([
          'What’s included' => [$inclusions, $package['inclusion_details'] ?? []],
          'What’s excluded' => [$exclusions, $package['exclusion_details'] ?? []],
      ] as $sectionTitle => [$lines, $details]): ?>
        <?php if ($lines !== [] || (is_array($details) && $details !== [])): ?>
          <section class="mt-5">
            <h2 class="h3 mb-3"><?= e($sectionTitle) ?></h2>
            <?php if ($lines !== []): ?><ul><?php foreach ($lines as $line): ?><li><?= e($line) ?></li><?php endforeach; ?></ul><?php endif; ?>
            <?php if (is_array($details)): ?>
              <?php foreach ($details as $detail): ?>
                <?php if (!is_array($detail) || !isset($detail['description'])) continue; ?>
                <?php if (!empty($detail['title'])): ?><h3 class="h5 mt-3"><?= e($detail['title']) ?></h3><?php endif; ?>
                <p><?= nl2br(e(strip_tags(text_value($detail['description'])))) ?></p>
              <?php endforeach; ?>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      <?php endforeach; ?>

      <?php if ($terms !== []): ?>
        <section class="mt-5">
          <h2 class="h3 mb-3">Terms and conditions</h2>
          <?php foreach ($terms as $term): ?>
            <?php if (!is_array($term)) continue; ?>
            <article class="mb-3">
              <h3 class="h5"><?= e($term['title'] ?? '') ?></h3>
              <?php foreach (['description', 'content'] as $field): ?>
                <?php if (!empty($term[$field])): ?><p><?= nl2br(e(strip_tags(text_value($term[$field])))) ?></p><?php endif; ?>
              <?php endforeach; ?>
            </article>
          <?php endforeach; ?>
        </section>
      <?php endif; ?>
    </div>
    <aside class="col-lg-4">
      <div class="card sticky-top">
        <div class="card-body">
          <?php if (!empty($summary['duration']['days'])): ?><p class="mb-2"><strong><?= e($summary['duration']['days']) ?> days</strong><?php if (!empty($summary['duration']['nights'])): ?> / <?= e($summary['duration']['nights']) ?> nights<?php endif; ?></p><?php endif; ?>
          <?php if (!empty($summary['starting_price'])): ?><p class="h4"><?= e($summary['currency'] ?? 'INR') ?> <?= e($summary['starting_price']) ?><span class="fs-6 fw-normal text-secondary"> / person</span></p><?php endif; ?>
          <?php if (!empty($summary['destinations']) && is_array($summary['destinations'])): ?>
            <p class="text-secondary"><?= e(implode(', ', array_map(static fn($place): string => is_array($place) ? text_value($place['name'] ?? '') : text_value($place), $summary['destinations']))) ?></p>
          <?php endif; ?>
          <a class="btn btn-primary w-100" href="/contact?<?= e(http_build_query(['package' => $slug])) ?>">Ask about this trip</a>
        </div>
      </div>
    </aside>
  </div>
</article></section>
