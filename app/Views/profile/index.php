<?php $session = session(); ?>

<!-- ── Profile Hero ────────────────────────────────────────── -->
<div class="profile-hero">
    <div class="container">
        <div class="profile-hero-inner">

            <!-- Avatar -->
            <div class="profile-avatar-wrap">
                <div class="profile-avatar" id="profileAvatar"
                     style="background:<?= esc($user['avatar_color']) ?>">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
                <!-- Colour picker trigger -->
                <button class="avatar-edit-btn" data-bs-toggle="modal" data-bs-target="#avatarModal"
                        title="Change colour">
                    <i class="bi bi-pencil-fill"></i>
                </button>
            </div>

            <!-- User info -->
            <div class="profile-info">
                <h1 class="profile-username"><?= esc($user['username']) ?></h1>
                <p class="profile-email text-muted">
                    <i class="bi bi-envelope me-1"></i><?= esc($user['email']) ?>
                </p>
                <p class="profile-joined text-muted small">
                    <i class="bi bi-calendar3 me-1"></i>
                    Joined <?= date('F Y', strtotime($user['created_at'])) ?>
                </p>
            </div>

            <!-- Stats -->
            <div class="profile-stats">
                <div class="profile-stat">
                    <div class="profile-stat-num"><?= $reviewCount ?></div>
                    <div class="profile-stat-label">Reviews</div>
                </div>
                <div class="profile-stat">
                    <div class="profile-stat-num"><?= $visitedCount ?></div>
                    <div class="profile-stat-label">Places Visited</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Flash messages -->
<?php if (session()->getFlashdata('error')): ?>
<div class="container mt-3">
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="container py-4">
    <div class="row g-4">

        <!-- ══ LEFT: Edit Profile ══════════════════════════════ -->
        <div class="col-lg-4">
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="bi bi-person-gear me-2"></i>Edit Profile
                </div>
                <div class="profile-card-body">
                    <form action="<?= base_url('profile/update') ?>" method="post">

                        <div class="mb-3">
                            <label class="form-label fw-medium">Username</label>
                            <input type="text" name="username" class="form-control"
                                   value="<?= esc($user['username']) ?>" required minlength="3">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= esc($user['email']) ?>" required>
                        </div>

                        <hr>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-lock me-1"></i>
                            Leave password fields blank to keep current password
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Current Password</label>
                            <input type="password" name="current_password" class="form-control"
                                   placeholder="Required to change password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">New Password</label>
                            <input type="password" name="new_password" class="form-control"
                                   placeholder="Min. 6 characters" minlength="6">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control"
                                   placeholder="Repeat new password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-warning fw-semibold">
                                <i class="bi bi-check-circle me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ══ RIGHT: Reviews + Recently Viewed ════════════════ -->
        <div class="col-lg-8">

            <!-- My Reviews -->
            <div class="profile-card mb-4">
                <div class="profile-card-header">
                    <i class="bi bi-chat-square-quote me-2"></i>My Reviews
                    <span class="badge bg-warning text-dark ms-2"><?= $reviewCount ?></span>
                </div>
                <div class="profile-card-body p-0">
                    <?php if (empty($reviews)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-chat-square display-5 d-block mb-3"></i>
                        <p>You haven't written any reviews yet.</p>
                        <a href="<?= base_url('places') ?>" class="btn btn-warning btn-sm">
                            Explore Places
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                        <div class="profile-review-item">
                            <div class="d-flex gap-3 align-items-start">
                                <!-- Place thumbnail -->
                                <a href="<?= base_url('places/' . $review['place_id']) ?>"
                                   class="profile-review-thumb flex-shrink-0">
                                    <?php if ($review['photo_url']): ?>
                                    <img src="<?= esc($review['photo_url']) ?>"
                                         alt="<?= esc($review['place_name']) ?>">
                                    <?php else: ?>
                                    <div class="profile-review-thumb-gradient"
                                         style="background:<?= esc($review['category_color'] ?? '#6b7280') ?>">
                                        <i class="bi <?= esc($review['category_icon'] ?? 'bi-pin-map') ?>"></i>
                                    </div>
                                    <?php endif; ?>
                                </a>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                        <div>
                                            <a href="<?= base_url('places/' . $review['place_id']) ?>"
                                               class="fw-semibold profile-place-link">
                                                <?= esc($review['place_name']) ?>
                                            </a>
                                            <span class="ms-2 badge"
                                                  style="background:<?= esc($review['category_color'] ?? '#6b7280') ?>;color:#fff;font-size:.7rem">
                                                <?= esc($review['category_name']) ?>
                                            </span>
                                        </div>
                                        <div class="star-row">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?> text-warning"
                                               style="font-size:.8rem"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="profile-review-body mt-1 mb-1">
                                        <?= esc($review['comment']) ?>
                                    </p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= date('d M Y', strtotime($review['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recently Viewed -->
            <div class="profile-card">
                <div class="profile-card-header">
                    <i class="bi bi-clock-history me-2"></i>Recently Viewed
                    <span class="badge bg-warning text-dark ms-2"><?= $visitedCount ?></span>
                </div>
                <div class="profile-card-body">
                    <?php if (empty($recentlyViewed)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-compass display-5 d-block mb-3"></i>
                        <p>No places visited yet.</p>
                        <a href="<?= base_url('places') ?>" class="btn btn-warning btn-sm">
                            Start Exploring
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($recentlyViewed as $place): ?>
                        <div class="col-sm-6 col-md-4">
                            <a href="<?= base_url('places/' . $place['id']) ?>"
                               class="recently-viewed-card">
                                <div class="rv-img">
                                    <?php if ($place['photo_url']): ?>
                                    <img src="<?= esc($place['photo_url']) ?>"
                                         alt="<?= esc($place['name']) ?>">
                                    <?php else: ?>
                                    <div class="rv-gradient"
                                         style="background:<?= esc($place['category_color'] ?? '#6b7280') ?>">
                                    </div>
                                    <?php endif; ?>
                                    <span class="rv-badge"
                                          style="background:<?= esc($place['category_color'] ?? '#6b7280') ?>">
                                        <i class="bi <?= esc($place['category_icon'] ?? 'bi-pin-map') ?>"></i>
                                    </span>
                                </div>
                                <div class="rv-body">
                                    <div class="rv-name"><?= esc($place['name']) ?></div>
                                    <div class="rv-city text-muted small">
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        <?= esc($place['city']) ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ── Avatar colour modal ─────────────────────────────────── -->
<div class="modal fade" id="avatarModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold">Choose Avatar Colour</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?= base_url('profile/avatar') ?>" method="post" id="avatarForm">
                    <input type="hidden" name="color" id="selectedColor"
                           value="<?= esc($user['avatar_color']) ?>">
                    <div class="avatar-colour-grid">
                        <?php
                        $colours = [
                            '#f59e0b','#3b82f6','#10b981','#ef4444',
                            '#8b5cf6','#f97316','#06b6d4','#ec4899',
                            '#14b8a6','#a855f7',
                        ];
                        foreach ($colours as $c):
                        ?>
                        <button type="button"
                                class="colour-swatch <?= $user['avatar_color'] === $c ? 'selected' : '' ?>"
                                style="background:<?= $c ?>"
                                data-color="<?= $c ?>"
                                title="<?= $c ?>">
                            <?php if ($user['avatar_color'] === $c): ?>
                            <i class="bi bi-check-lg text-white"></i>
                            <?php endif; ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <!-- Preview -->
                    <div class="text-center mt-3 mb-2">
                        <div class="profile-avatar mx-auto" id="avatarPreview"
                             style="background:<?= esc($user['avatar_color']) ?>;width:60px;height:60px;font-size:1.5rem">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                        <small class="text-muted d-block mt-1">Preview</small>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-warning fw-semibold">
                            Save Colour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Colour swatch picker
document.querySelectorAll('.colour-swatch').forEach(btn => {
    btn.addEventListener('click', function() {
        const color = this.dataset.color;
        document.getElementById('selectedColor').value = color;
        document.getElementById('avatarPreview').style.background = color;

        document.querySelectorAll('.colour-swatch').forEach(b => {
            b.innerHTML = '';
            b.classList.remove('selected');
        });
        this.innerHTML = '<i class="bi bi-check-lg text-white"></i>';
        this.classList.add('selected');
    });
});
</script>
