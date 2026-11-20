<?php
declare(strict_types=1);
$title = 'Find your next journey';
$description = 'Discover thoughtful travel experiences and memorable destinations.';
?>
<section class="hero py-5">
  <div class="container py-lg-5">
    <div class="col-lg-9">
      <p class="eyebrow mb-3">Travel, thoughtfully planned</p>
      <h1 class="display-2 fw-bold">Go a little further. Feel a lot more.</h1>
      <p class="lead">Find inspiring places, local experiences and trips made around the way you love to travel.</p>
      <a class="btn btn-primary btn-lg mt-2" href="/packages">Explore our trips</a>
    </div>
  </div>
</section>
<?php if (!empty($destinations)): ?>
<section class="section-soft py-5">
  <div class="container">
    <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-3 mb-4">
      <div><p class="eyebrow mb-2">Places to discover</p><h2 class="mb-0">Featured destinations</h2></div>
      <a href="/destinations">Explore destinations</a>
    </div>
    <div class="row g-4">
      <?php foreach (array_slice($destinations, 0, 3) as $destination): ?>
        <?php if (!is_array($destination)) continue; ?>
        <article class="col-12 col-md-6 col-lg-4">
          <div class="card h-100">
            <?php if (($image = safe_image_url($destination['image'] ?? null)) !== null): ?><img class="card-img-top" src="<?= e($image) ?>" width="640" height="400" loading="lazy" alt="<?= e($destination['name'] ?? 'Destination') ?>"><?php endif; ?>
            <div class="card-body"><h3 class="h5"><?= e($destination['name'] ?? 'Destination') ?></h3><p><?= e($destination['summary'] ?? '') ?></p><a href="/packages?<?= e(http_build_query(['destination' => $destination['filter'] ?? $destination['name'] ?? ''])) ?>">View related packages</a></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<section class="py-5">
  <div class="container">
    <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-3 mb-4">
      <div><p class="eyebrow mb-2">Start exploring</p><h2 class="mb-0">Featured journeys</h2></div>
      <a href="/packages">View all packages</a>
    </div>
    <?php $items = api_items($packages ?? []); ?>
    <?php if ($items === []): ?>
      <p class="alert alert-info">Our journeys are being refreshed. Please check back soon or <a href="/contact">contact us</a> to plan a trip.</p>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach (array_slice($items, 0, 3) as $item): ?>
          <?php if (!is_array($item)) continue; $slug = valid_slug(text_value($item['slug'] ?? '')); if ($slug === null) continue; ?>
          <?php $thumbnail = $item['thumbnail']['url'] ?? null; ?>
          <article class="col-12 col-md-6 col-lg-4"><div class="card h-100">
            <?php if (($image = safe_image_url($thumbnail)) !== null): ?><img class="card-img-top" src="<?= e($image) ?>" width="640" height="400" loading="lazy" alt="<?= e($item['title'] ?? 'Travel package') ?>"><?php endif; ?>
            <div class="card-body"><h3 class="h5"><a href="/packages/<?= e(rawurlencode($slug)) ?>"><?= e($item['title'] ?? $item['name'] ?? 'Explore this journey') ?></a></h3><p class="mb-0"><?= e($item['overview'] ?? $item['short_description'] ?? $item['description'] ?? '') ?></p></div>
          </div></article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<section class="py-5"><div class="container"><div class="prose"><p class="eyebrow">Why travel with us</p><h2>Thoughtful planning, from first idea to final detail</h2><p>Get clear itineraries, useful local insight, and personal support shaped around the way you want to travel.</p><a class="btn btn-outline-primary" href="/about">How we help</a></div></div></section>
<section class="section-soft py-5"><div class="container"><div class="row align-items-center g-4"><div class="col-lg-8"><p class="eyebrow mb-2">Your next journey starts here</p><h2>Tell us what you have in mind.</h2><p class="mb-0">Share your interests, timing, and questions. Our team can help you explore the options.</p></div><div class="col-lg-4 text-lg-end"><a class="btn btn-primary btn-lg" href="/contact">Talk to our team</a></div></div></div></section>
