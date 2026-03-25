<?php
namespace App\Controllers;

use App\Models\PlaceModel;
use App\Models\ReviewModel;
use App\Models\CategoryModel;
use Config\ApiKeys;

class Places extends BaseController {

    protected \CodeIgniter\Database\BaseConnection $db;
    protected PlaceModel    $placeModel;
    protected ReviewModel   $reviewModel;
    protected CategoryModel $categoryModel;
    protected ApiKeys       $keys;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                               \CodeIgniter\HTTP\ResponseInterface $response,
                               \Psr\Log\LoggerInterface $logger) {
    parent::initController($request, $response, $logger);
    $this->db            = \Config\Database::connect();  // ← ADD THIS LINE
    $this->placeModel    = new PlaceModel();
    $this->reviewModel   = new ReviewModel();
    $this->categoryModel = new CategoryModel();
    $this->keys          = config('ApiKeys');
    }

    // GET /places — full listing page with sidebar filters
    public function index(): string {
        $category = $this->request->getGet('category') ?? '';
        $sort     = $this->request->getGet('sort')     ?? 'newest';
        $search   = $this->request->getGet('q')        ?? '';

        $places     = $this->placeModel->getFiltered($category, $sort, $search, 9, 0);
        $total      = $this->placeModel->countFiltered($category, $search);
        $categories = $this->categoryModel->allWithCount();

        return view('layouts/main', [
            'title'   => 'Explore Places | Explorer',
            'content' => view('places/index', [
                'places'     => $places,
                'total'      => $total,
                'showing'    => count($places),
                'categories' => $categories,
                'category'   => $category,
                'sort'       => $sort,
                'search'     => $search,
                'hasMore'    => $total > 9,
            ])
        ]);
    }

    // POST /places/favourite — AJAX toggle save/unsave
    public function favourite(): \CodeIgniter\HTTP\ResponseInterface {
    if (!session()->get('logged_in')) {
        return $this->response->setStatusCode(401)
            ->setJSON(['error' => 'Please sign in to save places.']);
    }

    $userId  = session()->get('user_id');
    $placeId = (int)$this->request->getPost('place_id');

    if (!$placeId) {
        return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid place.']);
    }

    // Check if already saved
    $existing = $this->db->table('favourites')
        ->where('user_id', $userId)
        ->where('place_id', $placeId)
        ->get()->getRowArray();

    if ($existing) {
        // Unsave
        $this->db->table('favourites')
            ->where('user_id', $userId)
            ->where('place_id', $placeId)
            ->delete();
        return $this->response->setJSON(['saved' => false]);
    } else {
        // Save
        $this->db->table('favourites')->insert([
            'user_id'  => $userId,
            'place_id' => $placeId,
        ]);
        return $this->response->setJSON(['saved' => true]);
    }
}

    // GET /places/(:num) — full detail page
    public function detail(int $id): string {
    $place = $this->placeModel->getPlace($id);
    if (!$place) return redirect()->to('/places')->getBody();

    // Fetch and cache Unsplash photo if not already stored
    if (empty($place['photo_url'])) {
        $key = $this->keys->unsplashKey;
        if ($key) {
            $q   = urlencode($place['name'] . ' ' . $place['city']);
            $url = "https://api.unsplash.com/search/photos?query={$q}&per_page=1&orientation=landscape&client_id={$key}";
            $ch  = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER     => ['Accept-Version: v1'],
            ]);
            $raw = curl_exec($ch);
            curl_close($ch);
            if ($raw) {
                $data     = json_decode($raw, true);
                $photoUrl = $data['results'][0]['urls']['small'] ?? null;
                if ($photoUrl) {
                    try {
                        $this->placeModel->update($id, ['photo_url' => $photoUrl]);
                        } catch (\Exception $e) {
                        // Photo save failed — not critical, page still loads
                        }
                    $place['photo_url'] = $photoUrl;
                }
            }
        }
    }

    $reviews   = $this->reviewModel->getForPlace($id);
    $breakdown = $this->reviewModel->getRatingBreakdown($id);
    $related   = $this->placeModel->getRelated($place['category_id'], $id, 3);

    return view('layouts/main', [
        'title'   => $place['name'] . ' | Explorer',
        'content' => view('places/detail', [
            'place'     => $place,
            'reviews'   => $reviews,
            'breakdown' => $breakdown,
            'related'   => $related,
            'mapsKey'   => $this->keys->googleMapsKey,
        ])
    ]);
}
	public function bulkPhotos(): string {
    $db      = \Config\Database::connect();
    $places  = $db->table('places')->where('photo_url IS NULL')->get()->getResultArray();
    $key     = $this->keys->unsplashKey;
    $updated = 0;

    foreach ($places as $place) {
        if (!$key) break;
        $q   = urlencode($place['name'] . ' ' . $place['city']);
        $url = "https://api.unsplash.com/search/photos?query={$q}&per_page=1&orientation=landscape&client_id={$key}";
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => ['Accept-Version: v1'],
        ]);
        $raw = curl_exec($ch);
        curl_close($ch);

        if ($raw) {
            $data     = json_decode($raw, true);
            $photoUrl = $data['results'][0]['urls']['small'] ?? null;
            if ($photoUrl) {
                $db->table('places')->where('id', $place['id'])->update(['photo_url' => $photoUrl]);
                $updated++;
            }
        }
        sleep(1); // respect Unsplash rate limit
    }

    return "Done! Updated {$updated} places with photos. <a href='/~2402331/explorer/public/places'>Go back</a>";
}

    // POST /places/review — AJAX submit review (must be logged in)
    public function review(): \CodeIgniter\HTTP\ResponseInterface {
        $session = session();
        if (!$session->get('user_id')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Please sign in to leave a review.']);
        }

        $placeId = (int)$this->request->getPost('place_id');
        $rating  = min(5, max(1, (int)$this->request->getPost('rating')));
        $comment = trim($this->request->getPost('comment') ?? '');

        if (!$comment || strlen($comment) < 5) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Review must be at least 5 characters.']);
        }

        $id = $this->reviewModel->insert([
            'place_id' => $placeId,
            'user_id'  => $session->get('user_id'),
            'rating'   => $rating,
            'comment'  => $comment,
        ]);

       $review = $this->reviewModel->getForPlace($placeId);
$new    = array_values(array_filter($review, fn($r) => $r['id'] == $this->db->insertID()));

        return $this->response->setJSON([
            'success' => true,
            'review'  => $new[0] ?? ['author' => $session->get('username'), 'rating' => $rating, 'comment' => $comment, 'created_at' => date('Y-m-d H:i:s')],
        ]);
    }

    // GET /places/photos/(:num) — AJAX Unsplash photos for a place
    public function photos(int $id): \CodeIgniter\HTTP\ResponseInterface {
        $place = $this->placeModel->getPlace($id);
        if (!$place) return $this->response->setJSON(['photos' => []]);

        $key = $this->keys->unsplashKey;
        if (!$key || $key === 'YOUR_UNSPLASH_KEY') return $this->response->setJSON(['photos' => []]);

        $q   = urlencode($place['name'] . ' ' . $place['city']);
        $url = "https://api.unsplash.com/search/photos?query={$q}&per_page=8&orientation=landscape&client_id={$key}";
        $raw = $this->curlGet($url, ['Accept-Version: v1']);
        if (!$raw) return $this->response->setJSON(['photos' => []]);

        $data = json_decode($raw, true);
        return $this->response->setJSON([
            'photos' => array_map(fn($r) => [
                'small'   => $r['urls']['small'],
                'regular' => $r['urls']['regular'],
                'alt'     => $r['alt_description'] ?? $place['name'],
                'credit'  => $r['user']['name'],
                'link'    => $r['links']['html'],
            ], $data['results'] ?? [])
        ]);
    }

    // GET /places/weather/(:num) — AJAX live weather for a place's coordinates
    public function weather(int $id): \CodeIgniter\HTTP\ResponseInterface {
        $place = $this->placeModel->getPlace($id);
        if (!$place) return $this->response->setJSON(['error' => 'Place not found']);

        $key = $this->keys->weatherKey;
        if (!$key || $key === 'YOUR_OPENWEATHER_KEY') return $this->response->setJSON(['error' => 'No key']);

        $url = 'https://api.openweathermap.org/data/2.5/weather?' . http_build_query([
            'lat' => $place['lat'], 'lon' => $place['lng'],
            'appid' => $key, 'units' => 'metric',
        ]);
        $raw = $this->curlGet($url);
        if (!$raw) return $this->response->setJSON(['error' => 'Weather unavailable']);

        $w = json_decode($raw, true);
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

    // GET /places/filter — AJAX filtered/paginated place listing
    public function filter(): \CodeIgniter\HTTP\ResponseInterface {
        $category = $this->request->getGet('category') ?? '';
        $sort     = $this->request->getGet('sort')     ?? 'newest';
        $search   = $this->request->getGet('q')        ?? '';
        $price    = $this->request->getGet('price')    ?? '';
        $rating   = (float)($this->request->getGet('rating') ?? 0);
        $page     = max(1, (int)($this->request->getGet('page') ?? 1));
        $perPage  = 9;
        $offset   = ($page - 1) * $perPage;

        $places  = $this->placeModel->getFiltered($category, $sort, $search, $perPage, $offset, $price, $rating);
        $total   = $this->placeModel->countFiltered($category, $search, $price, $rating);
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

    private function curlGet(string $url, array $headers = []): string|false {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => false, CURLOPT_HTTPHEADER => $headers,
        ]);
        $res = curl_exec($ch);
        $ok  = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
        curl_close($ch);
        return ($ok && $res) ? $res : false;
    }
}