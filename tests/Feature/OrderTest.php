<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Order Management API Tests
 * 
 * Tests the order creation and management process including:
 * - Creating new orders with items and modifiers
 * - Validating table availability
 * - Calculating total amounts correctly
 * - Fetching active orders and order details
 */

describe('Creating a New Order', function () {
    beforeEach(function () {
        // Create roles
        $this->waiterRole = Role::firstOrCreate(['name' => 'Waiter']);
        $this->adminRole = Role::firstOrCreate(['name' => 'Admin']);

        // Create users
        $this->waiter = User::factory()->create([
            'role_id' => $this->waiterRole->id,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        // Create table
        $this->table = Table::factory()->create([
            'table_number' => 5,
            'status' => 'available',
        ]);

        // Create category
        $this->category = Category::create(['name' => 'Main Course']);

        // Create menu items
        $this->menuItem1 = MenuItem::factory()->create([
            'name' => 'Pad Thai',
            'price' => 120.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ]);

        $this->menuItem2 = MenuItem::factory()->create([
            'name' => 'Green Curry',
            'price' => 150.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ]);

        // Create modifiers
        $this->modifier1 = Modifier::factory()->create([
            'name' => 'Extra Spicy',
            'price_change' => 10.00,
        ]);

        $this->modifier2 = Modifier::factory()->create([
            'name' => 'Extra Portion',
            'price_change' => 20.00,
        ]);
    });

    test('an authenticated user can create a new order', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 2,
                    'modifier_ids' => [$this->modifier1->id],
                    'notes' => 'No cilantro',
                ],
                [
                    'menu_item_id' => $this->menuItem2->id,
                    'quantity' => 1,
                    'modifier_ids' => [],
                    'notes' => null,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'status',
                    'total_amount',
                    'table' => ['id', 'table_number', 'status'],
                    'user' => ['id', 'name', 'role'],
                    'order_items',
                ],
            ])
            ->assertJson([
                'message' => 'Order created successfully.',
            ]);

        // Assert order was created in database
        $this->assertDatabaseHas('orders', [
            'table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => 'pending',
        ]);

        // Assert order items were created
        $this->assertDatabaseHas('order_items', [
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('order_items', [
            'menu_item_id' => $this->menuItem2->id,
            'quantity' => 1,
        ]);

        // Assert table status updated to occupied
        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'status' => 'occupied',
        ]);
    });

    test('the total amount is calculated correctly', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id, // 120.00
                    'quantity' => 2,
                    'modifier_ids' => [$this->modifier1->id], // +10.00
                    // Total for this item: (120 + 10) * 2 = 260.00
                ],
                [
                    'menu_item_id' => $this->menuItem2->id, // 150.00
                    'quantity' => 1,
                    'modifier_ids' => [$this->modifier2->id], // +20.00
                    // Total for this item: (150 + 20) * 1 = 170.00
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Expected total: 260.00 + 170.00 = 430.00
        $order = Order::latest()->first();
        expect($order->total_amount)->toBe('430.00');

        $response->assertJsonPath('data.total_amount', '430.00');
    });

    test('order with multiple modifiers calculates correctly', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id, // 120.00
                    'quantity' => 1,
                    'modifier_ids' => [$this->modifier1->id, $this->modifier2->id], // +10.00 + 20.00
                    // Total: (120 + 10 + 20) * 1 = 150.00
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        $order = Order::latest()->first();
        expect($order->total_amount)->toBe('150.00');
    });

    test('modifiers are attached to order items', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                    'modifier_ids' => [$this->modifier1->id, $this->modifier2->id],
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        $orderItem = OrderItem::latest()->first();
        expect($orderItem->modifiers)->toHaveCount(2);
        expect($orderItem->modifiers->pluck('id')->toArray())
            ->toContain($this->modifier1->id, $this->modifier2->id);
    });

    test('it fails to create an order if the table is already occupied', function () {
        // Mark table as occupied
        $this->table->update(['status' => 'occupied']);

        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                    'modifier_ids' => [],
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Table is already occupied. Please select another table.',
            ]);

        // Verify no order was created
        $this->assertDatabaseMissing('orders', [
            'table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
        ]);
    });

    test('it fails to create an order if a menu item does not exist', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => 99999, // Non-existent menu item
                    'quantity' => 1,
                    'modifier_ids' => [],
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(422);
    });

    test('it fails to create an order if menu item is unavailable', function () {
        // Mark menu item as unavailable
        $this->menuItem1->update(['is_available' => false]);

        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                    'modifier_ids' => [],
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => "Menu item 'Pad Thai' is currently unavailable.",
            ]);
    });

    test('it fails to create an order with invalid data', function () {
        // Missing table_id
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', [
                'order_items' => [
                    [
                        'menu_item_id' => $this->menuItem1->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['table_id']);

        // Missing order_items
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_items']);

        // Empty order_items
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'order_items' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_items']);
    });

    test('it fails to create an order with invalid order item data', function () {
        // Missing menu_item_id
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'order_items' => [
                    [
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_items.0.menu_item_id']);

        // Missing quantity
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'order_items' => [
                    [
                        'menu_item_id' => $this->menuItem1->id,
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_items.0.quantity']);

        // Invalid quantity (zero)
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', [
                'table_id' => $this->table->id,
                'order_items' => [
                    [
                        'menu_item_id' => $this->menuItem1->id,
                        'quantity' => 0,
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_items.0.quantity']);
    });

    test('unauthenticated user cannot create an order', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(401);
    });

    test('admin can create an order', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->admin->id,
        ]);
    });
});

describe('Fetching Orders', function () {
    beforeEach(function () {
        // Create role and user
        $this->waiterRole = Role::firstOrCreate(['name' => 'Waiter']);
        $this->waiter = User::factory()->create([
            'role_id' => $this->waiterRole->id,
        ]);

        // Create tables
        $this->table1 = Table::factory()->create(['status' => 'occupied']);
        $this->table2 = Table::factory()->create(['status' => 'occupied']);
        $this->table3 = Table::factory()->create(['status' => 'available']);

        // Create category and menu items
        $this->category = Category::create(['name' => 'Food']);
        $this->menuItem = MenuItem::factory()->create([
            'category_id' => $this->category->id,
        ]);

        // Create orders with different statuses
        // Create older order first
        $this->pendingOrder = Order::create([
            'table_id' => $this->table1->id,
            'user_id' => $this->waiter->id,
            'status' => 'pending',
            'total_amount' => 100.00,
            'created_at' => now()->subMinutes(10),
            'updated_at' => now()->subMinutes(10),
        ]);

        OrderItem::create([
            'order_id' => $this->pendingOrder->id,
            'menu_item_id' => $this->menuItem->id,
            'quantity' => 1,
        ]);

        // Create newer order
        $this->preparingOrder = Order::create([
            'table_id' => $this->table2->id,
            'user_id' => $this->waiter->id,
            'status' => 'preparing',
            'total_amount' => 200.00,
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        OrderItem::create([
            'order_id' => $this->preparingOrder->id,
            'menu_item_id' => $this->menuItem->id,
            'quantity' => 2,
        ]);

        $this->completedOrder = Order::create([
            'table_id' => $this->table3->id,
            'user_id' => $this->waiter->id,
            'status' => 'completed',
            'total_amount' => 150.00,
        ]);

        OrderItem::create([
            'order_id' => $this->completedOrder->id,
            'menu_item_id' => $this->menuItem->id,
            'quantity' => 1,
        ]);

        $this->cancelledOrder = Order::create([
            'table_id' => $this->table3->id,
            'user_id' => $this->waiter->id,
            'status' => 'cancelled',
            'total_amount' => 80.00,
        ]);
    });

    test('it can fetch all active orders', function () {
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/orders/active');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'status',
                        'total_amount',
                        'table' => ['id', 'table_number', 'status'],
                        'user' => ['id', 'name', 'role'],
                        'order_items',
                    ],
                ],
            ]);

        // Should return only pending and preparing orders (not completed or cancelled)
        $data = $response->json('data');
        expect($data)->toHaveCount(2);

        $statuses = collect($data)->pluck('status')->toArray();
        expect($statuses)->toContain('pending', 'preparing');
        expect($statuses)->not->toContain('completed', 'cancelled');
    });

    test('active orders are sorted by creation date descending', function () {
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/orders/active');

        $response->assertStatus(200);

        $data = $response->json('data');
        
        // Should have 2 active orders (pending and preparing)
        expect($data)->toHaveCount(2);
        
        // Verify orders are sorted by created_at descending
        // The first order should have a created_at >= the second order
        $firstCreatedAt = \Carbon\Carbon::parse($data[0]['created_at']);
        $secondCreatedAt = \Carbon\Carbon::parse($data[1]['created_at']);
        
        expect($firstCreatedAt->gte($secondCreatedAt))->toBeTrue();
    });

    test('it can fetch details of a single order', function () {
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/orders/' . $this->pendingOrder->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'total_amount',
                    'table' => ['id', 'table_number', 'status'],
                    'user' => ['id', 'name', 'role'],
                    'order_items' => [
                        '*' => [
                            'id',
                            'quantity',
                            'menu_item',
                            'modifiers',
                        ],
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->pendingOrder->id,
                    'status' => 'pending',
                    'total_amount' => '100.00',
                ],
            ]);
    });

    test('order details include table information', function () {
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/orders/' . $this->pendingOrder->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.table.id', $this->table1->id);
    });

    test('order details include user information with role', function () {
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/orders/' . $this->pendingOrder->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.id', $this->waiter->id)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'role',
                    ],
                ],
            ]);
    });

    test('order details include menu items with category', function () {
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/orders/' . $this->pendingOrder->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.order_items.0.menu_item.id', $this->menuItem->id)
            ->assertJsonStructure([
                'data' => [
                    'order_items' => [
                        '*' => [
                            'menu_item' => [
                                'category',
                            ],
                        ],
                    ],
                ],
            ]);
    });

    test('order details include modifiers for each item', function () {
        // Create order with modifiers
        $modifier = Modifier::factory()->create();
        $order = Order::create([
            'table_id' => $this->table1->id,
            'user_id' => $this->waiter->id,
            'status' => 'pending',
            'total_amount' => 100.00,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $this->menuItem->id,
            'quantity' => 1,
        ]);

        $orderItem->modifiers()->attach($modifier->id);

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/orders/' . $order->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.order_items.0.modifiers.0.id', $modifier->id);
    });

    test('it returns 404 when fetching non-existent order', function () {
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/orders/99999');

        $response->assertStatus(404);
    });

    test('unauthenticated user cannot fetch orders', function () {
        $response = $this->getJson('/api/orders/active');
        $response->assertStatus(401);

        $response = $this->getJson('/api/orders/' . $this->pendingOrder->id);
        $response->assertStatus(401);
    });
});

describe('Updating Orders', function () {
    beforeEach(function () {
        // Create role and user
        $this->waiterRole = Role::firstOrCreate(['name' => 'Waiter']);
        $this->waiter = User::factory()->create([
            'role_id' => $this->waiterRole->id,
        ]);

        // Create table and category
        $this->table = Table::factory()->create(['status' => 'occupied']);
        $this->category = Category::create(['name' => 'Food']);

        // Create menu items
        $this->menuItem1 = MenuItem::factory()->create([
            'name' => 'Item 1',
            'price' => 100.00,
            'category_id' => $this->category->id,
        ]);

        $this->menuItem2 = MenuItem::factory()->create([
            'name' => 'Item 2',
            'price' => 50.00,
            'category_id' => $this->category->id,
        ]);

        // Create existing order
        $this->order = Order::create([
            'table_id' => $this->table->id,
            'user_id' => $this->waiter->id,
            'status' => 'pending',
            'total_amount' => 100.00,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'menu_item_id' => $this->menuItem1->id,
            'quantity' => 1,
        ]);
    });

    test('can add more items to an existing order', function () {
        $existingItem = $this->order->orderItems->first();

        $updateData = [
            'order_items' => [
                [
                    'order_item_id' => $existingItem->id,
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
                [
                    'menu_item_id' => $this->menuItem2->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->putJson('/api/orders/' . $this->order->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order updated successfully.',
            ]);

        // Verify new order item was added
        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'menu_item_id' => $this->menuItem2->id,
            'quantity' => 2,
        ]);

        // Verify total amount was updated (100 + 50*2 = 200)
        $this->order->refresh();
        expect($this->order->total_amount)->toBe('200.00');
    });

    test('cannot modify a completed order', function () {
        $this->order->update(['status' => 'completed']);

        $updateData = [
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem2->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->putJson('/api/orders/' . $this->order->id, $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot modify a completed or cancelled order.',
            ]);
    });

    test('cannot modify a cancelled order', function () {
        $this->order->update(['status' => 'cancelled']);

        $updateData = [
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem2->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->putJson('/api/orders/' . $this->order->id, $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot modify a completed or cancelled order.',
            ]);
    });

    test('cancelling an order frees up the table', function () {
        // Ensure table is occupied initially
        $this->table->update(['status' => 'occupied']);
        $this->order->update(['status' => 'pending']);

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->putJson('/api/orders/' . $this->order->id, [
                'status' => 'cancelled'
            ]);

        $response->assertStatus(200);

        // Verify table is available
        expect($this->table->fresh()->status)->toBe('available');
    });
});
