<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\RateLimiter;
use App\Models\User;
use App\Services\ActivityLogger;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        $this->publicView('auth/login');
    }

    public function registerForm(): void
    {
        $this->publicView('auth/register');
    }

    public function register(): void
    {
        Csrf::validate($_POST['_csrf'] ?? null);
        if (!RateLimiter::hit('register:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 3600)) {
            flash('error', 'Too many registration attempts. Please try again later.');
            $this->redirect('register');
        }

        $data = [
            'id_number' => trim($_POST['id_number'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => 'technical',
            'status' => 'inactive',
        ];
        $userModel = new User();

        if ($data['id_number'] === '' || $data['name'] === '' || $data['position'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['password']) < 8) {
            flash('error', 'Please complete all required fields. Password must be at least 8 characters.');
            $this->redirect('register');
        }
        if ($userModel->emailExists($data['email']) || $userModel->idNumberExists($data['id_number'])) {
            flash('error', 'The ID number or email address is already registered.');
            $this->redirect('register');
        }

        $id = $userModel->create($data);
        ActivityLogger::log('ICT personnel registration', 'user', (string) $id, 'Pending administrator activation.', $data['name']);
        flash('success', 'Registration submitted. Your account must be activated by the system administrator before login.');
        $this->redirect('login');
    }

    public function login(): void
    {
        Csrf::validate($_POST['_csrf'] ?? null);
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $limitKey = 'login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ':' . strtolower($email);
        if (!RateLimiter::hit($limitKey, 5, 900)) {
            flash('error', 'Too many login attempts. Please try again after 15 minutes.');
            $this->redirect('login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('error', 'Invalid email or password.');
            $this->redirect('login');
        }

        Auth::login($user);
        RateLimiter::clear($limitKey);
        $userModel->touchLogin((int) $user['id']);
        ActivityLogger::log('User login', 'user', (string) $user['id']);
        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        Csrf::validate($_POST['_csrf'] ?? null);
        ActivityLogger::log('User logout', 'user', (string) Auth::id());
        Auth::logout();
        flash('success', 'You have been logged out.');
        $this->redirect('login');
    }
}
