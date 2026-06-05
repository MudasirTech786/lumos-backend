<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WorkspaceAppController;
use App\Http\Controllers\Api\CrewController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\ShootController;
use App\Http\Controllers\Api\ShootLogisticController;
use App\Http\Controllers\Api\Inventory\InventoryCategoryController;
use App\Http\Controllers\Api\Inventory\InventoryItemController;
use App\Http\Controllers\Api\Inventory\InventoryMovementController;
use App\Http\Controllers\Api\Inventory\InventoryUsageController;
use App\Http\Controllers\Api\ShootInventoryController;
use App\Http\Controllers\Api\Inventory\DamageReportController;
use App\Http\Controllers\Api\Inventory\RepairController;
use App\Http\Controllers\Api\Inventory\InspectionController;
use App\Http\Controllers\Api\Inventory\WriteOffController;
use App\Http\Controllers\Api\ShootExpenseController;
use App\Http\Controllers\Api\ShootFinanceController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ProductionInvoiceController;

Route::get('/user', function (Request $request) {

    return $request->user();
})->middleware('auth:sanctum');


// =======================================
// AUTH ROUTES
// =======================================

Route::post(
    '/register',
    [AuthController::class, 'register']
);

Route::post(
    '/login',
    [AuthController::class, 'login']
);


// =======================================
// PROTECTED ROUTES
// =======================================

Route::middleware('auth:sanctum')->group(function () {

    // =======================================
    // AUTH
    // =======================================

    Route::post(
        '/logout',
        [AuthController::class, 'logout']
    );

    Route::get(
        '/me',
        [AuthController::class, 'me']
    );


    // =======================================
    // USERS
    // =======================================

    Route::apiResource(
        'users',
        UserController::class
    )->middleware([
        'permission:users.view'
    ]);

    Route::get(
        '/roles-list',
        [UserController::class, 'roles']
    )->middleware([
        'permission:roles.view'
    ]);


    // =======================================
    // ROLES
    // =======================================

    Route::apiResource(
        'roles',
        RoleController::class
    )->middleware([
        'permission:roles.view'
    ]);


    // =======================================
    // PERMISSIONS
    // =======================================

    Route::apiResource(
        'permissions',
        PermissionController::class
    )->middleware([
        'permission:permissions.view'
    ]);


    // =======================================
    // PROFILE
    // =======================================

    Route::post(
        '/profile',
        [ProfileController::class, 'update']
    );


    // =======================================
    // WORKSPACE APPS
    // =======================================

    Route::get(
        '/workspace-apps',
        [WorkspaceAppController::class, 'index']
    )->middleware([
        'permission:workspaces.view'
    ]);

    Route::post(
        '/workspace-apps',
        [WorkspaceAppController::class, 'store']
    )->middleware([
        'permission:workspaces.create'
    ]);

    Route::put(
        '/workspace-apps/{id}',
        [WorkspaceAppController::class, 'update']
    )->middleware([
        'permission:workspaces.edit'
    ]);

    Route::delete(
        '/workspace-apps/{id}',
        [WorkspaceAppController::class, 'destroy']
    )->middleware([
        'permission:workspaces.delete'
    ]);


    // =======================================
    // CREW
    // =======================================

    Route::apiResource(
        'crew',
        CrewController::class
    )->middleware([
        'permission:crew.view'
    ]);


    // =======================================
    // EMPLOYEES
    // =======================================

    Route::apiResource(
        'employees',
        EmployeeController::class
    )->middleware([
        'permission:employees.view'
    ]);


    // =======================================
    // LEAVES
    // =======================================

    Route::apiResource(
        'leaves',
        LeaveController::class
    )->middleware([
        'permission:leaves.view'
    ])->parameters([
        'leaves' => 'leave'
    ]);

    // =======================================
    // SHOOTS
    // =======================================

    Route::apiResource(
        'shoots',
        ShootController::class
    )->middleware([
        'permission:shoots.view'
    ])->parameters([
        'shoots' => 'shoot'
    ]);

    // For Shoot Status Update
    Route::patch(
        'shoots/{shoot}/status',
        [ShootController::class, 'updateStatus']
    )->middleware([
        'permission:shoots.edit'
    ]);

    // For Shoot fianance details
    Route::get(
        '/shoots/{shoot}/finance-details',
        [ShootFinanceController::class, 'details']
    );

    // For Assign Crew to Shoot
    Route::post(
        'shoots/{shoot}/assign-crew',
        [ShootController::class, 'assignCrew']
    )->middleware([
        'permission:shoots.edit'
    ]);

    // For Remove Crew from Shoot
    Route::delete(
        'shoots/{shoot}/crew/{crew}',
        [ShootController::class, 'removeCrew']
    )->middleware([
        'permission:shoots.edit'
    ]);

    // For Shoot Logistics
    Route::post(
        'shoots/{shoot}/logistics',
        [ShootLogisticController::class, 'save']
    )->middleware([
        'permission:shoots.edit'
    ]);

    // For Deleting Shoot Logistics
    Route::delete(
        '/logistics/{logistic}',
        [ShootLogisticController::class, 'destroy']
    );

    // For Updating Shoot Logistic Status
    Route::patch(
        '/logistics/{logistic}/status',
        [ShootLogisticController::class, 'updateStatus']
    );

    // For Shoot Calendar View
    Route::get(
        'shoots-calendar',
        [ShootController::class, 'calendar']
    )->middleware([
        'permission:shoots.view'
    ]);

    // =====================================
    // SHOOT INVENTORY
    // =====================================

    Route::get(

        'shoots/{shoot}/inventory',

        [ShootInventoryController::class, 'index']

    )->middleware([
        'permission:shoots.view'
    ]);

    Route::post(

        'shoots/{shoot}/inventory',

        [ShootInventoryController::class, 'store']

    )->middleware([
        'permission:shoots.edit'
    ]);

    Route::post(

        'shoot-inventory/{usage}/checkout',

        [ShootInventoryController::class, 'checkout']

    )->middleware([
        'permission:shoots.edit'
    ]);

    Route::post(

        'shoot-inventory/{usage}/return',

        [ShootInventoryController::class, 'processReturn']

    )->middleware([
        'permission:shoots.edit'
    ]);

    Route::delete(

        'shoot-inventory/{usage}',

        [ShootInventoryController::class, 'destroy']

    )->middleware([
        'permission:shoots.edit'
    ]);



    // =======================================
    // INVENTORY ROUTES
    // =======================================

    Route::prefix('inventory')->group(function () {

        // =======================================
        // CATEGORIES
        // =======================================

        Route::apiResource(
            'categories',
            InventoryCategoryController::class
        )->middleware([
            'permission:inventory.view'
        ]);

        // =======================================
        // ITEMS
        // =======================================

        Route::apiResource(
            'items',
            InventoryItemController::class
        )->middleware([
            'permission:inventory.view'
        ]);

        // =======================================
        // STOCK UPDATE
        // =======================================

        Route::post(
            'items/{item}/stock',
            [InventoryItemController::class, 'updateStock']
        )->middleware([
            'permission:inventory.edit'
        ]);

        // =======================================
        // MOVEMENTS
        // =======================================

        Route::get(
            'movements',
            [InventoryMovementController::class, 'index']
        )->middleware([
            'permission:inventory.view'
        ]);

        // =======================================
        // DAMAGES
        // =======================================

        Route::post(
            'items/{item}/damage',
            [InventoryItemController::class, 'markDamaged']
        )->middleware([
            'permission:inventory.edit'
        ]);

        // =====================================
        // DAMAGE REPORTS
        // =====================================

        Route::get(

            'damage-reports',

            [DamageReportController::class, 'index']

        );

        Route::post(

            'damage-reports',

            [DamageReportController::class, 'store']

        );

        Route::patch(

            'damage-reports/{damageReport}/status',

            [DamageReportController::class, 'updateStatus']

        );

        // =====================================
        // REPAIRS
        // =====================================

        Route::get(

            'repairs',

            [RepairController::class, 'index']

        );

        Route::post(

            'repairs',

            [RepairController::class, 'store']

        );

        Route::patch(

            'repairs/{repair}/status',

            [RepairController::class, 'updateStatus']

        );
        // =====================================
        // INSPECTIONS
        // =====================================

        Route::get(

            'inspections',

            [InspectionController::class, 'index']

        );

        Route::post(

            'inspections',

            [InspectionController::class, 'store']

        );

        // =====================================
        // WRITE OFFS
        // =====================================

        Route::get(

            'write-offs',

            [WriteOffController::class, 'index']

        );

        Route::post(

            'write-offs',

            [WriteOffController::class, 'store']

        );
    });

    // =======================================
    // INVENTORY Usage
    // =======================================
    Route::prefix('inventory/usage')
        ->middleware('auth:sanctum')
        ->group(function () {

            Route::get('/', [
                InventoryUsageController::class,
                'index'
            ]);

            Route::post('/', [
                InventoryUsageController::class,
                'store'
            ]);

            Route::get('/{usage}', [
                InventoryUsageController::class,
                'show'
            ]);

            Route::put('/{usage}', [
                InventoryUsageController::class,
                'update'
            ]);

            Route::post('/{usage}/checkout', [
                InventoryUsageController::class,
                'checkout'
            ]);

            Route::post('/{usage}/return', [
                InventoryUsageController::class,
                'processReturn'
            ]);

            Route::delete('/{usage}', [
                InventoryUsageController::class,
                'destroy'
            ]);
        });


    Route::apiResource(
        'shoot-expenses',
        ShootExpenseController::class
    )->middleware([
        'permission:finance.view'
    ]);

    Route::get('/shoots/{shoot}/expenses', [ShootExpenseController::class, 'byShoot']);

    Route::get(
        '/shoots/{shoot}/finance',
        [ShootFinanceController::class, 'show']
    );



    // Payroll Routes
    Route::prefix(
        'payrolls'
    )->group(function () {

        Route::get(
            '/',
            [PayrollController::class, 'index']
        );

        Route::get(
            '/{payroll}',
            [PayrollController::class, 'show']
        );

        Route::post(
            '/generate-crew',
            [
                PayrollController::class,
                'generateCrewPayroll'
            ]
        );

        Route::post(

            '/generate-employee',

            [
                PayrollController::class,
                'generateEmployeePayroll'
            ]

        );

        Route::post(

            '/{payroll}/approve',

            [
                PayrollController::class,
                'approve'
            ]

        );

        Route::post(

            '/{payroll}/mark-paid',

            [
                PayrollController::class,
                'markPaid'
            ]

        );
        Route::get(
            '/{payroll}/items',
            [PayrollController::class, 'items']
        );

        Route::get(
            '/finance/reports',
            [PayrollController::class, 'reports']
        );
    });

    // Invoice Routes
    Route::prefix('production-invoices')->group(function () {

        Route::get(
            '/',
            [ProductionInvoiceController::class, 'index']
        );

        Route::post(
            '/',
            [ProductionInvoiceController::class, 'store']
        );

        Route::get(
            '/{invoice}',
            [ProductionInvoiceController::class, 'show']
        );

        Route::put(
            '/{invoice}',
            [ProductionInvoiceController::class, 'update']
        );

        Route::delete(
            '/{invoice}',
            [ProductionInvoiceController::class, 'destroy']
        );

        Route::get(
            '/shoot/{shoot}',
            [ProductionInvoiceController::class, 'byShoot']
        );

        Route::get(
            '/{invoice}/items',
            [ProductionInvoiceController::class, 'items']
        );

        Route::post(
            '/{invoice}/payments',
            [ProductionInvoiceController::class, 'addPayment']
        );

        Route::get(
            '/{invoice}/payments',
            [ProductionInvoiceController::class, 'payments']
        );

        Route::get(
            '/shoots/{shoot}/invoice-summary',
            [ProductionInvoiceController::class, 'invoiceSummary']
        );
    });
});
