# CityExplore — 6CS028 Advanced Web Technologies Coursework
Built with **CodeIgniter 4**, MySQL, AJAX and three third-party APIs.

---

## Mark Scheme Coverage

| Criterion | Implementation | Marks |
|-----------|---------------|-------|
| **Architectural Pattern** | CodeIgniter 4 MVC — Controllers, Models (CI4 Query Builder), Views with layout template, MySQL via CI4 Database class | 20 |
| **Third-party API** | OpenWeatherMap (live weather), Unsplash (place photos), Google Maps JS API (interactive map + markers) — all called programmatically from PHP/JS | 20 |
| **Mobile** | Responsive CSS Grid, hamburger nav menu, Geolocation hardware API (`navigator.geolocation`) for "Near me" button | 20 |
| **RIA / AJAX** | Live search autocomplete, category filter (no reload), load more pagination, save/unsave places, review submission, nearby places refresh — all via `fetch()` | 20 |
| **Version Control** | Git with feature-by-feature commits, GitHub Issues with labels & milestones | 20 |

---

## Setup on mi-linux (step by step)

### 1. Upload the project
```bash
ssh your_username@mi-linux.wlv.ac.uk
cd public_html
# Upload this folder via FTP, or clone your GitHub repo:
git clone https://github.com/YOUR_USERNAME/cityexplore.git
```

### 2. Import the database
Log into **phpMyAdmin** on mi-linux, select your database, click **Import**, choose `config/schema.sql` → **Go**.
This creates all 4 tables and inserts 12 sample London places.

### 3. Rename env and fill in your details
```bash
cp env .env
nano .env
```
Edit these lines:
```
database.default.username = your_mi_linux_username
database.default.password = your_mi_linux_password
database.default.database = your_database_name

app.baseURL = 'https://mi-linux.wlv.ac.uk/~yourusername/cityexplore/public/'

WEATHER_API_KEY  = get_free_key_from_openweathermap.org
UNSPLASH_KEY     = get_free_key_from_unsplash.com/developers
GOOGLE_MAPS_KEY  = get_key_from_console.cloud.google.com
```

### 4. Set writable folder permissions
```bash
chmod -R 777 writable/
```

### 5. Visit your site
```
https://mi-linux.wlv.ac.uk/~yourusername/cityexplore/public/
```

---

## Getting Free API Keys (5 mins each)

| API | Sign up URL | Free limit |
|-----|-------------|------------|
| OpenWeatherMap | openweathermap.org/api | 1,000 calls/day |
| Unsplash | unsplash.com/developers | 50 requests/hour |
| Google Maps | console.cloud.google.com | $200 credit/month (plenty) |

> The site works without the API keys — you just won't see live weather, photos, or the map.

---

## Project Structure (CodeIgniter 4 MVC)

```
cityexplore/
├── public/                  ← Web root — point your browser here
│   ├── index.php            ← CI4 front controller (do not edit)
│   ├── .htaccess            ← URL rewriting
│   ├── css/style.css        ← All styles, fully responsive
│   └── js/main.js           ← All AJAX, Maps, Geolocation logic
│
├── app/
│   ├── Config/
│   │   ├── App.php          ← Base URL, session config
│   │   ├── Database.php     ← DB connection settings
│   │   ├── Routes.php       ← All URL routes defined here
│   │   └── ApiKeys.php      ← API key config (values from .env)
│   │
│   ├── Controllers/         ← CI4 Controllers (extend BaseController)
│   │   ├── BaseController.php
│   │   ├── Home.php         ← / , /search, /filter, /weather
│   │   ├── Places.php       ← /places, /places/:id, /save, /review, /nearby
│   │   └── Auth.php         ← /login, /register, /logout
│   │
│   ├── Models/              ← CI4 Models (extend CodeIgniter\Model)
│   │   ├── PlaceModel.php   ← getFeatured, getFiltered, search, ratings
│   │   ├── ReviewModel.php  ← getForPlace
│   │   ├── SavedPlaceModel.php ← savePlace, unsavePlace
│   │   └── UserModel.php    ← findByEmail, verifyPassword, password hashing
│   │
│   ├── Views/
│   │   ├── layouts/main.php ← Master layout (navbar + footer)
│   │   ├── home/index.php   ← Homepage: hero, search, grid, sidebar
│   │   ├── places/
│   │   │   ├── _card.php    ← Reusable place card partial
│   │   │   ├── index.php    ← All places listing
│   │   │   └── detail.php   ← Single place: photos, map, reviews
│   │   └── auth/
│   │       ├── login.php
│   │       └── register.php
│   │
│   └── Database/
│       ├── Migrations/      ← php spark migrate
│       └── Seeds/           ← php spark db:seed PlaceSeeder
│
├── config/
│   └── schema.sql           ← Import via phpMyAdmin (easiest option)
├── writable/                ← Cache, logs, sessions (chmod 777)
└── env                      ← Rename to .env and fill in your details
```

---

## AJAX Endpoints

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/search?q=&city=` | Live autocomplete as you type |
| GET | `/filter?city=&category=&offset=` | Filter places by category |
| GET | `/weather?city=` | Fetch live weather (OpenWeatherMap) |
| GET | `/places/nearby?lat=&lng=` | Nearby places via Geolocation |
| POST | `/places/save` | Save / unsave a place (session required) |
| POST | `/places/review` | Submit a star rating + comment |

---

## Suggested Git Commit History

```bash
git init && git add . && git commit -m "Initial CI4 project structure"
git commit -m "Add database schema and CI4 migrations"
git commit -m "Add PlaceModel and ReviewModel with CI4 Query Builder"
git commit -m "Add Home controller with index and AJAX search endpoint"
git commit -m "Add Places controller with detail, save, review endpoints"
git commit -m "Add Auth controller - register, login, logout with sessions"
git commit -m "Add master layout view and all page views"
git commit -m "Add OpenWeatherMap API integration for live weather"
git commit -m "Add Google Maps JS API with place markers"
git commit -m "Add Geolocation hardware API for nearby places feature"
git commit -m "Add Unsplash API for place detail photos"
git commit -m "Add AJAX category filter and load more pagination"
git commit -m "Add responsive CSS - mobile hamburger menu and grid"
git commit -m "Add save/unsave AJAX with session auth check"
git commit -m "Add review form with star rating AJAX submission"
git commit -m "Fix: weather widget fallback when API key not configured"
git commit -m "Fix: mobile layout adjustments for small screens"
```

Use **GitHub Issues** labelled `bug` / `enhancement` with weekly milestones for full marks on Version Control.
