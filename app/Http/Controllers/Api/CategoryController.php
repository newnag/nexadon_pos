<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = Category::withCount('menuItems')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Store a newly created category.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'data' => $category,
            'message' => 'เพิ่มหมวดหมู่สำเร็จ',
        ], 201);
    }

    /**
     * Display the specified category.
     *
     * @param Category $category
     * @return JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        $category->loadCount('menuItems');

        return response()->json([
            'data' => $category,
        ]);
    }

    /**
     * Update the specified category.
     *
     * @param Request $request
     * @param Category $category
     * @return JsonResponse
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);

        return response()->json([
            'data' => $category,
            'message' => 'แก้ไขหมวดหมู่สำเร็จ',
        ]);
    }

    /**
     * Remove the specified category.
     *
     * @param Category $category
     * @return JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        // Check if category has menu items
        $menuItemsCount = $category->menuItems()->count();

        if ($menuItemsCount > 0) {
            return response()->json([
                'message' => "ไม่สามารถลบหมวดหมู่ที่มีรายการอาหาร {$menuItemsCount} รายการอยู่ได้",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'ลบหมวดหมู่สำเร็จ',
        ]);
    }
}
