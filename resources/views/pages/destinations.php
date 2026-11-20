<?php
declare(strict_types=1);
$title = 'Destinations';
?>
<section class="py-5"><div class="container">
  <div class="prose mb-4"><p class="eyebrow">Where to next?</p><h1 class="display-5 fw-semibold">Explore destinations</h1><p>Browse a few places to inspire your plans. Destination details are editable in the client website configuration.</p></div>
  <?php if (!empty($destinations)): ?>
    <div class="row g-4">
      <?php foreach ($destinations as $destination): ?>
        <?php if (!is_array($destination)) continue; ?>
        <article class="col-12 col-md-6 col-lg-4"><div class="card h-100">
          <?php if (($image = safe_image_url($destination['image'] ?? null)) !== null): ?><img class="card-img-top" src="<?= e($image) ?>" width="640" height="400" loading="lazy" alt="<?= e($destination['name'] ?? 'Destination') ?>"><?php endif; ?>
          <div class="card-body d-flex flex-column"><h2 class="h5"><?= e($destination['name'] ?? 'Destination') ?></h2><p><?= e($destination['summary'] ?? '') ?></p><a class="mt-auto" href="/packages?<?= e(http_build_query(['destination' => $destination['filter'] ?? $destination['name'] ?? ''])) ?>">Browse related packages</a></div>
        </div></article>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="alert alert-info">No destinations have been added yet. Browse <a href="/packages">travel packages</a> or <a href="/contact">contact us</a>.</p>
  <?php endif; ?>
</div></section>
