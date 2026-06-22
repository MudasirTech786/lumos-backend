<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shoot;
use App\Models\CrewMember;
use App\Models\InventoryAsset;
use App\Models\AssetScanLog;
use App\Models\ProductionInvoice;
use App\Models\InventoryCategory;
use App\Services\FinanceService;



class DashboardController extends Controller
{
    public function kpis()
    {
        $activeProductions = Shoot::whereIn('status', [
            'planning',
            'active'
        ])->count();

        $crewOnSet = CrewMember::where('status', 'active')->count();

        $assetsDeployed = InventoryAsset::where('status', 'in_use')->count();

        $totalAssets = InventoryAsset::count();

        $qrScansToday = AssetScanLog::whereDate(
            'created_at',
            today()
        )->count();

        // TEMPORARY
        // Replace later with actual finance module
        $revenueMTD = Shoot::sum('client_invoice_amount');

        // TEMPORARY
        // Replace later with AlertService
        $openAlerts = 0;

        return response()->json([
            'active_productions' => $activeProductions,

            'crew_on_set' => $crewOnSet,

            'assets_deployed' => $assetsDeployed,
            'total_assets' => $totalAssets,

            'qr_scans_today' => $qrScansToday,

            'revenue_mtd' => $revenueMTD,

            'open_alerts' => $openAlerts,
        ]);
    }

    public function productions()
    {
        $productions = Shoot::with([
            'crewMembers',
            'assetAllocations',
            'expenses'
        ])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($shoot) {

                $budget = $shoot->client_budget ?? 0;

                $spent = $shoot->expenses->sum('amount');

                return [
                    'id' => $shoot->id,

                    'title' => $shoot->title,

                    'client' => $shoot->client_name,

                    'location' => $shoot->location,

                    'status' => $shoot->status,

                    'crew' => $shoot->crewMembers->count(),

                    'assets' => $shoot->assetAllocations->count(),

                    'budget' => $budget,

                    'spent' => $spent,
                ];
            });

        return response()->json($productions);
    }

    public function alerts()
    {
        $alerts = [];

        $shoots = Shoot::with('crewMembers')->get();

        foreach ($shoots as $shoot) {

            if (!$shoot->location) {

                $alerts[] = [
                    'type' => 'production',
                    'severity' => 'high',
                    'title' => $shoot->title,
                    'detail' => 'Missing location',
                    'shoot_id' => $shoot->id,
                ];
            }

            if ($shoot->crewMembers->count() === 0) {

                $alerts[] = [
                    'type' => 'production',
                    'severity' => 'high',
                    'title' => $shoot->title,
                    'detail' => 'No crew assigned',
                    'shoot_id' => $shoot->id,
                ];
            }

            if (!$shoot->start_datetime) {

                $alerts[] = [
                    'type' => 'production',
                    'severity' => 'medium',
                    'title' => $shoot->title,
                    'detail' => 'No schedule added',
                    'shoot_id' => $shoot->id,
                ];
            }
        }

        return response()->json([
            'count' => count($alerts),
            'high_priority' => collect($alerts)
                ->where('severity', 'high')
                ->count(),
            'alerts' => array_slice($alerts, 0, 10),
        ]);
    }

    public function financeTrend()
    {
        $months = collect();

        for ($i = 5; $i >= 0; $i--) {

            $date = now()->subMonths($i);

            $shoots = Shoot::with([
                'crewMembers',
                'logistics',
                'expenses',
                'inventoryUsages.item'
            ])
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->get();

            $revenue = $shoots->sum(
                'client_invoice_amount'
            );

            $cost = 0;

            foreach ($shoots as $shoot) {

                $cost += FinanceService::calculateShootCost(
                    $shoot
                );
            }

            $months->push([

                'm' => $date->format('M'),

                // Lakhs
                'rev' => round(
                    $revenue / 100000,
                    1
                ),

                'exp' => round(
                    $cost / 100000,
                    1
                ),

                'pro' => round(
                    ($revenue - $cost)
                        / 100000,
                    1
                ),
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | SUMMARY TOTALS
    |--------------------------------------------------------------------------
    */

        $allShoots = Shoot::with([
            'crewMembers',
            'logistics',
            'expenses',
            'inventoryUsages.item'
        ])->get();

        $totalRevenue =
            $allShoots->sum(
                'client_invoice_amount'
            );

        $totalCost = 0;

        foreach ($allShoots as $shoot) {

            $totalCost +=
                FinanceService::calculateShootCost(
                    $shoot
                );
        }

        return response()->json([

            'chart' => $months,

            'summary' => [

                'revenue' =>
                $totalRevenue,

                'expenses' =>
                $totalCost,

                'profit' =>
                $totalRevenue
                    -
                    $totalCost,
            ],
        ]);
    }

    public function assetUtilization()
    {
        $totalAssets =
            InventoryAsset::count();

        $available =
            InventoryAsset::where(
                'status',
                'available'
            )->count();

        $inUse =
            InventoryAsset::where(
                'status',
                'in_use'
            )->count();

        $repair =
            InventoryAsset::where(
                'status',
                'under_repair'
            )->count();

        $damaged =
            InventoryAsset::where(
                'status',
                'damaged'
            )->count();

        $overallUtilization =
            $totalAssets > 0
            ? round(
                ($inUse / $totalAssets) * 100
            )
            : 0;

        $categories =
            InventoryCategory::with([
                'items.assets'
            ])->get();

        $chart = $categories->map(
            function ($category) {

                $total = 0;
                $used = 0;

                foreach (
                    $category->items
                    as $item
                ) {

                    $total +=
                        $item->assets
                        ->count();

                    $used +=
                        $item->assets
                        ->where(
                            'status',
                            'in_use'
                        )
                        ->count();
                }

                $usedPct =
                    $total > 0
                    ? round(
                        ($used / $total)
                            * 100
                    )
                    : 0;

                return [

                    'name' =>
                    $category->name,

                    'used' =>
                    $usedPct,

                    'free' =>
                    100 - $usedPct,
                ];
            }
        );

        return response()->json([

            'overall_utilization' =>
            $overallUtilization,

            'available' =>
            $available,

            'in_use' =>
            $inUse,

            'repair' =>
            $repair,

            'damaged' =>
            $damaged,

            'chart' =>
            $chart,
        ]);
    }

    public function qrActivity()
    {
        $todayScans =
            AssetScanLog::whereDate(
                'created_at',
                today()
            )->count();

        $activities =
            AssetScanLog::with([
                'asset.item',
                'user'
            ])
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($log) {

                return [

                    'id' => $log->id,

                    'time' =>
                    $log->created_at
                        ->format('H:i'),

                    'item' =>
                    $log->asset?->item?->name
                        ?? 'Unknown Asset',

                    'code' =>
                    $log->asset?->asset_code
                        ?? '---',

                    'action' =>
                    ucfirst($log->action),

                    'user' =>
                    $log->user?->name
                        ?? '—',

                    'shoot' =>
                    $log->notes
                        ?? '—',
                ];
            });

        return response()->json([

            'today_scans' =>
            $todayScans,

            'activities' =>
            $activities,
        ]);
    }

    public function invoices()
    {
        $invoices = ProductionInvoice::with('shoot')
            ->latest()
            ->take(10)
            ->get();

        $totalBilled =
            ProductionInvoice::sum(
                'total_amount'
            );

        $collected =
            ProductionInvoice::sum(
                'paid_amount'
            );

        $pending =
            ProductionInvoice::whereIn(
                'status',
                [
                    'draft',
                    'sent',
                    'partially_paid'
                ]
            )
            ->sum(
                'balance_due'
            );

        $overdue =
            ProductionInvoice::where(
                'status',
                'overdue'
            )
            ->sum(
                'balance_due'
            );

        return response()->json([

            'summary' => [

                'total_billed' =>
                $totalBilled,

                'collected' =>
                $collected,

                'pending' =>
                $pending,

                'overdue' =>
                $overdue,
            ],

            'invoices' =>

            $invoices->map(
                function ($invoice) {

                    return [

                        'id' =>
                        $invoice->invoice_number,

                        'invoice_id' =>
                        $invoice->id,

                        'client' =>
                        $invoice->shoot?->client_name
                            ?? 'Unknown Client',

                        'amount' =>
                        $invoice->total_amount,

                        'paid_amount' =>
                        $invoice->paid_amount,

                        'balance_due' =>
                        $invoice->balance_due,

                        'status' =>
                        $invoice->status,
                    ];
                }
            ),
        ]);
    }

    public function crewOperations()
    {
        $crew = CrewMember::with([
            'shoots' => function ($query) {
                $query->latest();
            }
        ])
            ->whereHas('shoots')
            ->take(10)
            ->get()
            ->map(function ($member) {

                $latestShoot =
                    $member->shoots->first();

                return [

                    'id' => $member->id,

                    'name' => $member->name,

                    'role' =>
                    $member->designation
                        ?? 'Crew Member',

                    'shoot' =>
                    $latestShoot?->title
                        ?? 'Unassigned',

                    'status' =>
                    $latestShoot?->pivot?->status
                        ?? 'inactive',
                ];
            });

        return response()->json([
            'crew' => $crew
        ]);
    }
}
