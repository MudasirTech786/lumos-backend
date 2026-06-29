<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shoot;
use App\Models\CrewMember;
use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\InventoryAsset;
use App\Models\ProductionInvoice;
use App\Models\User;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $q = $request->validate(['q' => 'nullable|string|max:200'])['q'] ?? '';

        if (!trim($q)) {
            return response()->json([
                'productions' => [],
                'crew' => [],
                'employees' => [],
                'inventory' => [],
                'assets' => [],
                'invoices' => [],
                'users' => [],
            ]);
        }

        $keyword = '%' . $q . '%';

        $productions = Shoot::where(function ($query) use ($keyword) {
            $query->where('title', 'like', $keyword)
                ->orWhere('client_name', 'like', $keyword)
                ->orWhere('location', 'like', $keyword);
        })
            ->orderByRaw("CASE WHEN title LIKE ? THEN 0 WHEN client_name LIKE ? THEN 1 ELSE 2 END", [$q . '%', $q . '%'])
            ->limit(8)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'subtitle' => $s->status ? ucfirst($s->status) . ' Production' : 'Production',
                'type' => 'production',
                'route' => '/dashboard/shoots/' . $s->id,
                'status' => $s->status,
            ]);

        $crew = CrewMember::where(function ($query) use ($keyword) {
            $query->where('name', 'like', $keyword)
                ->orWhere('email', 'like', $keyword)
                ->orWhere('designation', 'like', $keyword);
        })
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 WHEN email LIKE ? THEN 1 ELSE 2 END", [$q . '%', $q . '%'])
            ->limit(8)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'title' => $c->name,
                'subtitle' => $c->designation ?? 'Crew Member',
                'type' => 'crew',
                'route' => '/dashboard/crew/' . $c->id,
            ]);

        $employees = Employee::where(function ($query) use ($keyword) {
            $query->where('name', 'like', $keyword)
                ->orWhere('email', 'like', $keyword)
                ->orWhere('employee_code', 'like', $keyword)
                ->orWhere('designation', 'like', $keyword);
        })
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 WHEN employee_code LIKE ? THEN 1 ELSE 2 END", [$q . '%', $q . '%'])
            ->limit(8)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'title' => $e->name,
                'subtitle' => ($e->designation ?? 'Employee') . ($e->department ? ' - ' . $e->department : ''),
                'type' => 'employee',
                'route' => '/dashboard/employees/' . $e->id,
            ]);

        $inventory = InventoryItem::with('category')
            ->where(function ($query) use ($keyword) {
                $query->where('name', 'like', $keyword)
                    ->orWhere('model', 'like', $keyword)
                    ->orWhere('sku', 'like', $keyword);
            })
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 WHEN model LIKE ? THEN 1 ELSE 2 END", [$q . '%', $q . '%'])
            ->limit(8)
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->name . ($item->model ? ' (' . $item->model . ')' : ''),
                'subtitle' => ($item->category?->name ?? 'Inventory') . ' · ' . ($item->status ?? 'available'),
                'type' => 'inventory',
                'route' => '/dashboard/inventory/items',
                'status' => $item->status,
            ]);

        $assets = InventoryAsset::with('item.category')
            ->where(function ($query) use ($keyword) {
                $query->where('asset_code', 'like', $keyword)
                    ->orWhere('serial_number', 'like', $keyword)
                    ->orWhereHas('item', fn($q) => $q->where('name', 'like', $keyword));
            })
            ->orderByRaw("CASE WHEN asset_code LIKE ? THEN 0 WHEN serial_number LIKE ? THEN 1 ELSE 2 END", [$q . '%', $q . '%'])
            ->limit(8)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'title' => $a->asset_code ?? 'Asset #' . $a->id,
                'subtitle' => ($a->item?->name ?? 'Unknown') . ' · ' . ($a->status ?? 'unknown'),
                'type' => 'asset',
                'route' => '/dashboard/inventory/assets',
                'status' => $a->status,
            ]);

        $invoices = ProductionInvoice::with('shoot')
            ->where(function ($query) use ($keyword) {
                $query->where('invoice_number', 'like', $keyword)
                    ->orWhere('title', 'like', $keyword);
            })
            ->orderByRaw("CASE WHEN invoice_number LIKE ? THEN 0 WHEN title LIKE ? THEN 1 ELSE 2 END", [$q . '%', $q . '%'])
            ->limit(8)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'title' => $inv->invoice_number ?? 'INV#' . $inv->id,
                'subtitle' => ($inv->shoot?->title ?? 'N/A') . ' · ' . number_format($inv->total_amount ?? 0) . ' PKR',
                'type' => 'invoice',
                'route' => '/dashboard/invoices/production-invoices/' . $inv->id,
                'status' => $inv->status,
            ]);

        $users = User::where(function ($query) use ($keyword) {
            $query->where('name', 'like', $keyword)
                ->orWhere('email', 'like', $keyword);
        })
            ->orderByRaw("CASE WHEN name LIKE ? THEN 0 WHEN email LIKE ? THEN 1 ELSE 2 END", [$q . '%', $q . '%'])
            ->limit(8)
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'title' => $u->name,
                'subtitle' => $u->email,
                'type' => 'user',
                'route' => '/dashboard/users',
            ]);

        return response()->json([
            'productions' => $productions,
            'crew' => $crew,
            'employees' => $employees,
            'inventory' => $inventory,
            'assets' => $assets,
            'invoices' => $invoices,
            'users' => $users,
        ]);
    }
}
