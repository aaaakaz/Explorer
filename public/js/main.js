/* ================================================================
   DARK / LIGHT THEME
================================================================ */
(function() {
    // Apply saved theme immediately on page load (before render)
    const saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', saved);
})();

function initTheme() {
    const toggle  = document.getElementById('themeToggle');
    const icon    = document.getElementById('themeIcon');
    if (!toggle || !icon) return;

    // Set correct icon for current theme
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    icon.className = current === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';

    toggle.addEventListener('click', () => {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const next   = isDark ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        icon.className = next === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    });
}

// Run after DOM is ready
document.addEventListener('DOMContentLoaded', initTheme);
/* ================================================================
   Explorer2 — main.js
================================================================ */

/* ── Gradient palette for card backgrounds ─────────────────── */
const GRADIENTS = [
  '#667eea,#764ba2','#f093fb,#f5576c','#4facfe,#00f2fe',
  '#43e97b,#38f9d7','#fa709a,#fee140','#a18cd1,#fbc2eb',
  '#fccb90,#d57eeb','#a1c4fd,#c2e9fb','#fd7043,#ff8a65',
  '#e0c3fc,#8ec5fc','#96fbc4,#f9f586','#fda085,#f6d365',
];

function cardGradient(name) {
  const idx = [...(name||'')].reduce((a,c) => a + c.charCodeAt(0), 0) % GRADIENTS.length;
  return GRADIENTS[idx];
}

/* ── HTML escape helper ─────────────────────────────────────── */
function esc(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(str || ''));
  return d.innerHTML;
}

/* ── Highlight matching text ───────────────────────────────── */
function highlight(text, q) {
  if (!q) return esc(text);
  const safe = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  return esc(text).replace(new RegExp(`(${safe})`, 'gi'), '<mark>$1</mark>');
}

/* ── Toast notification ─────────────────────────────────────── */
function toast(msg, type = 'dark') {
  const colours = { dark: '#111827', success: '#065f46', danger: '#7f1d1d', warning: '#92400e' };
  const el = document.createElement('div');
  el.style.cssText = `
    position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
    background:${colours[type]||colours.dark};color:#fff;
    padding:.75rem 1.25rem;border-radius:10px;font-size:.88rem;
    box-shadow:0 4px 20px rgba(0,0,0,.3);animation:fadeIn .3s ease;`;
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 3200);
}

/* ================================================================
   UNIFIED SEARCH
   Shows two groups: [Saved Places] from DB + [World Search] from Google
================================================================ */
class UnifiedSearch {
  constructor(inputEl, dropdownEl, { onSearch } = {}) {
    this.input    = inputEl;
    this.dropdown = dropdownEl;
    this.onSearch = onSearch || (() => {});
    this.timer    = null;
    this._bind();
  }

  _bind() {
    this.input.addEventListener('input', () => {
      clearTimeout(this.timer);
      const q = this.input.value.trim();
      if (q.length < 2) { this._hide(); return; }
      this.timer = setTimeout(() => this._fetch(q), 280);
    });

    document.addEventListener('click', e => {
      if (!this.input.contains(e.target) && !this.dropdown.contains(e.target)) this._hide();
    });

    this.input.addEventListener('keydown', e => {
      if (e.key === 'Escape') { this._hide(); return; }
      if (e.key === 'Enter') {
        e.preventDefault();
        const h = this.dropdown.querySelector('.search-item.highlighted');
        if (h) { h.click(); return; }
        this.onSearch(this.input.value.trim());
        this._hide();
      }
      if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
        this._navigate(e.key === 'ArrowDown' ? 1 : -1);
        e.preventDefault();
      }
    });
  }

  async _fetch(q) {
    const [localRes, googleRes] = await Promise.allSettled([
      fetch(`${BASE_URL}search?q=${encodeURIComponent(q)}`).then(r => r.json()),
      fetch(`${BASE_URL}google-autocomplete?q=${encodeURIComponent(q)}`).then(r => r.json()),
    ]);

    const local  = localRes.status  === 'fulfilled' ? (localRes.value.local   || []) : [];
    const google = googleRes.status === 'fulfilled' ? (googleRes.value.google || []) : [];
    this._render(q, local, google);
  }

  _render(q, local, google) {
    if (!local.length && !google.length) { this._hide(); return; }

    let html = '';

    if (local.length) {
      html += `<div class="search-group-label"><i class="bi bi-clock-history me-1"></i>Saved Places</div>`;
      html += local.map(s => `
        <div class="search-item" data-type="local" data-id="${s.id}">
          <div class="search-icon" style="background:${esc(s.category_color||'#6b7280')}">
            <i class="bi ${esc(s.category_icon||'bi-pin-map')}"></i>
          </div>
          <div class="flex-grow-1 overflow-hidden">
            <div class="search-name">${highlight(s.name, q)}</div>
            <div class="search-sub">${esc(s.city)}${s.country?', '+esc(s.country):''} &bull; ${esc(s.category_name)}</div>
          </div>
          <span class="search-badge">Saved</span>
        </div>`).join('');
    }

    if (google.length) {
      html += `<div class="search-group-label"><i class="bi bi-globe me-1"></i>Search the World<span class="google-badge">via Google</span></div>`;
      html += google.map(p => `
        <div class="search-item" data-type="google" data-place-id="${esc(p.place_id)}">
          <div class="search-icon" style="background:#4285F4">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <div class="flex-grow-1 overflow-hidden">
            <div class="search-name">${highlight(p.main_text, q)}</div>
            <div class="search-sub">${esc(p.secondary)}</div>
          </div>
          <span class="search-badge search-badge-google"><i class="bi bi-arrow-down-circle me-1"></i>Import</span>
        </div>`).join('');
    }

    this.dropdown.innerHTML = html;
    this.dropdown.classList.add('open');
    this._bindItems(q);
  }

  _bindItems(q) {
    this.dropdown.querySelectorAll('.search-item').forEach(item => {
      item.addEventListener('click', async () => {
        if (item.dataset.type === 'local') {
          window.location.href = `${BASE_URL}places/${item.dataset.id}`;
          return;
        }
        // Google import
        item.innerHTML = `<div class="d-flex align-items-center gap-2 py-1 px-2 text-muted w-100">
          <div class="spinner-border spinner-border-sm text-warning"></div>
          <span>Importing place…</span></div>`;

        try {
          const fd = new FormData();
          fd.append('place_id', item.dataset.placeId);
          const res  = await fetch(`${BASE_URL}google-import`, { method: 'POST', body: fd });
          const data = await res.json();
          if (data.id) {
            window.location.href = `${BASE_URL}places/${data.id}`;
          } else {
            toast('Could not import place. Please try again.', 'danger');
            this._hide();
          }
        } catch {
          toast('Network error. Please try again.', 'danger');
          this._hide();
        }
      });
    });
  }

  _navigate(dir) {
    const items = [...this.dropdown.querySelectorAll('.search-item')];
    if (!items.length) return;
    const curr = this.dropdown.querySelector('.search-item.highlighted');
    let idx = items.indexOf(curr) + dir;
    idx = Math.max(0, Math.min(idx, items.length - 1));
    items.forEach(i => i.classList.remove('highlighted'));
    items[idx].classList.add('highlighted');
  }

  _hide() {
    this.dropdown.classList.remove('open');
    this.dropdown.innerHTML = '';
  }
}

/* ── Wire up search bars ────────────────────────────────────── */
let sideTimer;

const navInput  = document.getElementById('navSearch');
const navDrop   = document.getElementById('navDropdown');
const heroInput = document.getElementById('heroSearch');
const heroDrop  = document.getElementById('heroDropdown');
const sideInput = document.getElementById('sideSearch');
const sideDrop  = document.getElementById('sideDropdown');

if (navInput && navDrop) {
    new UnifiedSearch(navInput, navDrop, {
        onSearch: q => { if (q) window.location.href = `${BASE_URL}places?q=${encodeURIComponent(q)}`; }
    });
    document.getElementById('navSearchBtn')?.addEventListener('click', () => {
        const q = navInput.value.trim();
        if (q) window.location.href = `${BASE_URL}places?q=${encodeURIComponent(q)}`;
    });
}

if (heroInput && heroDrop) {
    new UnifiedSearch(heroInput, heroDrop, {
        onSearch: q => { if (q) window.location.href = `${BASE_URL}places?q=${encodeURIComponent(q)}`; }
    });
    document.getElementById('heroSearchBtn')?.addEventListener('click', () => {
        const q = heroInput.value.trim();
        if (q) window.location.href = `${BASE_URL}places?q=${encodeURIComponent(q)}`;
    });
}

if (sideInput && sideDrop) {
    new UnifiedSearch(sideInput, sideDrop, {
        onSearch: q => { PlacesGrid.search(q); }
    });

    sideInput.addEventListener('input', () => {
        clearTimeout(sideTimer);
        sideTimer = setTimeout(() => PlacesGrid.search(sideInput.value.trim()), 500);
    });
}

/* ================================================================
   PLACES GRID (filter / sort / load more)
================================================================ */
const PlacesGrid = {
  grid:         document.getElementById('placesGrid'),
  spinner:      document.getElementById('loadSpinner'),
  loadMoreWrap: document.getElementById('loadMoreWrap'),
  showingText:  document.getElementById('showingText'),
  resultsLabel: document.getElementById('resultsLabel'),

  search(q) {
    if (window.PlacesState) {
      window.PlacesState.search = q;
      window.PlacesState.page   = 1;
    }
    this.load(true);
  },

  async load(reset = false) {
    if (!this.grid || !window.PlacesState) return;
    const s = window.PlacesState;
    if (reset) s.page = 1;

    const params = new URLSearchParams({
      category: s.category,
      sort:     s.sort,
      q:        s.search,
      page:     s.page,
    });

    this.spinner?.classList.remove('d-none');
    this.loadMoreWrap?.classList.add('d-none');
    if (reset) this.grid.innerHTML = '';

    try {
      const res  = await fetch(`${BASE_URL}filter?${params}`);
      const data = await res.json();

      if (!data.html.trim() && reset) {
        this.grid.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-search display-4 text-muted d-block mb-3"></i>
            <h5 class="text-muted">No places found</h5>
            <p class="text-muted small">Try a different search — or type any real place in the search bar above to import it from Google.</p>
          </div>`;
      } else {
        this.grid.insertAdjacentHTML('beforeend', data.html);
        paintCardGradients();
      }

      if (this.showingText) this.showingText.textContent = `Showing ${data.showing} of ${data.total} places`;
      if (this.resultsLabel) this.resultsLabel.textContent = `${data.total} places found`;

      if (data.hasMore) {
        s.page++;
        this.loadMoreWrap?.classList.remove('d-none');
      }
    } catch(e) {
      console.error('Grid load error', e);
    } finally {
      this.spinner?.classList.add('d-none');
    }
  }
};

// Category buttons
document.querySelectorAll('.cat-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (window.PlacesState) { window.PlacesState.category = btn.dataset.cat; window.PlacesState.page = 1; }
    PlacesGrid.load(true);
  });
});

// Sort
document.getElementById('sortSelect')?.addEventListener('change', function() {
  if (window.PlacesState) { window.PlacesState.sort = this.value; window.PlacesState.page = 1; }
  PlacesGrid.load(true);
});

// Load more
document.getElementById('loadMoreBtn')?.addEventListener('click', () => PlacesGrid.load(false));

// ── View toggle ───────────────────────────────────────────────
const gridBtn = document.getElementById('gridViewBtn');
const listBtn = document.getElementById('listViewBtn');
const grid    = document.getElementById('placesGrid');

if (localStorage.getItem('viewMode') === 'list' && grid) {
    grid.classList.add('list-view');
    gridBtn?.classList.remove('active');
    listBtn?.classList.add('active');
}

gridBtn?.addEventListener('click', function() {
    grid?.classList.remove('list-view');
    this.classList.add('active');
    listBtn?.classList.remove('active');
    localStorage.setItem('viewMode', 'grid');
    paintCardGradients();
});

listBtn?.addEventListener('click', function() {
    grid?.classList.add('list-view');
    this.classList.add('active');
    gridBtn?.classList.remove('active');
    localStorage.setItem('viewMode', 'list');
    paintCardGradients();
});

/* ================================================================
   CARD GRADIENTS (applied to all .place-card-img elements)
================================================================ */
function paintCardGradients() {
  document.querySelectorAll('.place-card-img:not([data-painted])').forEach(el => {
    el.setAttribute('data-painted', '1');
    const name = el.dataset.placeName || '';
    const grad = cardGradient(name);
    const div  = el.querySelector('.place-card-gradient');
    if (div) div.style.background = `linear-gradient(135deg, ${grad})`;
  });
}
paintCardGradients();

/* ================================================================
   HOMEPAGE WEATHER BAR
================================================================ */
document.getElementById('weatherBtn')?.addEventListener('click', fetchWeatherCity);
document.getElementById('weatherCity')?.addEventListener('keydown', e => {
  if (e.key === 'Enter') fetchWeatherCity();
});

async function fetchWeatherCity() {
  const city = document.getElementById('weatherCity')?.value?.trim() || 'London';
  const result = document.getElementById('weatherResult');
  if (!result) return;
  result.innerHTML = '<span class="text-white-50 small"><span class="spinner-border spinner-border-sm me-1"></span>Fetching…</span>';

  try {
    const res  = await fetch(`${BASE_URL}weather?city=${encodeURIComponent(city)}`);
    const data = await res.json();
    if (data.error) { result.innerHTML = `<span class="text-warning small">${esc(data.error)}</span>`; return; }

    const iconMap = { '01':'bi-sun-fill','02':'bi-cloud-sun-fill','03':'bi-cloud-fill',
      '04':'bi-clouds-fill','09':'bi-cloud-rain-fill','10':'bi-cloud-drizzle-fill',
      '11':'bi-lightning-fill','13':'bi-snow','50':'bi-wind' };
    const icon = iconMap[data.icon?.substring(0,2)] || 'bi-thermometer';

    result.innerHTML = `
      <span class="text-white fw-semibold">
        <i class="bi ${icon} text-warning me-1"></i>
        ${data.temp}°C &mdash; ${esc(data.description)}
        <span class="text-white-50 ms-2 small">
          <i class="bi bi-droplet me-1"></i>${data.humidity}%
          <i class="bi bi-wind ms-2 me-1"></i>${data.wind_speed} km/h
        </span>
      </span>`;
  } catch {
    result.innerHTML = '<span class="text-warning small">Weather unavailable</span>';
  }
}

// Auto-fetch on homepage load
if (document.getElementById('weatherBtn')) fetchWeatherCity();

/* ================================================================
   PLACE DETAIL PAGE
================================================================ */
if (window.PlaceDetail) {
  const pd = window.PlaceDetail;

  /* ── Unsplash Photos ─────────────────────────────────────── */
  (async () => {
    try {
      const res    = await fetch(`${BASE_URL}places/photos/${pd.id}`);
      const data   = await res.json();
      const photos = data.photos || [];

      // Hero gallery
      const heroGallery = document.getElementById('heroGallery');
      if (heroGallery) {
        if (photos.length) {
          heroGallery.innerHTML = photos.slice(0, 3).map(p =>
            `<img class="hero-photo" src="${esc(p.regular)}" alt="${esc(p.alt)}"
                  data-full="${esc(p.regular)}" data-credit="${esc(p.credit)}" data-link="${esc(p.link)}">`
          ).join('');
          heroGallery.querySelectorAll('.hero-photo').forEach(img =>
            img.addEventListener('click', () => openLightbox(img.src, img.dataset.credit, img.dataset.link))
          );
        } else {
          // Gradient fallback
          heroGallery.innerHTML = '';
          heroGallery.style.background = `linear-gradient(135deg, ${cardGradient(pd.name)})`;
        }
      }

      // Inline gallery
      const gallery = document.getElementById('photoGallery');
      if (gallery) {
        if (!photos.length) {
          gallery.innerHTML = '<p class="text-muted small">No photos found for this place.</p>';
          return;
        }
        gallery.innerHTML = photos.map(p =>
          `<img class="gallery-photo" src="${esc(p.small)}" alt="${esc(p.alt)}"
                data-full="${esc(p.regular)}" data-credit="${esc(p.credit)}" data-link="${esc(p.link)}">`
        ).join('');
        gallery.querySelectorAll('.gallery-photo').forEach(img =>
          img.addEventListener('click', () => openLightbox(img.dataset.full, img.dataset.credit, img.dataset.link))
        );
      }
    } catch(e) {
      const g = document.getElementById('photoGallery');
      if (g) g.innerHTML = '<p class="text-muted small">Photos unavailable.</p>';
    }
  })();

  /* ── Live Weather ────────────────────────────────────────── */
  (async () => {
    const widget = document.getElementById('weatherWidget');
    if (!widget) return;
    try {
      const res  = await fetch(`${BASE_URL}places/weather/${pd.id}`);
      const data = await res.json();

      if (data.error) { widget.innerHTML = `<p class="text-muted small text-center py-2">${esc(data.error)}</p>`; return; }

      const iconMap = { '01':'bi-sun-fill','02':'bi-cloud-sun-fill','03':'bi-cloud-fill',
        '04':'bi-clouds-fill','09':'bi-cloud-rain-fill','10':'bi-cloud-drizzle-fill',
        '11':'bi-lightning-fill','13':'bi-snow','50':'bi-wind' };
      const icon   = iconMap[data.icon?.substring(0,2)] || 'bi-thermometer';
      const colour = data.temp >= 20 ? '#f97316' : data.temp >= 10 ? '#3b82f6' : '#818cf8';

      widget.innerHTML = `
        <div class="weather-display">
          <div class="weather-icon" style="color:${colour}"><i class="bi ${icon}"></i></div>
          <div>
            <div class="weather-temp" style="color:${colour}">${data.temp}°C</div>
            <div class="weather-desc">${esc(data.description)}</div>
          </div>
        </div>
        <div class="weather-meta mt-2 pt-2 border-top">
          <span><i class="bi bi-thermometer-half me-1"></i>Feels ${data.feels_like}°C</span>
          <span><i class="bi bi-droplet me-1"></i>${data.humidity}%</span>
          <span><i class="bi bi-wind me-1"></i>${data.wind_speed} km/h</span>
        </div>
        <small class="text-muted d-block mt-2"><i class="bi bi-geo-alt me-1"></i>${esc(data.city_name)} &bull; Live</small>`;
    } catch {
      const w = document.getElementById('weatherWidget');
      if (w) w.innerHTML = '<p class="text-muted small text-center py-2">Weather unavailable.</p>';
    }
  })();

  /* ── Google Maps ─────────────────────────────────────────── */
  window.initMap = function() {
    const mapEl = document.getElementById('placeMap');
    if (!mapEl || !window.google) return;

    const pos = { lat: pd.lat, lng: pd.lng };
    const map = new google.maps.Map(mapEl, {
      center: pos, zoom: 15,
      mapTypeControl: false, streetViewControl: true,
    });

    const marker = new google.maps.Marker({
      position: pos, map, title: pd.name,
      animation: google.maps.Animation.DROP,
      icon: {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 11, fillColor: '#f59e0b', fillOpacity: 1,
        strokeColor: '#fff', strokeWeight: 2.5,
      },
    });

    const info = new google.maps.InfoWindow({
      content: `<div style="font-family:Inter,sans-serif;padding:4px 2px">
        <strong style="font-size:14px">${esc(pd.name)}</strong><br>
        <span style="color:#6b7280;font-size:12px">${esc(pd.city)}</span></div>`,
    });

    marker.addListener('click', () => info.open(map, marker));
    info.open(map, marker);
  };
}

/* ── Share button ───────────────────────────────────────────── */
document.getElementById('shareBtn')?.addEventListener('click', async () => {
  if (navigator.share) {
    try { await navigator.share({ title: document.title, url: location.href }); } catch {}
  } else {
    await navigator.clipboard.writeText(location.href);
    toast('Link copied to clipboard!', 'success');
  }
});

/* ================================================================
   STAR PICKER + REVIEW FORM
================================================================ */
const starPicker  = document.getElementById('starPicker');
const ratingInput = document.getElementById('ratingVal');
const reviewForm  = document.getElementById('reviewForm');

if (starPicker && ratingInput) {
  const stars = [...starPicker.querySelectorAll('.star-pick')];

  const paint = (n) => stars.forEach((s, i) => {
    const on = i < n;
    s.querySelector('i').className = on ? 'bi bi-star-fill' : 'bi bi-star';
    s.classList.toggle('active', on);
  });

  stars.forEach((star, idx) => {
    star.addEventListener('mouseenter', () => paint(idx + 1));
    star.addEventListener('mouseleave', () => paint(parseInt(ratingInput.value) || 0));
    star.addEventListener('click', () => { ratingInput.value = idx + 1; paint(idx + 1); });
  });
}

reviewForm?.addEventListener('submit', async e => {
  e.preventDefault();
  const alertEl = document.getElementById('reviewAlert');
  const btn     = document.getElementById('reviewSubmitBtn');
  const btnText = btn.querySelector('.btn-text');
  const spinner = btn.querySelector('.spinner-border');

  if (!ratingInput?.value) {
    if (alertEl) { alertEl.className = 'alert alert-danger'; alertEl.innerHTML = 'Please select a rating.'; alertEl.classList.remove('d-none'); }
    return;
  }

  btn.disabled = true;
  btnText?.classList.add('d-none');
  spinner?.classList.remove('d-none');

  try {
    const res  = await fetch(`${BASE_URL}places/review`, { method: 'POST', body: new FormData(reviewForm) });
    const data = await res.json();

    if (res.status === 401) {
      if (alertEl) { alertEl.className = 'alert alert-warning'; alertEl.innerHTML = data.error + ' <a href="' + BASE_URL + 'login">Sign in</a>'; alertEl.classList.remove('d-none'); }
      return;
    }

    if (data.success) {
      if (alertEl) { alertEl.className = 'alert alert-success'; alertEl.innerHTML = '<i class="bi bi-check-circle me-2"></i>Review submitted — thank you!'; alertEl.classList.remove('d-none'); }
      reviewForm.reset();
      if (ratingInput) ratingInput.value = '';
      starPicker?.querySelectorAll('.star-pick').forEach(s => { s.classList.remove('active'); s.querySelector('i').className = 'bi bi-star'; });

      // Inject review card
      const r    = data.review;
      const list = document.getElementById('reviewsList');
      document.getElementById('noReviewMsg')?.remove();

      const stars = Array.from({length:5}, (_,i) => `<i class="bi bi-star${i<r.rating?'-fill':''} text-warning"></i>`).join('');
      const card  = document.createElement('div');
      card.className = 'review-card mb-3';
      card.style.animation = 'fadeIn .4s ease';
      card.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex align-items-center gap-2">
            <div class="review-avatar" style="background:${esc(r.avatar_color||'#f59e0b')}">
              ${esc((r.username||'U').charAt(0).toUpperCase())}
            </div>
            <div>
              <div class="fw-semibold">${esc(r.username||'You')}</div>
              <div class="text-muted small">Just now</div>
            </div>
          </div>
          <div>${stars}</div>
        </div>
        <p class="mt-2 mb-0">${esc(r.comment||'')}</p>`;
      list?.insertAdjacentElement('afterbegin', card);
    } else {
      if (alertEl) { alertEl.className = 'alert alert-danger'; alertEl.innerHTML = data.error || 'Submission failed.'; alertEl.classList.remove('d-none'); }
    }
  } catch {
    if (alertEl) { alertEl.className = 'alert alert-danger'; alertEl.innerHTML = 'Network error. Please try again.'; alertEl.classList.remove('d-none'); }
  } finally {
    btn.disabled = false;
    btnText?.classList.remove('d-none');
    spinner?.classList.add('d-none');
  }
});

/* ================================================================
   LIGHTBOX
================================================================ */
let lightboxEl = null;

function openLightbox(src, credit, link) {
  if (!lightboxEl) {
    lightboxEl = document.createElement('div');
    lightboxEl.className = 'lightbox';
    lightboxEl.innerHTML = `
      <span class="lightbox-close"><i class="bi bi-x-lg"></i></span>
      <img id="lbImg" src="" alt="">
      <div class="lightbox-credit" id="lbCredit"></div>`;
    document.body.appendChild(lightboxEl);
    lightboxEl.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
    lightboxEl.addEventListener('click', e => { if (e.target === lightboxEl) closeLightbox(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
  }
  lightboxEl.querySelector('#lbImg').src = src;
  lightboxEl.querySelector('#lbCredit').innerHTML = credit
    ? `Photo by <a href="${esc(link)}" target="_blank">${esc(credit)}</a> on Unsplash` : '';
  lightboxEl.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  lightboxEl?.classList.remove('open');
  document.body.style.overflow = '';
}

/* ── Inject keyframes ───────────────────────────────────────── */
const styleEl = document.createElement('style');
styleEl.textContent = '@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}';
document.head.appendChild(styleEl);
