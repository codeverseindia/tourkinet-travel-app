</main>
<footer class="site-footer">
  <div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
      <span>&copy; <?= date('Y') ?> <?= e($website['company_name'] ?? config('website.company_name')) ?></span>
      <span><a href="/privacy-policy">Privacy</a> &middot; <a href="/terms-and-conditions">Terms</a> &middot; <a href="mailto:<?= e($website['email'] ?? '') ?>">Email us</a></span>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous"></script>
</body>
</html>
