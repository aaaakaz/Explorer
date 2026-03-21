<div class="col-md-6 col-lg-4">
  <a href="<?= base_url('places/' . $place['id']) ?>" class="place-card-link">
    <div class="place-card h-100">

      <!-- Image area with gradient fallback -->
      <div class="place-card-img"
           data-place-id="<?= $place['id'] ?>"
           data-place-name="<?= esc($place['name']) ?>"
           data-place-city="<?= esc($place['city']) ?>">
        <?php if (!empty($place['photo_url'])): ?>
    <img src="<?= esc($place['photo_url']) ?>" alt="<?= esc($place['name']) ?>"
         style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:0;">
<?php else: ?>
    <div class="place-card-gradient"></div>
<?php endif; ?>
        <span class="place-cat-badge" style="background:<?= esc($place['category_color'] ?? '#6b7280') ?>">
          <i class="bi <?= esc($place['category_icon'] ?? 'bi-pin-map') ?> me-1"></i>
          <?= esc($place['category_name'] ?? 'Place') ?>
        </span>
        <?php
          $prices = ['','Free','£','££','£££'];
          $p = (int)($place['price_range'] ?? 2);
        ?>
        <span class="place-price-badge"><?= $prices[$p] ?? '££' ?></span>
      </div>

      <div class="place-card-body">
        <h5 class="place-card-title"><?= esc($place['name']) ?></h5>
        <p class="place-card-city">
          <i class="bi bi-geo-alt-fill text-danger me-1"></i>
          <?= esc($place['city']) ?><?= $place['country'] ? ', ' . esc($place['country']) : '' ?>
        </p>
        <p class="place-card-desc">
          <?= esc(mb_strimwidth($place['description'] ?? '', 0, 90, '…')) ?>
        </p>

        <!-- Star rating -->
        <?php
          $avg  = round((float)($place['avg_rating'] ?? 0), 1);
          $full = floor($avg);
          $half = ($avg - $full) >= 0.5;
        ?>
        <div class="place-card-rating">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <?php if ($i <= $full): ?>
              <i class="bi bi-star-fill text-warning"></i>
            <?php elseif ($half && $i === $full + 1): ?>
              <i class="bi bi-star-half text-warning"></i>
            <?php else: ?>
              <i class="bi bi-star text-warning"></i>
            <?php endif; ?>
          <?php endfor; ?>
          <span class="ms-1 small text-muted">
            <?= $avg > 0 ? $avg : 'No reviews' ?>
            <?php if ($place['review_count'] > 0): ?>(<?= $place['review_count'] ?>)<?php endif; ?>
          </span>
        </div>
      </div>
    </div>
  </a>
</div>
