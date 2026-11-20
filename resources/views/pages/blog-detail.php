<?php
declare(strict_types=1);
$headline = text_value($blog['title'] ?? $blog['name'] ?? 'Travel story');
$seo = is_array($blog['seo'] ?? null) ? $blog['seo'] : [];
$title = text_value($seo['title'] ?? $headline);
$description = text_value($seo['description'] ?? $blog['short_intro'] ?? $blog['excerpt'] ?? $blog['summary'] ?? config('website.meta_description'));
$keywords = is_array($seo['keywords'] ?? null) ? $seo['keywords'] : [];
$canonical = safe_canonical_url($seo['canonical_url'] ?? null)
    ?? rtrim((string) config('app.url'), '/') . '/blogs/' . rawurlencode((string) ($blog['slug'] ?? ''));
$images = is_array($blog['images'] ?? null) ? $blog['images'] : [];
$bannerImage = is_array($images['banner'] ?? null) ? $images['banner'] : [];
$thumbnail = is_array($images['thumbnail'] ?? null) ? $images['thumbnail'] : [];
$banner = $bannerImage['url'] ?? $blog['image'] ?? null;
$bannerUrl = safe_image_url($banner);
$thumbnailUrl = safe_image_url($thumbnail['url'] ?? null);
$ogImage = safe_image_url($seo['og_image_url'] ?? null) ?? $bannerUrl;
$publishedAt = text_value($blog['published_at'] ?? '');
$publishedTimestamp = $publishedAt !== '' ? strtotime($publishedAt) : false;
$author = is_array($blog['author'] ?? null) ? $blog['author'] : [];
$authorEmail = text_value($author['email'] ?? '');
?>
<section class="py-5"><article class="container prose">
  <?php render_json_ld([
      '@context' => 'https://schema.org',
      '@type' => 'BreadcrumbList',
      'itemListElement' => [
          ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => rtrim((string) config('app.url'), '/') . '/'],
          ['@type' => 'ListItem', 'position' => 2, 'name' => 'Travel stories', 'item' => rtrim((string) config('app.url'), '/') . '/blogs'],
          ['@type' => 'ListItem', 'position' => 3, 'name' => $headline, 'item' => $canonical],
      ],
  ]); ?>
  <p class="breadcrumbs small"><a href="/">Home</a> / <a href="/blogs">Stories</a> / <?= e($headline) ?></p>
  <h1 class="display-5 fw-semibold"><?= e($headline) ?></h1>
  <?php if ($bannerUrl !== null): ?>
    <figure class="my-3">
      <img class="detail-image img-fluid rounded" src="<?= e($bannerUrl) ?>" width="1200" height="750" fetchpriority="high" alt="<?= e($bannerImage['caption'] ?? $headline) ?>">
      <?php if (!empty($bannerImage['caption'])): ?><figcaption class="small text-secondary mt-2"><?= e($bannerImage['caption']) ?></figcaption><?php endif; ?>
    </figure>
  <?php endif; ?>
  <?php if ($thumbnailUrl !== null && $thumbnailUrl !== $bannerUrl): ?>
    <figure class="my-4">
      <img class="img-fluid rounded" src="<?= e($thumbnailUrl) ?>" loading="lazy" alt="<?= e($thumbnail['caption'] ?? $headline) ?>">
      <?php if (!empty($thumbnail['caption'])): ?><figcaption class="small text-secondary mt-2"><?= e($thumbnail['caption']) ?></figcaption><?php endif; ?>
    </figure>
  <?php endif; ?>
  <?php if (is_array($blog['category'] ?? null)): ?><p class="eyebrow"><?= e($blog['category']['name'] ?? '') ?></p><?php endif; ?>
  <p class="lead"><?= e($blog['short_intro'] ?? $blog['excerpt'] ?? $blog['summary'] ?? '') ?></p>
  <?php if ($publishedTimestamp !== false): ?><p class="small text-secondary">Published <?= e(date('F j, Y', $publishedTimestamp)) ?></p><?php endif; ?>
  <?php if (!empty($author['name'])): ?>
    <p class="small text-secondary mb-1">By <?= e($author['name']) ?><?php if (filter_var($authorEmail, FILTER_VALIDATE_EMAIL)): ?> · <a href="mailto:<?= e($authorEmail) ?>"><?= e($authorEmail) ?></a><?php endif; ?></p>
    <?php if (!empty($author['bio'])): ?><p><?= e($author['bio']) ?></p><?php endif; ?>
  <?php endif; ?>
  <?php if (!empty($blog['tags']) && is_array($blog['tags'])): ?><p class="small"><?php foreach ($blog['tags'] as $tag): ?><?php if (is_array($tag)): ?><span class="badge text-bg-light me-1"><?= e($tag['name'] ?? '') ?></span><?php endif; ?><?php endforeach; ?></p><?php endif; ?>
  <div><?= nl2br(e(strip_tags(text_value($blog['content'] ?? $blog['body'] ?? '')))) ?></div>
</article></section>
