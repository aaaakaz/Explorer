<?php $session = session(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'Explorer') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>

<!-- ═══ NAVBAR ════════════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg sticky-top" id="mainNav">
  <div class="container">
    <a class="navbar-brand" href="<?= base_url() ?>">
      <i class="bi bi-compass me-2"></i>Explorer
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navContent">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>"><i class="bi bi-house me-1"></i>Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('places') ?>"><i class="bi bi-grid me-1"></i>Explore</a></li>
      </ul>

      <!-- Global search -->
      <div class="nav-search-wrap position-relative me-3">
        <div class="input-group">
          <input type="text" id="navSearch" class="form-control nav-search-input"
                 placeholder="Search any place in the world…" autocomplete="off">
          <button class="btn btn-outline-warning" id="navSearchBtn"><i class="bi bi-search"></i></button>
        </div>
        <div id="navDropdown" class="search-dropdown"></div>
      </div>

      <!-- Auth + Theme -->
<ul class="navbar-nav">
    <li class="nav-item d-flex align-items-center me-2">
        <button class="btn btn-sm theme-toggle" id="themeToggle" title="Toggle theme">
            <i class="bi bi-moon-fill" id="themeIcon"></i>
        </button>
    </li>
    <?php if ($session->get('logged_in')): ?>
    <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
            <span class="nav-avatar" style="background:<?= esc($session->get('avatar_color')) ?>">
              <?= strtoupper(substr($session->get('username'), 0, 1)) ?>
            </span>
            <span><?= esc($session->get('username')) ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header small text-muted">Signed in</h6></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= base_url('profile') ?>">
              <i class="bi bi-person-circle me-2"></i>My Profile
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= base_url('logout') ?>">
              <i class="bi bi-box-arrow-right me-2"></i>Sign Out
            </a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('login') ?>"><i class="bi bi-box-arrow-in-right me-1"></i>Sign In</a></li>
        <li class="nav-item"><a class="btn btn-warning btn-sm fw-semibold ms-2 mt-1" href="<?= base_url('register') ?>">Join Free</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- Flash messages -->
<?php if ($session->getFlashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show mb-0 rounded-0 border-0 border-start border-4 border-success ps-4" role="alert">
  <i class="bi bi-check-circle-fill me-2"></i><?= esc($session->getFlashdata('success')) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?= $content ?>

<!-- ═══ FOOTER ═══════════════════════════════════════════════ -->
<footer class="site-footer mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand"><i class="bi bi-compass me-2"></i>Explorer</div>
        <p class="text-muted small mt-2">Discover amazing places worldwide — parks, cafés, museums, restaurants and hidden gems.</p>
      </div>
      <div class="col-lg-4">
        <h6 class="fw-semibold mb-3">Quick Links</h6>
        <div class="d-flex flex-column gap-1">
          <a href="<?= base_url() ?>" class="footer-link"><i class="bi bi-house me-2"></i>Home</a>
          <a href="<?= base_url('places') ?>" class="footer-link"><i class="bi bi-grid me-2"></i>All Places</a>
          <a href="<?= base_url('places?category=parks') ?>" class="footer-link"><i class="bi bi-tree me-2"></i>Parks</a>
          <a href="<?= base_url('places?category=museums') ?>" class="footer-link"><i class="bi bi-building me-2"></i>Museums</a>
        </div>
      </div>
      <div class="col-lg-4">
        <h6 class="fw-semibold mb-3">Built With</h6>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge bg-secondary">CodeIgniter 4</span>
          <span class="badge bg-secondary">PHP 8</span>
          <span class="badge bg-secondary">MySQL</span>
          <span class="badge bg-secondary">Bootstrap 5</span>
          <span class="badge bg-secondary">Google Maps</span>
          <span class="badge bg-secondary">OpenWeatherMap</span>
          <span class="badge bg-secondary">Unsplash</span>
        </div>
      </div>
    </div>
    <hr class="border-secondary mt-4">
    <p class="text-center text-muted small mb-0">&copy; <?= date('Y') ?> Explorer — 6CS028 Advanced Web Technologies</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>const BASE_URL = '<?= base_url() ?>';</script>
<script src="<?= base_url('js/main.js') ?>"></script>
</body>
</html>
