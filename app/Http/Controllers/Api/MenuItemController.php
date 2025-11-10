<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuItemController extends Controller
{
    /**
     * Display a listing of menu items.
     * Includes category and modifiers information.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MenuItem::with(['category', 'modifiers']);

        // Filter by category if provided
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by availability if provided
        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Sort by field if provided
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $menuItems = $query->paginate($perPage);

        return MenuItemResource::collection($menuItems);
    }

    /**
     * Store a newly created menu item.
     * Only Admin and Manager can access.
     */
    public function store(StoreMenuItemRequest $request): JsonResponse
    {
        $menuItem = MenuItem::create($request->validated());

        // Attach modifiers if provided
        if ($request->has('modifier_ids')) {
            $menuItem->modifiers()->sync($request->modifier_ids);
        }

        // Load relationships for response
        $menuItem->load(['category', 'modifiers']);

        return response()->json([
            'message' => 'Menu item created successfully.',
            'data' => new MenuItemResource($menuItem),
        ], 201);
    }

    /**
     * Display the specified menu item.
     */
    public function show(MenuItem $menuItem): MenuItemResource
    {
        $menuItem->load(['category', 'modifiers']);
        
        return new MenuItemResource($menuItem);
    }

    /**
     * Update the specified menu item.
     * Only Admin and Manager can access.
     */
    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): JsonResponse
    {
        $menuItem->update($request->validated());

        // Update modifiers if provided
        if ($request->has('modifier_ids')) {
            $menuItem->modifiers()->sync($request->modifier_ids);
        }

        // Load relationships for response
        $menuItem->load(['category', 'modifiers']);

        return response()->json([
            'message' => 'Menu item updated successfully.',
            'data' => new MenuItemResource($menuItem),
        ], 200);
    }

    /**
     * Remove the specified menu item.
     * Only Admin and Manager can access.
     */
    public function destroy(MenuItem $menuItem): JsonResponse
    {
        $menuItem->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully.',
        ], 200);
    }
}
