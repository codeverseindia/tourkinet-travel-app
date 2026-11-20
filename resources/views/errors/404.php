<?php
declare(strict_types=1);
$title = $title ?? 'Page not found';
$message = $message ?? 'The page you are looking for could not be found.';
?>
<section class="py-5"><div class="container"><div class="prose"><p class="eyebrow">404</p><h1 class="display-5 fw-semibold"><?= e($title) ?></h1><p><?= e($message) ?></p><a class="btn btn-primary" href="/">Return home</a></div></div></section>
