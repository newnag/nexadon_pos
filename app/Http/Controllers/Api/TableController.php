<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TableResource;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of all tables
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $tables = Table::with(['orders' => function ($query) {
            $query->whereNotIn('status', ['completed', 'cancelled'])
                  ->latest();
        }])
        ->orderBy('table_number')
        ->get();

        return TableResource::collection($tables);
    }

    /**
     * Display the specified table
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Table $table)
    {
        $table->load(['orders' => function ($query) {
            $query->whereNotIn('status', ['completed', 'cancelled'])
                  ->with(['orderItems.menuItem', 'user'])
                  ->latest();
        }]);

        return new TableResource($table);
    }

    /**
     * Store a newly created table
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:255|unique:tables,table_number',
            'status' => 'sometimes|in:available,occupied,reserved',
        ]);

        // Default status is available if not provided
        $validated['status'] = $validated['status'] ?? 'available';

        $table = Table::create($validated);

        return new TableResource($table);
    }

    /**
     * Update the specified table status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'table_number' => 'sometimes|string|max:255|unique:tables,table_number,' . $table->id,
            'status' => 'sometimes|in:available,occupied,reserved',
        ]);

        $table->update($validated);

        return new TableResource($table);
    }

    /**
     * Remove the specified table
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Table $table)
    {
        // Check if table has active orders
        $activeOrders = $table->orders()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        if ($activeOrders > 0) {
            return response()->json([
                'message' => 'ไม่สามารถลบโต๊ะที่มีออเดอร์อยู่ได้',
            ], 422);
        }

        $table->delete();

        return response()->json([
            'message' => 'ลบโต๊ะสำเร็จ',
        ]);
    }
}
