<?php $session = session(); ?>

<!-- ═══ HERO ══════════════════════════════════════════════════ -->
<section class="hero-section">
  <div class="hero-overlay"></div>
  <div class="container position-relative text-center text-white py-5">
    <h1 class="hero-title mb-3">
      Discover <span class="text-warning">Amazing Places</span>
    </h1>
    <p class="hero-subtitle mb-5">
      Search any real place in the world — parks, cafés, museums, restaurants and hidden gems
    </p>

    <!-- Hero search bar -->
    <div class="hero-search-wrap mx-auto position-relative">
      <div class="input-group input-group-lg shadow-lg">
        <span class="input-group-text bg-white border-0 ps-4">
          <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text" id="heroSearch" class="form-control border-0 fs-5"
               placeholder="Search any place — Eiffel Tower, Hyde Park, Dishoom…" autocomplete="off">
        <button class="btn btn-warning px-4 fw-semibold" id="heroSearchBtn">
          Explore <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </div>
      <div id="heroDropdown" class="search-dropdown search-dropdown-lg"></div>
    </div>

    <!-- Stats -->
    <div class="hero-stats d-flex justify-content-center gap-5 mt-5 pt-4 border-top border-white border-opacity-25">
      <div>
        <div class="stat-num"><?= $stats['places'] ?>+</div>
        <div class="stat-label">Places</div>
      </div>
      <div>
        <div class="stat-num"><?= $stats['cities'] ?>+</div>
        <div class="stat-label">Cities</div>
      </div>
      <div>
        <div class="stat-num"><?= $stats['countries'] ?>+</div>
        <div class="stat-label">Countries</div>
      </div>
    </div>
  </div>
</section>

<!-- ═══ CATEGORY CHIPS ════════════════════════════════════════ -->
<section class="py-5 bg-light">
  <div class="container">
    <h2 class="section-title text-center mb-4">Browse by Category</h2>
    <div class="category-grid">
      <?php foreach ($categories as $cat): ?>
      <a href="<?= base_url('places?category=' . $cat['slug']) ?>" class="category-chip"
         style="--cat-color:<?= esc($cat['color']) ?>">
        <div class="chip-icon"><i class="bi <?= esc($cat['icon']) ?>"></i></div>
        <div class="chip-name"><?= esc($cat['name']) ?></div>
        <div class="chip-count"><?= $cat['place_count'] ?> places</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ FEATURED PLACES ═══════════════════════════════════════ -->
<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="section-title mb-0">Featured Places</h2>
      <a href="<?= base_url('places') ?>" class="btn btn-outline-dark btn-sm">
        View all <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <!-- Weather widget for quick city check -->
    <div class="weather-bar mb-4" id="weatherBar">
      <div class="weather-bar-inner">
        <i class="bi bi-cloud-sun me-2 text-warning"></i>
        <span class="text-muted small me-2">Live weather:</span>
        <input type="text" id="weatherCity" class="weather-city-input" value="London" placeholder="Enter city">
        <button class="btn btn-sm btn-outline-warning ms-2" id="weatherBtn">Check</button>
        <div id="weatherResult" class="ms-3"></div>
      </div>
    </div>

    <div class="row g-4" id="featuredGrid">
      <?php foreach ($featured as $place): ?>
        <?= view('places/_card', ['place' => $place]) ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ CTA ═══════════════════════════════════════════════════ -->
<section class="cta-section py-5 text-white text-center">
  <div class="container">
    <i class="bi bi-compass display-4 mb-3 d-block text-warning"></i>
    <h2 class="fw-bold mb-3">Search any real place</h2>
    <p class="lead mb-4 opacity-75">Type any location in the world — we'll pull it in from Google and show you live weather, photos and reviews.</p>
    <a href="<?= base_url('places') ?>" class="btn btn-warning btn-lg fw-semibold px-5">
      Start Exploring <i class="bi bi-arrow-right ms-2"></i>
    </a>
  </div>
</section>
