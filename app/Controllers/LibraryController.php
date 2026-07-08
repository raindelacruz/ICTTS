<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Library;
use App\Models\Ticket;
use App\Services\ActivityLogger;

class LibraryController extends Controller
{
    public function services(): void
    {
        Auth::requireRole(['admin']);
        $library = new Library();
        $this->view('libraries/services', [
            'categories' => $library->allCategories($_GET),
            'filters' => $_GET,
        ]);
    }

    public function serviceCategory(string $id): void
    {
        Auth::requireRole(['admin']);
        $library = new Library();
        $category = $library->serviceCategory((int) $id);
        if (!$category) {
            flash('error', 'Service category not found.');
            $this->redirect('libraries/services');
        }

        $filters = $_GET;
        $filters['item_category_id'] = (string) $category['id'];

        $this->view('libraries/service_category', [
            'category' => $category,
            'items' => $library->serviceItemsForManagement($filters),
            'filters' => $filters,
            'priorities' => Ticket::PRIORITIES,
        ]);
    }

    public function storeServiceCategory(): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->addServiceCategory(trim($_POST['name'] ?? ''));
        ActivityLogger::log('Service library change', 'service_category', null, 'Service category added.');
        flash('success', 'Service category added.');
        $this->redirect('libraries/services');
    }

    public function updateServiceCategory(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->updateServiceCategory((int) $id, trim($_POST['name'] ?? ''), $_POST['status'] ?? 'active');
        ActivityLogger::log('Service library change', 'service_category', $id, 'Service category updated.');
        flash('success', 'Service category updated.');
        $this->redirect('libraries/services');
    }

    public function deleteServiceCategory(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->deleteServiceCategory((int) $id);
        ActivityLogger::log('Service library change', 'service_category', $id, 'Service category deleted.');
        flash('success', 'Service category deleted.');
        $this->redirect('libraries/services');
    }

    public function storeServiceItem(): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $categoryId = (int) $_POST['service_category_id'];
        (new Library())->addServiceItemWithPriority($categoryId, trim($_POST['name']), $_POST['default_priority'] ?? 'Medium');
        ActivityLogger::log('Service library change', 'service_item', null, 'Service item added.');
        flash('success', 'Service item added.');
        $this->redirect('libraries/services/' . $categoryId);
    }

    public function updateServiceItem(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $categoryId = (int) $_POST['service_category_id'];
        (new Library())->updateServiceItemWithPriority((int) $id, $categoryId, trim($_POST['name'] ?? ''), $_POST['status'] ?? 'active', $_POST['default_priority'] ?? 'Medium');
        ActivityLogger::log('Service library change', 'service_item', $id, 'Service item updated.');
        flash('success', 'Service item updated.');
        $this->redirect('libraries/services/' . $categoryId);
    }

    public function deleteServiceItem(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        $library = new Library();
        $categoryId = $library->serviceItemCategoryId((int) $id);
        $library->deleteServiceItem((int) $id);
        ActivityLogger::log('Service library change', 'service_item', $id, 'Service item deleted.');
        flash('success', 'Service item deleted.');
        $this->redirect($categoryId ? 'libraries/services/' . $categoryId : 'libraries/services');
    }

    public function locations(): void
    {
        Auth::requireRole(['admin']);
        $library = new Library();
        $this->view('libraries/locations', [
            'regions' => $library->allRegions($_GET),
            'activeRegions' => $library->regions(),
            'offices' => $library->officesForManagement($_GET),
            'filters' => $_GET,
            'officeTypes' => ['Regional Office', 'Branch Office', 'Central Office', 'District Office', 'Other'],
        ]);
    }

    public function storeRegion(): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->addRegion(trim($_POST['code'] ?? ''), trim($_POST['name'] ?? ''));
        ActivityLogger::log('Location library change', 'region', null, 'Region added.');
        flash('success', 'Region added.');
        $this->redirect('libraries/locations');
    }

    public function updateRegion(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->updateRegion((int) $id, trim($_POST['code'] ?? ''), trim($_POST['name'] ?? ''), $_POST['status'] ?? 'active');
        ActivityLogger::log('Location library change', 'region', $id, 'Region updated.');
        flash('success', 'Region updated.');
        $this->redirect('libraries/locations');
    }

    public function deleteRegion(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->deleteRegion((int) $id);
        ActivityLogger::log('Location library change', 'region', $id, 'Region deleted.');
        flash('success', 'Region deleted.');
        $this->redirect('libraries/locations');
    }

    public function storeOffice(): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->addOffice((int) $_POST['region_id'], trim($_POST['name']), $_POST['office_type']);
        ActivityLogger::log('Location library change', 'office', null, 'Office added.');
        flash('success', 'Office added.');
        $this->redirect('libraries/locations');
    }

    public function updateOffice(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->updateOffice((int) $id, (int) $_POST['region_id'], trim($_POST['name'] ?? ''), $_POST['office_type'], $_POST['status'] ?? 'active');
        ActivityLogger::log('Location library change', 'office', $id, 'Office updated.');
        flash('success', 'Office updated.');
        $this->redirect('libraries/locations');
    }

    public function deleteOffice(string $id): void
    {
        Auth::requireRole(['admin']);
        Csrf::validate($_POST['_csrf'] ?? null);
        (new Library())->deleteOffice((int) $id);
        ActivityLogger::log('Location library change', 'office', $id, 'Office deleted.');
        flash('success', 'Office deleted.');
        $this->redirect('libraries/locations');
    }
}
