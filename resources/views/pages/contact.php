<?php
declare(strict_types=1);
$title = 'Plan your trip';
$packageHash = text_value($package['package_hash'] ?? '');
$packageCode = text_value($package['package_code'] ?? '');
$packageName = text_value($package['name'] ?? $package['title'] ?? '');
$packageSelected = $packageHash !== '' || $packageCode !== '' || $packageName !== '';
$old = $old ?? [];
$errors = $errors ?? [];
$attribution = $attribution ?? [];
?>
<section class="py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10 col-xl-9">
        <p class="eyebrow mb-2">Let's plan something</p>
        <h1 class="display-5 fw-semibold">Tell us about your trip.</h1>
        <p class="lead text-secondary mb-4">Share your plans and our team will be in touch.</p>
        <?php if (!empty($sent)): ?><p class="alert alert-success" role="status">Thanks for reaching out. We have received your inquiry.</p><?php endif; ?>
        <?php foreach (['form', '_csrf', 'contact_method'] as $errorKey): ?>
          <?php if (isset($errors[$errorKey])): ?><p class="alert alert-danger" role="alert"><?= e($errors[$errorKey]) ?></p><?php endif; ?>
        <?php endforeach; ?>

        <form action="/contact" method="post" class="card shadow-sm">
          <div class="card-body p-4 p-md-5">
            <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
            <div class="honeypot" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <?php if ($packageSelected): ?>
              <input type="hidden" name="inquiry_type" value="package">
              <input type="hidden" name="package_hash" value="<?= e($packageHash) ?>">
              <input type="hidden" name="package_code" value="<?= e($packageCode) ?>">
              <input type="hidden" name="package_name" value="<?= e($packageName) ?>">
              <div class="alert alert-light border mb-4">
                <span class="form-text d-block">Asking about</span>
                <strong><?= e($packageName !== '' ? $packageName : ($packageCode !== '' ? $packageCode : $packageHash)) ?></strong>
              </div>
            <?php else: ?>
              <div class="mb-4">
                <label class="form-label" for="inquiry_type">What can we help with?</label>
                <select class="form-select" id="inquiry_type" name="inquiry_type">
                  <option value="regular" <?= ($old['inquiry_type'] ?? 'regular') === 'regular' ? 'selected' : '' ?>>Plan a trip</option>
                  <option value="package" <?= ($old['inquiry_type'] ?? '') === 'package' ? 'selected' : '' ?>>Ask about a package</option>
                </select>
                <?php if (isset($errors['inquiry_type'])): ?><div class="invalid-feedback d-block"><?= e($errors['inquiry_type']) ?></div><?php endif; ?>
              </div>
              <div class="mb-4">
                <label class="form-label" for="package_name">Package name <span class="text-secondary">(for package inquiries)</span></label>
                <input class="form-control" id="package_name" name="package_name" maxlength="200" value="<?= e($old['package_name'] ?? '') ?>">
                <?php if (isset($errors['package_name'])): ?><div class="invalid-feedback d-block"><?= e($errors['package_name']) ?></div><?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label" for="first_name">First name</label>
                <input class="form-control" id="first_name" name="first_name" maxlength="100" autocomplete="given-name" value="<?= e($old['first_name'] ?? '') ?>">
                <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback d-block"><?= e($errors['first_name']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="last_name">Last name</label>
                <input class="form-control" id="last_name" name="last_name" maxlength="100" autocomplete="family-name" value="<?= e($old['last_name'] ?? '') ?>">
                <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback d-block"><?= e($errors['last_name']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" type="email" id="email" name="email" maxlength="254" autocomplete="email" value="<?= e($old['email'] ?? '') ?>">
                <?php if (isset($errors['email'])): ?><div class="invalid-feedback d-block"><?= e($errors['email']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="phone">Phone</label>
                <input class="form-control" type="tel" id="phone" name="phone" maxlength="30" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>">
                <?php if (isset($errors['phone'])): ?><div class="invalid-feedback d-block"><?= e($errors['phone']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <div class="form-text">Please provide at least one: email or phone.</div>
              </div>
              <div class="col-12">
                <label class="form-label" for="destination">Preferred destination</label>
                <input class="form-control" id="destination" name="destination" maxlength="120" value="<?= e($old['destination'] ?? '') ?>">
                <?php if (isset($errors['destination'])): ?><div class="invalid-feedback d-block"><?= e($errors['destination']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="travel_start_date">Travel start date</label>
                <input class="form-control" type="date" id="travel_start_date" name="travel_start_date" value="<?= e($old['travel_start_date'] ?? '') ?>">
                <?php if (isset($errors['travel_start_date'])): ?><div class="invalid-feedback d-block"><?= e($errors['travel_start_date']) ?></div><?php endif; ?>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="travel_end_date">Travel end date</label>
                <input class="form-control" type="date" id="travel_end_date" name="travel_end_date" value="<?= e($old['travel_end_date'] ?? '') ?>">
                <?php if (isset($errors['travel_end_date'])): ?><div class="invalid-feedback d-block"><?= e($errors['travel_end_date']) ?></div><?php endif; ?>
              </div>
              <div class="col-sm-4">
                <label class="form-label" for="adults">Adults</label>
                <input class="form-control" type="number" id="adults" name="adults" min="1" max="50" step="1" value="<?= e($old['adults'] ?? '1') ?>">
                <?php if (isset($errors['adults'])): ?><div class="invalid-feedback d-block"><?= e($errors['adults']) ?></div><?php endif; ?>
              </div>
              <div class="col-sm-4">
                <label class="form-label" for="children">Children</label>
                <input class="form-control" type="number" id="children" name="children" min="0" max="50" step="1" value="<?= e($old['children'] ?? '0') ?>">
                <?php if (isset($errors['children'])): ?><div class="invalid-feedback d-block"><?= e($errors['children']) ?></div><?php endif; ?>
              </div>
              <div class="col-sm-4">
                <label class="form-label" for="infants">Infants</label>
                <input class="form-control" type="number" id="infants" name="infants" min="0" max="50" step="1" value="<?= e($old['infants'] ?? '0') ?>">
                <?php if (isset($errors['infants'])): ?><div class="invalid-feedback d-block"><?= e($errors['infants']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <label class="form-label" for="estimated_budget">Estimated budget</label>
                <input class="form-control" type="number" id="estimated_budget" name="estimated_budget" min="0" step="0.01" value="<?= e($old['estimated_budget'] ?? '') ?>">
                <?php if (isset($errors['estimated_budget'])): ?><div class="invalid-feedback d-block"><?= e($errors['estimated_budget']) ?></div><?php endif; ?>
              </div>
              <div class="col-12">
                <label class="form-label" for="message">Message</label>
                <textarea class="form-control" id="message" name="message" maxlength="5000" rows="5"><?= e($old['message'] ?? '') ?></textarea>
                <?php if (isset($errors['message'])): ?><div class="invalid-feedback d-block"><?= e($errors['message']) ?></div><?php endif; ?>
              </div>
              <?php foreach (['utm_source', 'utm_medium', 'utm_campaign'] as $field): ?>
                <input type="hidden" name="<?= e($field) ?>" value="<?= e($old[$field] ?? $attribution[$field] ?? '') ?>">
              <?php endforeach; ?>
              <?php if (!empty($turnstileSiteKey)): ?>
                <div class="col-12"><div class="cf-turnstile" data-sitekey="<?= e($turnstileSiteKey) ?>"></div><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script></div>
              <?php endif; ?>
              <div class="col-12 pt-2"><button class="btn btn-primary btn-lg" type="submit">Send inquiry</button></div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
