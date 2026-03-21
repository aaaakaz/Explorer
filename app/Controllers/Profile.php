<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ReviewModel;

class Profile extends BaseController {

    protected UserModel   $userModel;
    protected ReviewModel $reviewModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        $this->userModel   = new UserModel();
        $this->reviewModel = new ReviewModel();
    }

    // ── Redirect to login if not logged in ───────────────────────
    private function requireLogin(): ?\CodeIgniter\HTTP\RedirectResponse {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Please sign in to view your profile.');
            return redirect()->to(base_url('login'));
        }
        return null;
    }

    // GET /profile ─────────────────────────────────────────────────
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse {
        if ($r = $this->requireLogin()) return $r;

        $db     = \Config\Database::connect();
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);

        // Stats
        $reviewCount  = $db->table('reviews')->where('user_id', $userId)->countAllResults();
        $visitedCount = $db->table('recently_viewed')->where('user_id', $userId)->countAllResults();

        // My reviews with place info
        $reviews = $db->table('reviews r')
            ->select('r.*, p.name AS place_name, p.id AS place_id, p.photo_url,
                      c.name AS category_name, c.color AS category_color, c.icon AS category_icon')
            ->join('places p', 'p.id = r.place_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->where('r.user_id', $userId)
            ->orderBy('r.created_at', 'DESC')
            ->get()->getResultArray();

        // Recently viewed places
        $recentlyViewed = $db->table('recently_viewed rv')
            ->select('p.*, c.name AS category_name, c.color AS category_color,
                      c.icon AS category_icon, c.slug AS category_slug,
                      rv.viewed_at,
                      IFNULL(AVG(r2.rating),0) AS avg_rating,
                      COUNT(r2.id) AS review_count')
            ->join('places p', 'p.id = rv.place_id', 'left')
            ->join('categories c', 'c.id = p.category_id', 'left')
            ->join('reviews r2', 'r2.place_id = p.id', 'left')
            ->where('rv.user_id', $userId)
            ->groupBy('rv.id')
            ->orderBy('rv.viewed_at', 'DESC')
            ->limit(12)
            ->get()->getResultArray();

        return view('layouts/main', [
            'title'          => esc($user['username']) . "'s Profile | Explorer",
            'content'        => view('profile/index', [
                'user'           => $user,
                'reviewCount'    => $reviewCount,
                'visitedCount'   => $visitedCount,
                'reviews'        => $reviews,
                'recentlyViewed' => $recentlyViewed,
            ])
        ]);
    }

    // POST /profile/update ─────────────────────────────────────────
    public function update(): \CodeIgniter\HTTP\RedirectResponse {
        if ($r = $this->requireLogin()) return $r;

        $userId   = session()->get('user_id');
        $user     = $this->userModel->find($userId);
        $username = trim($this->request->getPost('username') ?? '');
        $email    = trim($this->request->getPost('email') ?? '');
        $current  = $this->request->getPost('current_password') ?? '';
        $newPass  = $this->request->getPost('new_password') ?? '';
        $confirm  = $this->request->getPost('confirm_password') ?? '';

        // Validate username
        if (!$username || strlen($username) < 3) {
            session()->setFlashdata('error', 'Username must be at least 3 characters.');
            return redirect()->to(base_url('profile'));
        }

        // Check username taken by someone else
        $existing = $this->userModel->where('username', $username)->where('id !=', $userId)->first();
        if ($existing) {
            session()->setFlashdata('error', 'That username is already taken.');
            return redirect()->to(base_url('profile'));
        }

        $updateData = ['username' => $username, 'email' => $email];

        // Password change (optional)
        if ($newPass) {
            if (!password_verify($current, $user['password_hash'])) {
                session()->setFlashdata('error', 'Current password is incorrect.');
                return redirect()->to(base_url('profile'));
            }
            if (strlen($newPass) < 6) {
                session()->setFlashdata('error', 'New password must be at least 6 characters.');
                return redirect()->to(base_url('profile'));
            }
            if ($newPass !== $confirm) {
                session()->setFlashdata('error', 'New passwords do not match.');
                return redirect()->to(base_url('profile'));
            }
            $updateData['password_hash'] = password_hash($newPass, PASSWORD_DEFAULT);
        }

        $this->userModel->update($userId, $updateData);

        // Update session username
        session()->set('username', $username);
        session()->setFlashdata('success', 'Profile updated successfully!');
        return redirect()->to(base_url('profile'));
    }

    // POST /profile/avatar ─────────────────────────────────────────
    public function avatar(): \CodeIgniter\HTTP\RedirectResponse {
        if ($r = $this->requireLogin()) return $r;

        $color  = $this->request->getPost('color') ?? '#f59e0b';
        $userId = session()->get('user_id');

        // Whitelist allowed colours
        $allowed = ['#f59e0b','#3b82f6','#10b981','#ef4444','#8b5cf6',
                    '#f97316','#06b6d4','#ec4899','#14b8a6','#a855f7'];
        if (!in_array($color, $allowed)) $color = '#f59e0b';

        $this->userModel->update($userId, ['avatar_color' => $color]);
        session()->set('avatar_color', $color);
        session()->setFlashdata('success', 'Avatar colour updated!');
        return redirect()->to(base_url('profile'));
    }
}
