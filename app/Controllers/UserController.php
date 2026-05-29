<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\User;
use App\Services\ActivityLogger;

class UserController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['admin']);
        $this->view('users/index', ['users' => (new User())->all()]);
    }

    public function create(): void
    {
        Auth::requireRole(['admin']);
        $this->view('users/form', ['user' => null]);
    }

    public function profile(): void
    {
        Auth::requireLogin();
        $this->view('users/profile', ['user' => (new User())->find(Auth::id())]);
    }

    public function updateProfile(): void
    {
        Auth::requireLogin();
        Csrf::validate($_POST['_csrf'] ?? null);
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
        ];
        $userModel = new User();
        if ($data['name'] === '' || $data['position'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || ($data['password'] !== '' && strlen($data['password']) < 8)) {
            flash('error', 'Please complete your profile fields. Password must be at least 8 characters when changed.');
            $this->redirect('profile');
        }
        if ($userModel->emailExists($data['email'], Auth::id())) {
            flash('error', 'That email address is already used by another account.');
            $this->redirect('profile');
        }
        $userModel->updateProfile(Auth::id(), $data);
        $_SESSION['user']['name'] = $data['name'];
        $_SESSION['user']['email'] = $data['email'];
        ActivityLogger::log('Profile update', 'user', (string) Auth::id(), 'User updated own profile.');
        flash('success', 'Profile updated.');
        $this->redirect('profile');
    }

    public function store(): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $data = $this->data();
        if (!$this->valid($data, true)) {
            flash('error', 'Please complete all required user fields. Password must be at least 8 characters.');
            $this->redirect('users/create');
        }
        $userModel = new User();
        if ($userModel->emailExists($data['email']) || $userModel->idNumberExists($data['id_number'])) {
            flash('error', 'The ID number or email address is already registered.');
            $this->redirect('users/create');
        }
        $id = $userModel->create($data);
        ActivityLogger::log('User management changes', 'user', (string) $id, 'User created.');
        flash('success', 'User created.');
        $this->redirect('users');
    }

    public function edit(string $id): void
    {
        Auth::requireRole(['admin']);
        $this->view('users/form', ['user' => (new User())->find((int) $id)]);
    }

    public function update(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $data = $this->data();
        if (!$this->valid($data, false)) {
            flash('error', 'Please complete all required user fields.');
            $this->redirect('users/' . $id . '/edit');
        }
        $userModel = new User();
        if ($userModel->emailExists($data['email'], (int) $id) || $userModel->idNumberExists($data['id_number'], (int) $id)) {
            flash('error', 'The ID number or email address is already registered.');
            $this->redirect('users/' . $id . '/edit');
        }
        $userModel->update((int) $id, $data);
        ActivityLogger::log('User management changes', 'user', $id, 'User updated.');
        flash('success', 'User updated.');
        $this->redirect('users');
    }

    private function data(): array
    {
        return [
            'id_number' => trim($_POST['id_number'] ?? ''),
            'name' => trim($_POST['name'] ?? ''),
            'position' => trim($_POST['position'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role' => $_POST['role'] ?? 'technical',
            'status' => $_POST['status'] ?? 'active',
        ];
    }

    private function valid(array $data, bool $passwordRequired): bool
    {
        return $data['id_number'] !== ''
            && $data['name'] !== ''
            && $data['position'] !== ''
            && filter_var($data['email'], FILTER_VALIDATE_EMAIL)
            && in_array($data['role'], ['technical', 'unit_head', 'division_chief', 'admin'], true)
            && in_array($data['status'], ['active', 'inactive'], true)
            && (!$passwordRequired || strlen($data['password']) >= 8)
            && ($data['password'] === '' || strlen($data['password']) >= 8);
    }
}
