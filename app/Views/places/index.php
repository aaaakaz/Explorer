<!-- ═══ PAGE HEADER ══════════════════════════════════════════ -->
<div class="page-header py-4">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
        <li class="breadcrumb-item active text-white">Places</li>
      </ol>
    </nav>
    <h1 class="fw-bold text-white mb-0">
      <i class="bi bi-compass me-2 text-warning"></i>
      <?= $search ? 'Results for "' . esc($search) . '"' : 'All Places' ?>
    </h1>
    <p class="text-white-50 mb-0 mt-1" id="resultsLabel"><?= $total ?> places found</p>
  </div>
</div>

<div class="container py-4">
  <div class="row g-4">

    <!-- ══ SIDEBAR ══════════════════════════════════════════ -->
    <div class="col-lg-3">
      <div class="filter-sidebar">

        <!-- Search -->
        <div class="filter-block">
          <h6 class="filter-heading"><i class="bi bi-search me-2"></i>Search</h6>
          <div class="position-relative">
            <input type="text" id="sideSearch" class="form-control"
                   value="<?= esc($search) ?>" placeholder="Search places…" autocomplete="off">
            <div id="sideDropdown" class="search-dropdown"></div>
          </div>
        </div>

        <!-- Categories -->
        <div class="filter-block">
          <h6 class="filter-heading"><i class="bi bi-grid me-2"></i>Category</h6>
          <div class="d-flex flex-column gap-1">
            <button class="cat-btn <?= !$category ? 'active' : '' ?>" data-cat="">
              <i class="bi bi-globe me-2"></i>All Places
            </button>
            <?php foreach ($categories as $cat): ?>
            <button class="cat-btn <?= $category === $cat['slug'] ? 'active' : '' ?>"
                    data-cat="<?= esc($cat['slug']) ?>"
                    style="--cat-color:<?= esc($cat['color']) ?>">
              <i class="bi <?= esc($cat['icon']) ?> me-2"></i>
              <?= esc($cat['name']) ?>
              <span class="badge ms-auto"><?= $cat['place_count'] ?></span>
            </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Sort -->
        <div class="filter-block">
          <h6 class="filter-heading"><i class="bi bi-sort-down me-2"></i>Sort By</h6>
          <select id="sortSelect" class="form-select form-select-sm">
            <option value="newest"  <?= $sort==='newest'  ?'selected':'' ?>>Newest First</option>
            <option value="rating"  <?= $sort==='rating'  ?'selected':'' ?>>Highest Rated</option>
            <option value="name_asc"<?= $sort==='name_asc'?'selected':'' ?>>Name (A–Z)</option>
            <option value="featured"<?= $sort==='featured'?'selected':'' ?>>Featured</option>
          </select>
        </div>

      </div>
    </div>

    <!-- ══ MAIN GRID ═════════════════════════════════════════ -->
    <div class="col-lg-9">

      <div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small" id="showingText">
        Showing <?= $showing ?> of <?= $total ?> places
    </span>
    <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-secondary active" id="gridViewBtn" title="Grid view">
            <i class="bi bi-grid-3x3-gap-fill"></i>
        </button>
        <button class="btn btn-outline-secondary" id="listViewBtn" title="List view">
            <i class="bi bi-list-ul"></i>
        </button>
    </div>
</div>

      <!-- Places grid -->
      <div class="row g-4" id="placesGrid">
        <?php if (empty($places)): ?>
        <div class="col-12 text-center py-5">
          <i class="bi bi-search display-4 text-muted d-block mb-3"></i>
          <h5 class="text-muted">No places found</h5>
          <p class="text-muted small">Try a different search — or type any real place above to import it from Google.</p>
        </div>
        <?php else: ?>
          <?php foreach ($places as $place): ?>
            <?= view('places/_card', ['place' => $place]) ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Loading spinner -->
      <div class="text-center py-4 d-none" id="loadSpinner">
        <div class="spinner-border text-warning"></div>
        <p class="text-muted small mt-2">Loading places…</p>
      </div>

      <!-- Load more -->
      <?php if ($hasMore): ?>
      <div class="text-center mt-4" id="loadMoreWrap">
        <button class="btn btn-outline-dark btn-lg px-5" id="loadMoreBtn">
          <i class="bi bi-arrow-down-circle me-2"></i>Load More
        </button>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>
window.PlacesState = {
  category: '<?= esc($category) ?>',
  sort:     '<?= esc($sort) ?>',
  search:   '<?= esc($search) ?>',
  page:     1,
  total:    <?= $total ?>,
};
</script>
