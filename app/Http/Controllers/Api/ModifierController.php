<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Modifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModifierController extends Controller
{
    /**
     * Get all modifiers.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $modifiers = Modifier::orderBy('name')->get(['id', 'name', 'price_change']);

        return response()->json([
            'data' => $modifiers,
        ]);
    }

    /**
     * Store a newly created modifier.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_change' => 'required|numeric',
        ]);

        $modifier = Modifier::create($validated);

        return response()->json([
            'data' => $modifier,
            'message' => 'เพิ่มตัวเลือกเพิ่มเติมสำเร็จ',
        ], 201);
    }

    /**
     * Display the specified modifier.
     *
     * @param Modifier $modifier
     * @return JsonResponse
     */
    public function show(Modifier $modifier): JsonResponse
    {
        return response()->json([
            'data' => $modifier,
        ]);
    }

    /**
     * Update the specified modifier.
     *
     * @param Request $request
     * @param Modifier $modifier
     * @return JsonResponse
     */
    public function update(Request $request, Modifier $modifier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price_change' => 'sometimes|numeric',
        ]);

        $modifier->update($validated);

        return response()->json([
            'data' => $modifier,
            'message' => 'แก้ไขตัวเลือกเพิ่มเติมสำเร็จ',
        ]);
    }

    /**
     * Remove the specified modifier.
     *
     * @param Modifier $modifier
     * @return JsonResponse
     */
    public function destroy(Modifier $modifier): JsonResponse
    {
        $modifier->delete();

        return response()->json([
            'message' => 'ลบตัวเลือกเพิ่มเติมสำเร็จ',
        ]);
    }
}
