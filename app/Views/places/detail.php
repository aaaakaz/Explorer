<?php
$avg    = round((float)($place['avg_rating'] ?? 0), 1);
$prices = ['','Free','£ Budget','££ Moderate','£££ Expensive'];
$session = session();
?>

<!-- Place hero -->
<div class="detail-hero" id="detailHero">
  <div class="detail-hero-gallery" id="heroGallery">
    <div class="hero-photo-loading">
      <div class="spinner-border text-warning"></div>
    </div>
  </div>
  <div class="detail-hero-overlay"></div>
  <div class="container detail-hero-content">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb breadcrumb-dark">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('places') ?>">Places</a></li>
        <li class="breadcrumb-item active"><?= esc($place['name']) ?></li>
      </ol>
    </nav>
    <span class="detail-cat-badge mb-2" style="background:<?= esc($place['category_color'] ?? '#6b7280') ?>">
      <i class="bi <?= esc($place['category_icon'] ?? 'bi-pin-map') ?> me-1"></i>
      <?= esc($place['category_name'] ?? '') ?>
    </span>
    <h1 class="detail-title"><?= esc($place['name']) ?></h1>
    <div class="detail-meta">
      <span><i class="bi bi-geo-alt-fill me-1"></i><?= esc($place['city']) ?>, <?= esc($place['country']) ?></span>
      <?php if ($avg > 0): ?>
      <span><i class="bi bi-star-fill text-warning me-1"></i><?= $avg ?> (<?= $place['review_count'] ?> reviews)</span>
      <?php endif; ?>
      <?php if ($place['price_range']): ?>
      <span><i class="bi bi-currency-pound me-1"></i><?= esc($prices[$place['price_range']] ?? '') ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="container py-5">
  <div class="row g-5">

    <!-- ══ LEFT COLUMN ═══════════════════════════════════════ -->
    <div class="col-lg-8">

      <!-- About -->
      <section class="detail-section">
        <h3 class="detail-heading">About this place</h3>
        <p class="lead text-muted"><?= nl2br(esc($place['description'])) ?></p>
        <?php if ($place['tags']): ?>
        <div class="mt-3">
          <?php foreach (explode(',', $place['tags']) as $tag): ?>
          <a href="<?= base_url('places?q=' . urlencode(trim($tag))) ?>"
             class="tag-chip"><?= esc(trim($tag)) ?></a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </section>

      <!-- Photo Gallery -->
      <section class="detail-section">
        <h3 class="detail-heading">
          <i class="bi bi-images me-2 text-warning"></i>Photos
          <small class="text-muted fs-6 ms-2 fw-normal">via Unsplash</small>
        </h3>
        <div id="photoGallery" class="photo-gallery">
          <div class="photo-gallery-loading text-muted small py-3">
            <div class="spinner-border spinner-border-sm text-warning me-2"></div>
            Loading photos…
          </div>
        </div>
      </section>

      <!-- Map -->
      <section class="detail-section">
        <h3 class="detail-heading"><i class="bi bi-map me-2 text-warning"></i>Location</h3>
        <div id="placeMap" class="place-map"
             data-lat="<?= esc($place['lat']) ?>"
             data-lng="<?= esc($place['lng']) ?>"
             data-name="<?= esc($place['name']) ?>">
        </div>
        <div class="mt-2 text-muted small">
          <i class="bi bi-geo-alt text-danger me-1"></i>
          <?= esc($place['address']) ?>
          <a href="https://maps.google.com/?q=<?= urlencode($place['name'] . ' ' . $place['address']) ?>"
             class="btn btn-sm btn-outline-secondary ms-2" target="_blank">
            <i class="bi bi-box-arrow-up-right me-1"></i>Open in Maps
          </a>
        </div>
      </section>

      <!-- Reviews -->
      <section class="detail-section" id="reviewsSection">
        <h3 class="detail-heading">
          <i class="bi bi-chat-square-quote me-2 text-warning"></i>Reviews
          <?php if ($place['review_count'] > 0): ?>
          <span class="badge bg-warning text-dark ms-2"><?= $place['review_count'] ?></span>
          <?php endif; ?>
        </h3>

        <!-- Rating breakdown -->
        <?php if ($place['review_count'] > 0): ?>
        <div class="rating-summary mb-4">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="rating-big"><?= $avg ?></div>
            <div>
              <div class="mb-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="bi bi-star<?= $i <= $avg ? '-fill' : ($i - 0.5 <= $avg ? '-half' : '') ?> text-warning"></i>
                <?php endfor; ?>
              </div>
              <small class="text-muted"><?= $place['review_count'] ?> review<?= $place['review_count'] > 1 ? 's' : '' ?></small>
            </div>
          </div>
          <?php foreach ([5,4,3,2,1] as $star): ?>
          <?php $cnt = $breakdown[$star]; $pct = $place['review_count'] > 0 ? round($cnt / $place['review_count'] * 100) : 0; ?>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="small text-nowrap" style="width:32px"><?= $star ?> <i class="bi bi-star-fill text-warning" style="font-size:.7rem"></i></span>
            <div class="progress flex-grow-1" style="height:6px">
              <div class="progress-bar bg-warning" style="width:<?= $pct ?>%"></div>
            </div>
            <span class="small text-muted" style="width:20px"><?= $cnt ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Reviews list -->
        <div id="reviewsList">
          <?php if (empty($reviews)): ?>
          <div class="text-center py-4 text-muted" id="noReviewMsg">
            <i class="bi bi-chat-square display-6 d-block mb-2"></i>
            No reviews yet — be the first!
          </div>
          <?php else: ?>
          <?php foreach ($reviews as $r): ?>
          <div class="review-card mb-3">
            <div class="d-flex justify-content-between align-items-start">
              <div class="d-flex align-items-center gap-2">
                <div class="review-avatar" style="background:<?= esc($r['avatar_color'] ?? '#f59e0b') ?>">
                  <?= strtoupper(substr($r['username'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                  <div class="fw-semibold"><?= esc($r['username'] ?? 'User') ?></div>
                  <div class="text-muted small"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                </div>
              </div>
              <div>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="bi bi-star<?= $i <= $r['rating'] ? '-fill' : '' ?> text-warning"></i>
                <?php endfor; ?>
              </div>
            </div>
            <p class="mt-2 mb-0"><?= nl2br(esc($r['comment'])) ?></p>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Review form -->
        <div class="review-form-card mt-4">
          <h5 class="fw-semibold mb-3"><i class="bi bi-pencil-square me-2"></i>Leave a Review</h5>

          <?php if (!$session->get('logged_in')): ?>
          <div class="login-prompt">
            <i class="bi bi-person-circle d-block mb-2"></i>
            <h6 class="fw-semibold mb-1">Sign in to leave a review</h6>
            <p class="small text-muted mb-3">Join Explorer free to share your experience.</p>
            <div class="d-flex gap-2 justify-content-center">
              <a href="<?= base_url('login') ?>" class="btn btn-warning btn-sm fw-semibold px-4">Sign In</a>
              <a href="<?= base_url('register') ?>" class="btn btn-outline-secondary btn-sm px-4">Register Free</a>
            </div>
          </div>
          <?php else: ?>
          <div id="reviewAlert" class="alert d-none"></div>
          <form id="reviewForm">
            <input type="hidden" name="place_id" value="<?= $place['id'] ?>">

            <div class="d-flex align-items-center gap-2 mb-3 p-3 bg-light rounded-3">
              <div class="review-avatar" style="background:<?= esc($session->get('avatar_color')) ?>">
                <?= strtoupper(substr($session->get('username'), 0, 1)) ?>
              </div>
              <div>
                <div class="fw-semibold"><?= esc($session->get('username')) ?></div>
                <div class="small text-muted">Posting as yourself</div>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium">Your Rating</label>
              <div class="star-picker" id="starPicker">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star-pick" data-val="<?= $i ?>"><i class="bi bi-star"></i></span>
                <?php endfor; ?>
              </div>
              <input type="hidden" name="rating" id="ratingVal">
            </div>

            <div class="mb-3">
              <label class="form-label fw-medium">Your Review</label>
              <textarea name="comment" class="form-control" rows="4"
                        placeholder="Tell others about your experience…"></textarea>
            </div>

            <button type="submit" class="btn btn-warning fw-semibold px-4" id="reviewSubmitBtn">
              <span class="btn-text"><i class="bi bi-send me-2"></i>Submit Review</span>
              <span class="spinner-border spinner-border-sm d-none"></span>
            </button>
          </form>
          <?php endif; ?>
        </div>

      </section>

    </div><!-- /col-lg-8 -->

    <!-- ══ RIGHT SIDEBAR ══════════════════════════════════════ -->
    <div class="col-lg-4">
      <div class="sticky-sidebar">

        <!-- Weather widget -->
        <div class="info-card mb-3">
          <div class="info-card-header"><i class="bi bi-cloud-sun me-2"></i>Live Weather</div>
          <div class="info-card-body" id="weatherWidget">
            <div class="text-center py-3">
              <div class="spinner-border spinner-border-sm text-warning"></div>
              <p class="small text-muted mt-2 mb-0">Fetching weather…</p>
            </div>
          </div>
        </div>

        <!-- Place info -->
        <div class="info-card mb-3">
          <div class="info-card-header"><i class="bi bi-info-circle me-2"></i>Place Info</div>
          <div class="info-card-body">
            <ul class="info-list">
              <?php if ($place['address']): ?>
              <li><i class="bi bi-geo-alt-fill text-danger"></i><span><?= esc($place['address']) ?></span></li>
              <?php endif; ?>
              <?php if ($place['opening_hours']): ?>
              <li><i class="bi bi-clock text-success"></i><span><?= esc($place['opening_hours']) ?></span></li>
              <?php endif; ?>
              <?php if ($place['phone']): ?>
              <li><i class="bi bi-telephone text-primary"></i><a href="tel:<?= esc($place['phone']) ?>"><?= esc($place['phone']) ?></a></li>
              <?php endif; ?>
              <?php if ($place['website']): ?>
              <li><i class="bi bi-globe text-info"></i>
                <a href="<?= esc($place['website']) ?>" target="_blank" rel="noopener">
                  Visit Website <i class="bi bi-box-arrow-up-right small ms-1"></i>
                </a>
              </li>
              <?php endif; ?>
              <?php if ($place['price_range']): ?>
              <li><i class="bi bi-currency-pound text-warning"></i><span><?= esc($prices[$place['price_range']] ?? '') ?></span></li>
              <?php endif; ?>
            </ul>
          </div>
        </div>

        <!-- Actions -->
        <div class="d-grid gap-2 mb-3">
          <a href="https://maps.google.com/?q=<?= urlencode($place['name'] . ' ' . $place['address']) ?>"
             class="btn btn-danger" target="_blank">
            <i class="bi bi-pin-map-fill me-2"></i>Get Directions
          </a>
          <button class="btn btn-outline-secondary" id="shareBtn">
            <i class="bi bi-share me-2"></i>Share this Place
          </button>
          <a href="#reviewsSection" class="btn btn-outline-warning">
            <i class="bi bi-pencil me-2"></i>Write a Review
          </a>
        </div>

      </div>
    </div>

  </div><!-- /row -->

  <!-- Related places -->
  <?php if (!empty($related)): ?>
  <hr class="my-5">
  <h3 class="detail-heading">More <?= esc($place['category_name'] ?? 'Places') ?></h3>
  <div class="row g-4">
    <?php foreach ($related as $rp): ?>
      <?= view('places/_card', ['place' => $rp]) ?>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div><!-- /container -->

<!-- Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?= esc($mapsKey) ?>&callback=initMap" async defer></script>

<script>
window.PlaceDetail = {
  id:   <?= (int)$place['id'] ?>,
  lat:  <?= (float)$place['lat'] ?>,
  lng:  <?= (float)$place['lng'] ?>,
  name: '<?= addslashes(esc($place['name'])) ?>',
  city: '<?= addslashes(esc($place['city'])) ?>',
};
</script>
