<?php

class AuthController extends Controller
{
    public function login(): void
    {
        if (isLoggedIn()) {
            $this->redirect('dashboard');
        }

        $this->view('auth/login');
    }

    public function authenticate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }

        $username = trim(post('username'));
        $password = post('password');

        if ($username === '' || $password === '') {
            flash('error', 'Username dan password wajib diisi.');
            setOld(['username' => $username]);
            $this->redirect('login');
        }

        $userModel = $this->model('User');
        $user = $userModel->findByUsername($username);

        $validPassword = false;
        if ($user) {
            $stored = (string) $user['password'];
            if (password_get_info($stored)['algo'] !== null) {
                // Password tersimpan dalam bentuk hash (normal).
                $validPassword = password_verify($password, $stored);
            } else {
                // Data lama yang masih plaintext: terima hanya jika persis sama,
                // lalu langsung diubah menjadi hash.
                $validPassword = hash_equals($stored, $password);
                if ($validPassword) {
                    $userModel->setPassword((int) $user['id_user'], $password);
                }
            }
        }

        if (!$validPassword) {
            flash('error', 'Username atau password salah.');
            setOld(['username' => $username]);
            $this->redirect('login');
        }

        if ($user['status'] !== 'aktif') {
            flash('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
            setOld(['username' => $username]);
            $this->redirect('login');
        }

        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id_user' => $user['id_user'],
            'nama_lengkap' => $user['nama_lengkap'],
            'username' => $user['username'],
            'nama_role' => $user['nama_role'],
        ];

        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
