<?php

namespace App\Http\Controllers\Api;

use App\Models\ShootExpense;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShootExpenseResource;
use App\Http\Requests\StoreShootExpenseRequest;
use App\Http\Requests\UpdateShootExpenseRequest;
use Illuminate\Support\Facades\Auth;

class ShootExpenseController extends Controller
{
    public function index()
    {
        return ShootExpenseResource::collection(

            ShootExpense::latest()
                ->paginate(20)
        );
    }

    public function store(
        StoreShootExpenseRequest $request
    ) {
        $data = $request->validated();

        if ($request->hasFile('receipt')) {

            $data['receipt'] = $request
                ->file('receipt')
                ->store(
                    'shoot-expenses',
                    'public'
                );
        }

        $data['created_by'] =  Auth::user()->id;

        $expense = ShootExpense::create(
            $data
        );

        return new ShootExpenseResource(
            $expense
        );
    }

    public function show(
        ShootExpense $shootExpense
    ) {
        return new ShootExpenseResource(
            $shootExpense
        );
    }

    public function update(
        UpdateShootExpenseRequest $request,
        ShootExpense $shootExpense
    ) {
        $data = $request->validated();

        if ($request->hasFile('receipt')) {

            $data['receipt'] = $request
                ->file('receipt')
                ->store(
                    'shoot-expenses',
                    'public'
                );
        }

        $shootExpense->update(
            $data
        );

        return new ShootExpenseResource(
            $shootExpense
        );
    }

    public function byShoot($shootId)
    {
        return ShootExpenseResource::collection(

            ShootExpense::where(
                'shoot_id',
                $shootId
            )
                ->latest()
                ->get()
        );
    }

    public function destroy(
        ShootExpense $shootExpense
    ) {
        $shootExpense->delete();

        return response()->json([

            'message' =>
            'Expense deleted successfully'
        ]);
    }
}
