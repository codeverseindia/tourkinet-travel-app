<?php
declare(strict_types=1);
$title = 'Travel stories';
$description = 'Ideas and stories to inspire your next journey.';
$items = api_items($blogs ?? []);
$queryFilters = $filters ?? [];
$meta = is_array($blogs['meta'] ?? null) ? $blogs['meta'] : [];
?>
<section class="py-5"><div class="container">
  <p class="eyebrow">Ideas for the road</p><h1 class="display-5 fw-semibold mb-4">Travel stories</h1>
  <form action="/blogs" method="get" class="row g-2 mb-4">
    <div class="col-md-4"><label class="visually-hidden" for="search">Search stories</label><input class="form-control" id="search" name="search" placeholder="Search stories" maxlength="120" value="<?= e($queryFilters['search'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="visually-hidden" for="category">Category</label><input class="form-control" id="category" name="category" placeholder="Category" maxlength="120" value="<?= e($queryFilters['category'] ?? '') ?>"></div>
    <div class="col-md-3"><label class="visually-hidden" for="tag">Tag</label><input class="form-control" id="tag" name="tag" placeholder="Tag" maxlength="120" value="<?= e($queryFilters['tag'] ?? '') ?>"></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary" type="submit">Search</button></div>
  </form>
  <?php if ($items === []): ?><p class="alert alert-info">No stories match your search. In the meantime, <a href="/packages">explore our journeys</a>.</p>
  <?php else: ?><div class="row g-4">
    <?php foreach ($items as $item): ?>
      <?php
      if (!is_array($item)) continue;
      $slug = valid_slug(text_value($item['slug'] ?? ''));
      if ($slug === null) continue;
      $thumbnail = $item['images']['thumbnail']['url'] ?? $item['image'] ?? null;
      ?>
      <article class="col-12 col-md-6 col-lg-4"><div class="card h-100">
        <?php if (($image = safe_image_url($thumbnail)) !== null): ?><img class="card-img-top" src="<?= e($image) ?>" width="640" height="400" loading="lazy" alt="<?= e($item['title'] ?? 'Travel story') ?>"><?php endif; ?>
        <div class="card-body">
          <?php if (is_array($item['category'] ?? null)): ?><p class="eyebrow mb-2"><?= e($item['category']['name'] ?? '') ?></p><?php endif; ?>
          <h2 class="h5"><a href="/blogs/<?= e(rawurlencode($slug)) ?>"><?= e($item['title'] ?? $item['name'] ?? 'Travel story') ?></a></h2>
          <p class="mb-0"><?= e($item['short_intro'] ?? $item['excerpt'] ?? $item['summary'] ?? '') ?></p>
        </div>
      </div></article>
    <?php endforeach; ?>
  </div>
    <?php
    $lastPage = max(1, (int) ($meta['last_page'] ?? $queryFilters['page'] ?? 1));
    $currentPage = min($lastPage, max(1, (int) ($meta['current_page'] ?? $queryFilters['page'] ?? 1)));
    $firstPage = max(1, $currentPage - 2);
    $finalPage = min($lastPage, $currentPage + 2);
    ?>
    <?php if ($lastPage > 1): ?><nav class="mt-5" aria-label="Story pages"><ul class="pagination justify-content-center">
      <?php for ($page = $firstPage; $page <= $finalPage; $page++): ?><li class="page-item <?= $page === $currentPage ? 'active' : '' ?>"><a class="page-link" href="/blogs?<?= e(http_build_query(array_merge($queryFilters, ['page' => $page]))) ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>><?= e($page) ?></a></li><?php endfor; ?>
    </ul></nav><?php endif; ?>
  <?php endif; ?>
</div></section>
