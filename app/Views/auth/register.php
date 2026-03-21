<div class="auth-page">
  <div class="auth-card">
    <div class="auth-header">
      <i class="bi bi-person-plus auth-icon"></i>
      <h2>Create account</h2>
      <p>Join Explorer and start discovering amazing places</p>
    </div>
    <div class="auth-body">
      <?php if (!empty($error)): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-circle-fill"></i><?= esc($error) ?>
      </div>
      <?php endif; ?>

      <form action="<?= base_url('register') ?>" method="post">
        <div class="mb-3">
          <label class="form-label fw-medium">Username</label>
          <input type="text" name="username" class="form-control" placeholder="e.g. jane_explorer" required autofocus minlength="3">
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Email address</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Password</label>
          <div class="input-group">
            <input type="password" name="password" id="pwField" class="form-control" placeholder="Min. 6 characters" required minlength="6">
            <button type="button" class="btn btn-outline-secondary" id="togglePw">
              <i class="bi bi-eye" id="pwEye"></i>
            </button>
          </div>
          <!-- Strength bar -->
          <div class="mt-2" style="height:4px;background:#e5e7eb;border-radius:999px">
            <div id="strengthBar" style="height:100%;width:0;border-radius:999px;transition:width .3s,background .3s"></div>
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-medium">Confirm Password</label>
          <input type="password" name="confirm" id="confirmField" class="form-control" placeholder="Repeat password" required>
        </div>
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-warning fw-semibold py-2">
            <i class="bi bi-person-check me-2"></i>Create Account
          </button>
        </div>
      </form>

      <p class="text-center text-muted small mb-0">
        Already have an account? <a href="<?= base_url('login') ?>" class="text-warning fw-semibold">Sign in</a>
      </p>
    </div>
  </div>
</div>

<script>
// Password visibility toggle
document.getElementById('togglePw')?.addEventListener('click', function() {
  const f = document.getElementById('pwField');
  const i = document.getElementById('pwEye');
  f.type = f.type === 'password' ? 'text' : 'password';
  i.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
});

// Strength bar
document.getElementById('pwField')?.addEventListener('input', function() {
  const v = this.value;
  const score = [v.length >= 6, /\d/.test(v), /[A-Z]/.test(v), /[^a-z0-9]/i.test(v)].filter(Boolean).length;
  const bar = document.getElementById('strengthBar');
  const colours = ['','#ef4444','#f97316','#3b82f6','#10b981'];
  bar.style.width = (score * 25) + '%';
  bar.style.background = colours[score] || '';
});

// Confirm match
document.getElementById('confirmField')?.addEventListener('input', function() {
  const match = this.value === document.getElementById('pwField').value;
  this.classList.toggle('is-invalid', !match && this.value.length > 0);
});
</script>
