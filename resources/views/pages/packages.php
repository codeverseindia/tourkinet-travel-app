<?php
declare(strict_types=1);
$title = 'Travel packages';
$description = 'Explore thoughtfully planned tours and travel packages.';
$items = api_items($packages ?? []);
$queryFilters = $filters ?? [];
$meta = is_array($packages['meta'] ?? null) ? $packages['meta'] : [];
?>
<section class="py-5"><div class="container">
  <p class="eyebrow">Find your next trip</p>
  <h1 class="display-5 fw-semibold mb-4">Travel packages</h1>
  <form action="/packages" method="get" class="card card-body bg-light border-0 mb-4">
    <div class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label" for="destination">Destination</label>
        <input class="form-control" id="destination" name="destination" maxlength="120" value="<?= e($queryFilters['destination'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="theme">Theme</label>
        <input class="form-control" id="theme" name="theme" maxlength="120" value="<?= e($queryFilters['theme'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="type">Tour type</label>
        <input class="form-control" id="type" name="type" maxlength="120" value="<?= e($queryFilters['type'] ?? '') ?>">
      </div>
      <div class="col-sm-6 col-md-3">
        <label class="form-label" for="duration_min">Minimum days</label>
        <input class="form-control" type="number" id="duration_min" name="duration_min" min="1" max="9999" value="<?= e($queryFilters['duration_min'] ?? '') ?>">
      </div>
      <div class="col-sm-6 col-md-3">
        <label class="form-label" for="duration_max">Maximum days</label>
        <input class="form-control" type="number" id="duration_max" name="duration_max" min="1" max="9999" value="<?= e($queryFilters['duration_max'] ?? '') ?>">
      </div>
      <div class="col-sm-6 col-md-3">
        <label class="form-label" for="is_featured">Featured</label>
        <select class="form-select" id="is_featured" name="is_featured">
          <option value="">All packages</option>
          <option value="true" <?= in_array(($queryFilters['is_featured'] ?? ''), ['true', '1'], true) ? 'selected' : '' ?>>Featured only</option>
          <option value="false" <?= in_array(($queryFilters['is_featured'] ?? ''), ['false', '0'], true) ? 'selected' : '' ?>>Not featured</option>
        </select>
      </div>
      <div class="col-sm-6 col-md-3 d-flex gap-2">
        <button class="btn btn-primary" type="submit">Apply filters</button>
        <a class="btn btn-outline-secondary" href="/packages">Clear</a>
      </div>
    </div>
  </form>

  <?php if ($items === []): ?>
    <p class="alert alert-info">No packages are available for these filters. Please <a href="/contact">contact us</a> for help planning your trip.</p>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($items as $item): ?>
        <?php
        if (!is_array($item)) continue;
        $slug = valid_slug(text_value($item['slug'] ?? ''));
        if ($slug === null) continue;
        $name = $item['title'] ?? $item['name'] ?? 'Travel package';
        $thumbnail = $item['thumbnail']['url'] ?? $item['image'] ?? null;
        $duration = is_array($item['duration'] ?? null) ? $item['duration'] : [];
        $destinations = is_array($item['destinations'] ?? null) ? $item['destinations'] : [];
        ?>
        <article class="col-12 col-md-6 col-lg-4">
          <div class="card h-100">
            <?php if (($image = safe_image_url($thumbnail)) !== null): ?><img class="card-img-top" src="<?= e($image) ?>" width="640" height="400" loading="lazy" alt="<?= e($name) ?>"><?php endif; ?>
            <div class="card-body d-flex flex-column">
              <?php if (!empty($item['is_featured'])): ?><span class="badge text-bg-light align-self-start mb-2">Featured</span><?php endif; ?>
              <h2 class="h5"><a href="/packages/<?= e(rawurlencode($slug)) ?>"><?= e($name) ?></a></h2>
              <p><?= e($item['overview'] ?? $item['short_description'] ?? '') ?></p>
              <?php if ($destinations !== []): ?><p class="small text-secondary mb-2"><?= e(implode(', ', array_map(static fn($place): string => is_array($place) ? text_value($place['name'] ?? '') : text_value($place), $destinations))) ?></p><?php endif; ?>
              <div class="mt-auto d-flex justify-content-between align-items-end gap-2">
                <span class="small text-secondary">
                  <?php if (!empty($duration['days'])): ?><?= e($duration['days']) ?> days<?php endif; ?>
                  <?php if (!empty($duration['nights'])): ?> · <?= e($duration['nights']) ?> nights<?php endif; ?>
                </span>
                <?php if (isset($item['starting_price'])): ?><strong><?= e($item['currency'] ?? 'INR') ?> <?= e($item['starting_price']) ?></strong><?php endif; ?>
              </div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php
    $lastPage = max(1, (int) ($meta['last_page'] ?? $queryFilters['page'] ?? 1));
    $currentPage = min($lastPage, max(1, (int) ($meta['current_page'] ?? $queryFilters['page'] ?? 1)));
    $firstPage = max(1, $currentPage - 2);
    $finalPage = min($lastPage, $currentPage + 2);
    ?>
    <?php if ($lastPage > 1): ?>
      <nav class="mt-5" aria-label="Package pages">
        <ul class="pagination justify-content-center">
          <?php for ($page = $firstPage; $page <= $finalPage; $page++): ?>
            <li class="page-item <?= $page === $currentPage ? 'active' : '' ?>"><a class="page-link" href="/packages?<?= e(http_build_query(array_merge($queryFilters, ['page' => $page]))) ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>><?= e($page) ?></a></li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div></section>
