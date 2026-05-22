<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\InventoryCategory;

class InventoryCategoryController extends Controller
{
    /* ====================================================== */
    /* INDEX */
    /* ====================================================== */

    public function index()
    {
        return InventoryCategory::latest()->get();
    }

    /* ====================================================== */
    /* STORE */
    /* ====================================================== */

    public function store(Request $request)
    {
        $validated = $request->validate([

            'name' =>
                'required|string|max:255',

            'description' =>
                'nullable|string',

        ]);

        $validated['slug'] =
            Str::slug(
                $validated['name']
            );

        $category =
            InventoryCategory::create(
                $validated
            );

        return response()->json([
            'message' =>
                'Category created successfully',

            'data' =>
                $category,
        ]);
    }

    /* ====================================================== */
    /* SHOW */
    /* ====================================================== */

    public function show(
        InventoryCategory $category
    ) {
        return response()->json(
            $category
        );
    }

    /* ====================================================== */
    /* UPDATE */
    /* ====================================================== */

    public function update(
        Request $request,
        InventoryCategory $category
    ) {

        $validated = $request->validate([

            'name' =>
                'required|string|max:255',

            'description' =>
                'nullable|string',

        ]);

        $validated['slug'] =
            Str::slug(
                $validated['name']
            );

        $category->update(
            $validated
        );

        return response()->json([
            'message' =>
                'Category updated successfully',

            'data' =>
                $category,
        ]);
    }

    /* ====================================================== */
    /* DESTROY */
    /* ====================================================== */

    public function destroy(
        InventoryCategory $category
    ) {

        $category->delete();

        return response()->json([
            'message' =>
                'Category deleted successfully'
        ]);
    }
}