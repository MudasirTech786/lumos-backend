<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Shoot;
use App\Models\CrewMember;
use App\Models\InventoryAsset;
use App\Models\AssetScanLog;
use App\Models\ProductionInvoice;

class DashboardController extends Controller
{
    public function kpis()
    {
        $cacheKey = 'dashboard:kpis';

        $data = Cache::remember($cacheKey, 30, function () {
            $shootCounts = Shoot::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('planning','active') THEN 1 ELSE 0 END) as active_productions
            ")->first();

            $crewOnSet = CrewMember::where('status', 'active')->count();

            $assetCounts = InventoryAsset::selectRaw("
                COUNT(*) as total_assets,
                SUM(CASE WHEN status = 'in_use' THEN 1 ELSE 0 END) as assets_deployed
            ")->first();

            $qrScansToday = AssetScanLog::whereDate('created_at', today())->count();

            $revenueMTD = Shoot::sum('client_invoice_amount');

            return [
                'active_productions' => (int) $shootCounts->active_productions,
                'crew_on_set' => $crewOnSet,
                'assets_deployed' => (int) $assetCounts->assets_deployed,
                'total_assets' => (int) $assetCounts->total_assets,
                'qr_scans_today' => $qrScansToday,
                'revenue_mtd' => $revenueMTD,
                'open_alerts' => 0,
            ];
        });

        return response()->json($data);
    }

    public function productions()
    {
        $cacheKey = 'dashboard:productions';

        $productions = Cache::remember($cacheKey, 30, function () {
            return Shoot::withCount([
                    'crewMembers',
                    'assetAllocations',
                ])
                ->withSum('expenses', 'amount')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($shoot) {
                    $budget = $shoot->client_budget ?? 0;
                    $spent = (float) ($shoot->expenses_sum_amount ?? 0);

                    return [
                        'id' => $shoot->id,
                        'title' => $shoot->title,
                        'client' => $shoot->client_name,
                        'location' => $shoot->location,
                        'status' => $shoot->status,
                        'crew' => $shoot->crew_members_count,
                        'assets' => $shoot->asset_allocations_count,
                        'budget' => $budget,
                        'spent' => $spent,
                    ];
                });
        });

        return response()->json($productions);
    }

    public function alerts()
    {
        $cacheKey = 'dashboard:alerts';

        $result = Cache::remember($cacheKey, 15, function () {
            $alerts = [];

            $noLocation = Shoot::whereNull('location')
                ->select('id', 'title')
                ->get();

            foreach ($noLocation as $shoot) {
                $alerts[] = [
                    'type' => 'production',
                    'severity' => 'high',
                    'title' => $shoot->title,
                    'detail' => 'Missing location',
                    'shoot_id' => $shoot->id,
                ];
            }

            $noCrew = Shoot::doesntHave('crewMembers')
                ->select('id', 'title')
                ->get();

            foreach ($noCrew as $shoot) {
                $alerts[] = [
                    'type' => 'production',
                    'severity' => 'high',
                    'title' => $shoot->title,
                    'detail' => 'No crew assigned',
                    'shoot_id' => $shoot->id,
                ];
            }

            $noSchedule = Shoot::whereNull('start_datetime')
                ->select('id', 'title')
                ->get();

            foreach ($noSchedule as $shoot) {
                $alerts[] = [
                    'type' => 'production',
                    'severity' => 'medium',
                    'title' => $shoot->title,
                    'detail' => 'No schedule added',
                    'shoot_id' => $shoot->id,
                ];
            }

            return [
                'count' => count($alerts),
                'high_priority' => collect($alerts)->where('severity', 'high')->count(),
                'alerts' => array_slice($alerts, 0, 10),
            ];
        });

        return response()->json($result);
    }

    public function financeTrend()
    {
        $cacheKey = 'dashboard:finance-trend';

        $result = Cache::remember($cacheKey, 300, function () {
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth();

            $monthKey = fn($y, $m) => $y . '-' . $m;

            $shootIds = Shoot::where('created_at', '>=', $sixMonthsAgo)
                ->select('id', 'client_invoice_amount', 'created_at')
                ->get();

            if ($shootIds->isEmpty()) {
                return $this->emptyFinanceResponse();
            }

            $allIds = $shootIds->pluck('id');

            $revenueByMonth = $shootIds->groupBy(fn($s) => $monthKey($s->created_at->year, $s->created_at->month))
                ->map(fn($group) => $group->sum('client_invoice_amount'));

            $crewCostsByShoot = DB::table('shoot_crew')
                ->join('shoots', 'shoots.id', '=', 'shoot_crew.shoot_id')
                ->whereIn('shoot_crew.shoot_id', $allIds)
                ->selectRaw('
                    shoot_crew.shoot_id,
                    SUM(shoot_crew.rate * (DATEDIFF(COALESCE(shoots.end_datetime, shoots.start_datetime), shoots.start_datetime) + 1)) as crew_cost
                ')
                ->groupBy('shoot_crew.shoot_id')
                ->pluck('crew_cost', 'shoot_id');

            $logisticsCostsByShoot = DB::table('shoot_logistics')
                ->whereIn('shoot_id', $allIds)
                ->selectRaw('shoot_id, SUM(estimated_cost) as log_cost')
                ->groupBy('shoot_id')
                ->pluck('log_cost', 'shoot_id');

            $inventoryCostsByShoot = DB::table('inventory_usages')
                ->join('inventory_items', 'inventory_items.id', '=', 'inventory_usages.inventory_item_id')
                ->join('shoots', 'shoots.id', '=', 'inventory_usages.shoot_id')
                ->whereIn('inventory_usages.shoot_id', $allIds)
                ->selectRaw('
                    inventory_usages.shoot_id,
                    SUM(inventory_usages.quantity * inventory_items.daily_rental_value * (DATEDIFF(COALESCE(shoots.end_datetime, shoots.start_datetime), shoots.start_datetime) + 1)) as inv_cost
                ')
                ->groupBy('inventory_usages.shoot_id')
                ->pluck('inv_cost', 'shoot_id');

            $expensesByShoot = DB::table('shoot_expenses')
                ->whereIn('shoot_id', $allIds)
                ->selectRaw('shoot_id, SUM(amount) as exp_cost')
                ->groupBy('shoot_id')
                ->pluck('exp_cost', 'shoot_id');

            $shootCosts = collect();
            foreach ($allIds as $id) {
                $cost = (float) ($crewCostsByShoot->get($id, 0))
                    + (float) ($logisticsCostsByShoot->get($id, 0))
                    + (float) ($inventoryCostsByShoot->get($id, 0))
                    + (float) ($expensesByShoot->get($id, 0));
                $shootCosts[$id] = $cost;
            }

            $shootMonthMap = $shootIds->groupBy(fn($s) => $monthKey($s->created_at->year, $s->created_at->month));

            $months = collect();
            $totalRevenue = 0;
            $totalCost = 0;

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $key = $monthKey($date->year, $date->month);

                $monthRevenue = (float) ($revenueByMonth->get($key, 0));
                $monthShootIds = $shootMonthMap->get($key, collect())->pluck('id');

                $monthCost = 0;
                foreach ($monthShootIds as $id) {
                    $monthCost += $shootCosts->get($id, 0);
                }

                $totalRevenue += $monthRevenue;
                $totalCost += $monthCost;

                $months->push([
                    'm' => $date->format('M'),
                    'rev' => round($monthRevenue / 100000, 1),
                    'exp' => round($monthCost / 100000, 1),
                    'pro' => round(($monthRevenue - $monthCost) / 100000, 1),
                ]);
            }

            return [
                'chart' => $months,
                'summary' => [
                    'revenue' => $totalRevenue,
                    'expenses' => $totalCost,
                    'profit' => $totalRevenue - $totalCost,
                ],
            ];
        });

        return response()->json($result);
    }

    private function emptyFinanceResponse(): array
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'm' => $date->format('M'),
                'rev' => 0,
                'exp' => 0,
                'pro' => 0,
            ]);
        }

        return [
            'chart' => $months,
            'summary' => [
                'revenue' => 0,
                'expenses' => 0,
                'profit' => 0,
            ],
        ];
    }

    public function assetUtilization()
    {
        $cacheKey = 'dashboard:assets';

        $result = Cache::remember($cacheKey, 60, function () {
            $stats = InventoryAsset::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(CASE WHEN status = 'in_use' THEN 1 ELSE 0 END) as in_use,
                SUM(CASE WHEN status = 'under_repair' THEN 1 ELSE 0 END) as repair,
                SUM(CASE WHEN status = 'damaged' THEN 1 ELSE 0 END) as damaged
            ")->first();

            $totalAssets = (int) $stats->total;
            $inUse = (int) $stats->in_use;

            $overallUtilization = $totalAssets > 0
                ? round(($inUse / $totalAssets) * 100)
                : 0;

            $chart = DB::table('inventory_categories')
                ->join('inventory_items', 'inventory_items.category_id', '=', 'inventory_categories.id')
                ->join('inventory_assets', 'inventory_assets.inventory_item_id', '=', 'inventory_items.id')
                ->selectRaw("
                    inventory_categories.name,
                    COUNT(*) as total,
                    SUM(CASE WHEN inventory_assets.status = 'in_use' THEN 1 ELSE 0 END) as used
                ")
                ->groupBy('inventory_categories.id', 'inventory_categories.name')
                ->orderBy('inventory_categories.name')
                ->get()
                ->map(function ($cat) {
                    $usedPct = $cat->total > 0
                        ? round(($cat->used / $cat->total) * 100)
                        : 0;

                    return [
                        'name' => $cat->name,
                        'used' => $usedPct,
                        'free' => 100 - $usedPct,
                    ];
                });

            return [
                'overall_utilization' => $overallUtilization,
                'available' => (int) $stats->available,
                'in_use' => $inUse,
                'repair' => (int) $stats->repair,
                'damaged' => (int) $stats->damaged,
                'chart' => $chart,
            ];
        });

        return response()->json($result);
    }

    public function qrActivity()
    {
        $cacheKey = 'dashboard:qr-activity';

        $result = Cache::remember($cacheKey, 15, function () {
            $todayScans = AssetScanLog::whereDate('created_at', today())->count();

            $activities = AssetScanLog::with([
                    'asset.item',
                    'user'
                ])
                ->latest()
                ->take(8)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'time' => $log->created_at->format('H:i'),
                        'item' => $log->asset?->item?->name ?? 'Unknown Asset',
                        'code' => $log->asset?->asset_code ?? '---',
                        'action' => ucfirst($log->action),
                        'user' => $log->user?->name ?? '—',
                        'shoot' => $log->notes ?? '—',
                    ];
                });

            return [
                'today_scans' => $todayScans,
                'activities' => $activities,
            ];
        });

        return response()->json($result);
    }

    public function invoices()
    {
        $cacheKey = 'dashboard:invoices';

        $result = Cache::remember($cacheKey, 30, function () {
            $summary = ProductionInvoice::selectRaw("
                SUM(total_amount) as total_billed,
                SUM(paid_amount) as collected,
                SUM(CASE WHEN status IN ('draft','sent','partially_paid') THEN balance_due ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'overdue' THEN balance_due ELSE 0 END) as overdue
            ")->first();

            $invoices = ProductionInvoice::with('shoot')
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($invoice) {
                    return [
                        'id' => $invoice->invoice_number,
                        'invoice_id' => $invoice->id,
                        'client' => $invoice->shoot?->client_name ?? 'Unknown Client',
                        'amount' => $invoice->total_amount,
                        'paid_amount' => $invoice->paid_amount,
                        'balance_due' => $invoice->balance_due,
                        'status' => $invoice->status,
                    ];
                });

            return [
                'summary' => [
                    'total_billed' => (float) ($summary->total_billed ?? 0),
                    'collected' => (float) ($summary->collected ?? 0),
                    'pending' => (float) ($summary->pending ?? 0),
                    'overdue' => (float) ($summary->overdue ?? 0),
                ],
                'invoices' => $invoices,
            ];
        });

        return response()->json($result);
    }

    public function crewOperations()
    {
        $cacheKey = 'dashboard:crew-operations';

        $result = Cache::remember($cacheKey, 30, function () {
            $crew = CrewMember::whereHas('shoots')
                ->with(['shoots' => function ($query) {
                    $query->latest()->limit(1);
                }])
                ->take(10)
                ->get()
                ->map(function ($member) {
                    $latestShoot = $member->shoots->first();

                    return [
                        'id' => $member->id,
                        'name' => $member->name,
                        'role' => $member->designation ?? 'Crew Member',
                        'shoot' => $latestShoot?->title ?? 'Unassigned',
                        'status' => $latestShoot?->pivot?->status ?? 'inactive',
                    ];
                });

            return ['crew' => $crew];
        });

        return response()->json($result);
    }
}
