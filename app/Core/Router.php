<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LibraryController;
use App\Controllers\LogController;
use App\Controllers\NotificationController;
use App\Controllers\PublicController;
use App\Controllers\ReportController;
use App\Controllers\TicketController;
use App\Controllers\UserController;

class Router
{
    public function dispatch(): void
    {
        $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $base = trim(BASE_URL, '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = trim(substr($path, strlen($base)), '/');
        }
        $path = $path === '' ? 'request' : $path;
        $method = $_SERVER['REQUEST_METHOD'];

        $routes = [
            ['GET', 'request', [PublicController::class, 'requestForm']],
            ['POST', 'request', [PublicController::class, 'submitRequest']],
            ['GET', 'confirm/{token}', [PublicController::class, 'confirmForm']],
            ['POST', 'confirm/{token}', [PublicController::class, 'confirmComplete']],
            ['GET', 'api/offices', [PublicController::class, 'offices']],
            ['GET', 'api/services', [PublicController::class, 'services']],
            ['GET', 'login', [AuthController::class, 'loginForm']],
            ['POST', 'login', [AuthController::class, 'login']],
            ['GET', 'register', [AuthController::class, 'registerForm']],
            ['POST', 'register', [AuthController::class, 'register']],
            ['POST', 'logout', [AuthController::class, 'logout']],
            ['GET', 'dashboard', [DashboardController::class, 'index']],
            ['GET', 'tickets', [TicketController::class, 'index']],
            ['GET', 'tickets/{id}', [TicketController::class, 'show']],
            ['POST', 'tickets/{id}/assign', [TicketController::class, 'assign']],
            ['POST', 'tickets/{id}/reassign', [TicketController::class, 'reassign']],
            ['POST', 'tickets/{id}/status', [TicketController::class, 'updateStatus']],
            ['POST', 'tickets/{id}/attachments', [TicketController::class, 'attach']],
            ['GET', 'tickets/attachments/{id}/download', [TicketController::class, 'downloadAttachment']],
            ['POST', 'tickets/{id}/endorse', [TicketController::class, 'endorse']],
            ['POST', 'tickets/{id}/reopen', [TicketController::class, 'reopen']],
            ['POST', 'tickets/escalate-overdue', [TicketController::class, 'escalateOverdue']],
            ['GET', 'notifications/{id}/open', [NotificationController::class, 'open']],
            ['POST', 'notifications/read-all', [NotificationController::class, 'markAllRead']],
            ['GET', 'users', [UserController::class, 'index']],
            ['GET', 'profile', [UserController::class, 'profile']],
            ['POST', 'profile', [UserController::class, 'updateProfile']],
            ['GET', 'users/create', [UserController::class, 'create']],
            ['POST', 'users', [UserController::class, 'store']],
            ['GET', 'users/{id}/edit', [UserController::class, 'edit']],
            ['POST', 'users/{id}/update', [UserController::class, 'update']],
            ['GET', 'libraries/services', [LibraryController::class, 'services']],
            ['POST', 'libraries/service-categories', [LibraryController::class, 'storeServiceCategory']],
            ['POST', 'libraries/service-categories/{id}/update', [LibraryController::class, 'updateServiceCategory']],
            ['POST', 'libraries/service-categories/{id}/delete', [LibraryController::class, 'deleteServiceCategory']],
            ['POST', 'libraries/service-items', [LibraryController::class, 'storeServiceItem']],
            ['POST', 'libraries/service-items/{id}/update', [LibraryController::class, 'updateServiceItem']],
            ['POST', 'libraries/service-items/{id}/delete', [LibraryController::class, 'deleteServiceItem']],
            ['GET', 'libraries/locations', [LibraryController::class, 'locations']],
            ['POST', 'libraries/regions', [LibraryController::class, 'storeRegion']],
            ['POST', 'libraries/regions/{id}/update', [LibraryController::class, 'updateRegion']],
            ['POST', 'libraries/regions/{id}/delete', [LibraryController::class, 'deleteRegion']],
            ['POST', 'libraries/offices', [LibraryController::class, 'storeOffice']],
            ['POST', 'libraries/offices/{id}/update', [LibraryController::class, 'updateOffice']],
            ['POST', 'libraries/offices/{id}/delete', [LibraryController::class, 'deleteOffice']],
            ['GET', 'reports', [ReportController::class, 'index']],
            ['GET', 'logs', [LogController::class, 'index']],
            ['POST', 'logs/email-test', [LogController::class, 'testEmail']],
        ];

        foreach ($routes as [$routeMethod, $pattern, $handler]) {
            $params = $this->match($method, $routeMethod, $path, $pattern);
            if ($params !== null) {
                [$class, $action] = $handler;
                (new $class())->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        echo 'Page not found';
    }

    private function match(string $method, string $routeMethod, string $path, string $pattern): ?array
    {
        if ($method !== $routeMethod) {
            return null;
        }

        $regex = '#^' . preg_replace('#\{[^/]+\}#', '([^/]+)', $pattern) . '$#';
        if (!preg_match($regex, $path, $matches)) {
            return null;
        }

        array_shift($matches);
        return $matches;
    }
}
