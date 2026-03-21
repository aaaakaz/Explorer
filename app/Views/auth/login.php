<div class="auth-page">
  <div class="auth-card">
    <div class="auth-header">
      <i class="bi bi-door-open auth-icon"></i>
      <h2>Welcome back</h2>
      <p>Sign in to your Explorer account</p>
    </div>
    <div class="auth-body">
      <?php if (!empty($error)): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-circle-fill"></i><?= esc($error) ?>
      </div>
      <?php endif; ?>

      <form action="<?= base_url('login') ?>" method="post">
        <div class="mb-3">
          <label class="form-label fw-medium">Email address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label fw-medium">Password</label>
          <div class="input-group">
            <input type="password" name="password" id="pwField" class="form-control" placeholder="••••••••" required>
            <button type="button" class="btn btn-outline-secondary" id="togglePw">
              <i class="bi bi-eye" id="pwEye"></i>
            </button>
          </div>
        </div>
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-warning fw-semibold py-2">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
          </button>
        </div>
      </form>

      <p class="text-center text-muted small mb-0">
        Don't have an account? <a href="<?= base_url('register') ?>" class="text-warning fw-semibold">Create one free</a>
      </p>
    </div>
  </div>
</div>

<script>
document.getElementById('togglePw')?.addEventListener('click', function() {
  const f = document.getElementById('pwField');
  const i = document.getElementById('pwEye');
  f.type = f.type === 'password' ? 'text' : 'password';
  i.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});
</script>
