<?php
namespace App\Controllers;
use App\Models\UserModel;

class Auth extends BaseController {

    protected UserModel $userModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        $this->userModel = new UserModel();
    }

    public function login(): string|\CodeIgniter\HTTP\RedirectResponse {
        // Already logged in
        if (session()->get('user_id')) return redirect()->to(base_url());

        $error = session()->getFlashdata('error');

        if ($this->request->is('post')) {
            $email    = trim($this->request->getPost('email') ?? '');
            $password = $this->request->getPost('password') ?? '';
            $user     = $this->userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                $colors = ['#f59e0b','#3b82f6','#10b981','#ef4444','#8b5cf6','#f97316'];
                session()->set([
                    'user_id'      => $user['id'],
                    'username'     => $user['username'],
                    'avatar_color' => $user['avatar_color'] ?? $colors[0],
                    'logged_in'    => true,
                ]);
                session()->setFlashdata('success', 'Welcome back, ' . esc($user['username']) . '!');
                return redirect()->to(base_url());
            }
            $error = 'Invalid email or password.';
        }

        return view('layouts/main', [
            'title'   => 'Sign In | Explorer',
            'content' => view('auth/login', ['error' => $error])
        ]);
    }

    public function register(): string|\CodeIgniter\HTTP\RedirectResponse {
        // Already logged in
        if (session()->get('user_id')) return redirect()->to(base_url());

        $error = null;

        if ($this->request->is('post')) {
            $username = trim($this->request->getPost('username') ?? '');
            $email    = trim($this->request->getPost('email') ?? '');
            $password = $this->request->getPost('password') ?? '';
            $confirm  = $this->request->getPost('confirm') ?? '';

            if (!$username || !$email || strlen($password) < 6) {
                $error = 'Please fill all fields. Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } elseif ($this->userModel->findByEmail($email)) {
                $error = 'An account with that email already exists.';
            } elseif ($this->userModel->where('username', $username)->countAllResults() > 0) {
                $error = 'That username is already taken.';
            } else {
                $colors = ['#f59e0b','#3b82f6','#10b981','#ef4444','#8b5cf6','#f97316'];
                $color  = $colors[array_rand($colors)];

                $id = $this->userModel->insert([
                    'username'      => $username,
                    'email'         => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'avatar_color'  => $color,
                ]);

                session()->set([
                    'user_id'      => $id,
                    'username'     => $username,
                    'avatar_color' => $color,
                    'logged_in'    => true,
                ]);
                session()->setFlashdata('success', 'Welcome to Explorer, ' . esc($username) . '! 🎉');
                return redirect()->to(base_url());
            }
        }

        return view('layouts/main', [
            'title'   => 'Create Account | Explorer',
            'content' => view('auth/register', ['error' => $error])
        ]);
    }

    public function logout(): \CodeIgniter\HTTP\RedirectResponse {
        session()->destroy();
        session()->setFlashdata('success', 'You have been signed out.');
        return redirect()->to(base_url('login'));
    }
}