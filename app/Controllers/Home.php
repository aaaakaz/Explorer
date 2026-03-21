<?php
namespace App\Controllers;

use App\Models\PlaceModel;
use App\Models\CategoryModel;
use Config\ApiKeys;

class Home extends BaseController {

    protected PlaceModel    $places;
    protected CategoryModel $categories;
    protected ApiKeys       $keys;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        $this->places     = new PlaceModel();
        $this->categories = new CategoryModel();
        $this->keys       = config('ApiKeys');
    }

    // GET / — Homepage
    public function index(): string {
        $db       = \Config\Database::connect();
        $featured = $this->places->getFeatured(8);
        $categories = $this->categories->allWithCount();
        $stats = [
            'places'    => $db->table('places')->countAll(),
            'cities'    => $db->table('places')->select('city')->distinct()->countAllResults(),
            'countries' => $db->table('places')->select('country')->distinct()->countAllResults(),
        ];

        return view('layouts/main', [
            'title'   => 'Explorer — Discover Amazing Places',
            'content' => view('home/index', [
                'featured'   => $featured,
                'categories' => $categories,
                'stats'      => $stats,
                'mapsKey'    => $this->keys->googleMapsKey,
            ])
        ]);
    }

    // GET /search?q= — AJAX local DB suggestions
    public function search(): \CodeIgniter\HTTP\ResponseInterface {
        $q = trim($this->request->getGet('q') ?? '');
        if (strlen($q) < 2) return $this->response->setJSON(['local' => []]);
        return $this->response->setJSON(['local' => $this->places->suggest($q)]);
    }

    // GET /google-autocomplete?q= — AJAX Google Places autocomplete (worldwide)
    public function googleAutocomplete(): \CodeIgniter\HTTP\ResponseInterface {
        $q   = trim($this->request->getGet('q') ?? '');
        $key = $this->keys->googleMapsKey;

        if (strlen($q) < 2 || !$key || $key === 'YOUR_GOOGLE_MAPS_KEY') {
            return $this->response->setJSON(['google' => []]);
        }

        $url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?'
             . http_build_query(['input' => $q, 'key' => $key, 'language' => 'en']);

        $raw = $this->curlGet($url);
        if (!$raw) return $this->response->setJSON(['google' => []]);

        $data   = json_decode($raw, true);
        $google = array_map(fn($p) => [
            'place_id'  => $p['place_id'],
            'main_text' => $p['structured_formatting']['main_text'] ?? $p['description'],
            'secondary' => $p['structured_formatting']['secondary_text'] ?? '',
        ], $data['predictions'] ?? []);

        return $this->response->setJSON(['google' => $google]);
    }

    // POST /google-import — import a Google Place into DB, return local id
    public function googleImport(): \CodeIgniter\HTTP\ResponseInterface {
        $db  = \Config\Database::connect();
        $gid = trim($this->request->getPost('place_id') ?? '');
        $key = $this->keys->googleMapsKey;

        if (!$gid) return $this->response->setStatusCode(400)->setJSON(['error' => 'No place_id']);

        // Already saved?
        $existing = $this->places->findByGoogleId($gid);
        if ($existing) return $this->response->setJSON(['id' => $existing['id'], 'cached' => true]);

        // Fetch from Google Places Details API
        $url = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query([
            'place_id' => $gid,
            'fields'   => 'place_id,name,formatted_address,geometry,types,formatted_phone_number,website,opening_hours,price_level,editorial_summary,address_components',
            'key'      => $key,
            'language' => 'en',
        ]);

        $raw = $this->curlGet($url);
        if (!$raw) return $this->response->setStatusCode(502)->setJSON(['error' => 'Google API failed']);

        $data   = json_decode($raw, true);
        $result = $data['result'] ?? null;
        if (!$result) return $this->response->setStatusCode(404)->setJSON(['error' => 'Place not found']);

        // Extract city + country
        $city = ''; $country = '';
        foreach ($result['address_components'] ?? [] as $comp) {
            if (in_array('locality', $comp['types']))              $city    = $comp['long_name'];
            if (in_array('postal_town', $comp['types']) && !$city) $city    = $comp['long_name'];
            if (in_array('country', $comp['types']))               $country = $comp['long_name'];
        }

        // Map Google types to our category
        $categoryId = $this->mapTypesToCategory($result['types'] ?? []);

        // Opening hours string
        $hours = '';
        if (!empty($result['opening_hours']['weekday_text'])) {
            $hours = implode(' | ', array_slice($result['opening_hours']['weekday_text'], 0, 2));
        }

        // Price range (Google 0-4, we use 1-4)
        $price = isset($result['price_level']) ? min(4, max(1, (int)$result['price_level'] + 1)) : 2;

        // Tags from Google types
        $tags = implode(',', array_slice(
            array_map(fn($t) => str_replace('_', ' ', $t), $result['types'] ?? []), 0, 8
        ));

        $name = $result['name'];
        $this->places->skipValidation(true)->insert([
            'google_place_id' => $gid,
            'category_id'     => $categoryId,
            'name'            => $name,
            'slug'            => $this->places->makeSlug($name, $city),
            'description'     => $result['editorial_summary']['overview'] ?? "A place in {$city}.",
            'address'         => $result['formatted_address'] ?? '',
            'city'            => $city,
            'country'         => $country,
            'lat'             => $result['geometry']['location']['lat'],
            'lng'             => $result['geometry']['location']['lng'],
            'phone'           => $result['formatted_phone_number'] ?? '',
            'website'         => $result['website'] ?? '',
            'opening_hours'   => $hours,
            'price_range'     => $price,
            'tags'            => $tags,
            'featured'        => 0,
        ]);

        return $this->response->setJSON(['id' => (int)$db->insertID(), 'cached' => false]);
    }

    // GET /filter — AJAX load places (filtering, sorting, load more)
    public function filter(): \CodeIgniter\HTTP\ResponseInterface {
        $category = $this->request->getGet('category') ?? '';
        $sort     = $this->request->getGet('sort')     ?? 'newest';
        $search   = $this->request->getGet('q')        ?? '';
        $page     = max(1, (int)($this->request->getGet('page') ?? 1));
        $perPage  = 9;
        $offset   = ($page - 1) * $perPage;

        $places  = $this->places->getFiltered($category, $sort, $search, $perPage, $offset);
        $total   = $this->places->countFiltered($category, $search);
        $showing = $offset + count($places);

        $html = '';
        foreach ($places as $p) {
            $html .= view('places/_card', ['place' => $p]);
        }

        return $this->response->setJSON([
            'html'    => $html,
            'total'   => $total,
            'showing' => $showing,
            'hasMore' => $showing < $total,
        ]);
    }

    // GET /weather?city= — AJAX weather for city name
    public function weather(): \CodeIgniter\HTTP\ResponseInterface {
        $city = trim($this->request->getGet('city') ?? 'London');
        $key  = $this->keys->weatherKey;

        if (!$key || $key === 'YOUR_OPENWEATHER_KEY') {
            return $this->response->setJSON(['error' => 'No weather API key set']);
        }

        $url = 'https://api.openweathermap.org/data/2.5/weather?'
             . http_build_query(['q' => $city, 'appid' => $key, 'units' => 'metric']);
        $raw = $this->curlGet($url);
        if (!$raw) return $this->response->setJSON(['error' => 'Weather unavailable']);

        $w = json_decode($raw, true);
        if (!isset($w['main'])) return $this->response->setJSON(['error' => 'Weather unavailable']);

        return $this->response->setJSON([
            'temp'        => round($w['main']['temp']),
            'feels_like'  => round($w['main']['feels_like']),
            'humidity'    => $w['main']['humidity'],
            'description' => ucfirst($w['weather'][0]['description']),
            'icon'        => $w['weather'][0]['icon'],
            'wind_speed'  => round($w['wind']['speed'] * 3.6),
            'city_name'   => $w['name'],
        ]);
    }

    // ── Private helpers ───────────────────────────────────────
    private function curlGet(string $url): string|false {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $res = curl_exec($ch);
        $ok  = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
        curl_close($ch);
        return ($ok && $res) ? $res : false;
    }

    private function mapTypesToCategory(array $types): int {
        $db  = \Config\Database::connect();
        $map = [
            'park'             => 'parks',    'natural_feature'  => 'parks',
            'cafe'             => 'cafes',    'bakery'           => 'cafes',
            'restaurant'       => 'restaurants', 'food'          => 'restaurants',
            'bar'              => 'nightlife', 'night_club'      => 'nightlife',
            'museum'           => 'museums',  'art_gallery'      => 'museums',
            'shopping_mall'    => 'shopping', 'store'            => 'shopping',
            'movie_theater'    => 'entertainment', 'amusement_park' => 'entertainment',
            'stadium'          => 'entertainment',
            'church'           => 'historic', 'tourist_attraction' => 'historic',
            'place_of_worship' => 'historic',
        ];
        foreach ($types as $t) {
            if (isset($map[$t])) {
                $cat = $db->table('categories')->where('slug', $map[$t])->get()->getRowArray();
                if ($cat) return (int)$cat['id'];
            }
        }
        return 1;
    }
}