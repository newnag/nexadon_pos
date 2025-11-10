<?php

use App\Events\NewOrderPlaced;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\Role;
use App\Models\Table;
use App\Models\User;
use Illuminate\Support\Facades\Event;

uses()->group('broadcasting');

beforeEach(function () {
    // Create roles
    $this->waiterRole = Role::create(['name' => 'Waiter']);
    $this->adminRole = Role::create(['name' => 'Admin']);

    // Create users
    $this->waiter = User::factory()->create([
        'role_id' => $this->waiterRole->id,
    ]);

    $this->admin = User::factory()->create([
        'role_id' => $this->adminRole->id,
    ]);

    // Create table
    $this->table = Table::factory()->create(['status' => 'available']);

    // Create category and menu items
    $this->category = Category::create(['name' => 'Beverages']);
    
    $this->menuItem1 = MenuItem::factory()->create([
        'name' => 'Coffee',
        'price' => 60.00,
        'category_id' => $this->category->id,
    ]);

    $this->menuItem2 = MenuItem::factory()->create([
        'name' => 'Tea',
        'price' => 50.00,
        'category_id' => $this->category->id,
    ]);

    // Create modifiers
    $this->modifier1 = Modifier::create([
        'name' => 'Extra Sugar',
        'price_change' => 5.00,
    ]);

    $this->modifier2 = Modifier::create([
        'name' => 'Oat Milk',
        'price_change' => 15.00,
    ]);
});

describe('Event Broadcasting', function () {
    test('the NewOrderPlaced event is broadcasted when an order is created', function () {
        Event::fake();

        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 2,
                    'notes' => 'Hot please',
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Assert that the NewOrderPlaced event was dispatched
        Event::assertDispatched(NewOrderPlaced::class);
    });

    test('the NewOrderPlaced event is dispatched exactly once per order', function () {
        Event::fake();

        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Assert exactly one NewOrderPlaced event was dispatched
        Event::assertDispatched(NewOrderPlaced::class, 1);
    });

    test('the event contains the correct order data', function () {
        Event::fake();

        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 2,
                    'notes' => 'Extra hot',
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Assert event was dispatched with correct order
        Event::assertDispatched(function (NewOrderPlaced $event) {
            return $event->order->table_id === $this->table->id
                && $event->order->user_id === $this->waiter->id
                && $event->order->status === 'pending';
        });
    });

    test('the event includes order items and modifiers', function () {
        Event::fake();

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

        // Assert event was dispatched with order that has items
        Event::assertDispatched(function (NewOrderPlaced $event) {
            $order = $event->order;
            $order->load(['orderItems.modifiers']);

            return $order->orderItems->count() === 1
                && $order->orderItems->first()->menu_item_id === $this->menuItem1->id
                && $order->orderItems->first()->modifiers->count() === 2;
        });
    });

    test('the event broadcasts on the correct channel', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Get the created order
        $order = \App\Models\Order::latest()->first();

        // Create event instance
        $event = new NewOrderPlaced($order);

        // Assert broadcast channel
        $channels = $event->broadcastOn();
        expect($channels)->toHaveCount(1);
        expect($channels[0]->name)->toBe('private-kitchen-channel');
    });

    test('the event broadcasts with the correct event name', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Get the created order
        $order = \App\Models\Order::latest()->first();

        // Create event instance
        $event = new NewOrderPlaced($order);

        // Assert broadcast name
        expect($event->broadcastAs())->toBe('order.placed');
    });

    test('the event broadcasts with correct order data structure', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 2,
                    'notes' => 'No sugar',
                    'modifier_ids' => [$this->modifier1->id],
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Get the created order
        $order = \App\Models\Order::latest()->first();

        // Create event instance
        $event = new NewOrderPlaced($order);

        // Get broadcast data
        $broadcastData = $event->broadcastWith();

        // Assert structure
        expect($broadcastData)->toHaveKeys([
            'order_id',
            'table',
            'waiter',
            'status',
            'total_amount',
            'items',
            'created_at',
        ]);

        expect($broadcastData['order_id'])->toBe($order->id);
        expect($broadcastData['table']['id'])->toBe($this->table->id);
        expect($broadcastData['table']['number'])->toBe((string)$this->table->table_number);
        expect($broadcastData['waiter']['id'])->toBe($this->waiter->id);
        expect($broadcastData['waiter']['name'])->toBe($this->waiter->name);
        expect($broadcastData['status'])->toBe('pending');
        expect($broadcastData['items'])->toHaveCount(1);
    });

    test('broadcast data includes menu item details', function () {
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Get the created order
        $order = \App\Models\Order::latest()->first();

        // Create event instance
        $event = new NewOrderPlaced($order);

        // Get broadcast data
        $broadcastData = $event->broadcastWith();

        // Assert menu item data
        $item = $broadcastData['items'][0];
        expect($item)->toHaveKeys(['id', 'menu_item', 'quantity', 'notes', 'modifiers']);
        expect($item['menu_item']['name'])->toBe('Coffee');
        expect($item['menu_item']['category'])->toBe('Beverages');
        expect($item['quantity'])->toBe(1);
    });

    test('broadcast data includes modifier information', function () {
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

        // Get the created order
        $order = \App\Models\Order::latest()->first();

        // Create event instance
        $event = new NewOrderPlaced($order);

        // Get broadcast data
        $broadcastData = $event->broadcastWith();

        // Assert modifier data
        $modifiers = $broadcastData['items'][0]['modifiers'];
        expect($modifiers)->toHaveCount(2);
        expect($modifiers[0]['name'])->toBeIn(['Extra Sugar', 'Oat Milk']);
        expect($modifiers[1]['name'])->toBeIn(['Extra Sugar', 'Oat Milk']);
    });

    test('the event is not dispatched when order creation fails', function () {
        Event::fake();

        // Try to create order with non-existent menu item
        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => 99999, // Non-existent
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(422);

        // Assert that NO NewOrderPlaced event was dispatched
        Event::assertNotDispatched(NewOrderPlaced::class);
    });

    test('the event is not dispatched when table is occupied', function () {
        Event::fake();

        // Mark table as occupied
        $this->table->update(['status' => 'occupied']);

        $orderData = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData);

        $response->assertStatus(422);

        // Assert that NO NewOrderPlaced event was dispatched
        Event::assertNotDispatched(NewOrderPlaced::class);
    });

    test('multiple orders dispatch multiple events', function () {
        Event::fake();

        // Create first order
        $orderData1 = [
            'table_id' => $this->table->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem1->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $response1 = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData1);

        $response1->assertStatus(201);

        // Create another table for second order
        $table2 = Table::factory()->create(['status' => 'available']);

        // Create second order
        $orderData2 = [
            'table_id' => $table2->id,
            'order_items' => [
                [
                    'menu_item_id' => $this->menuItem2->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response2 = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/orders', $orderData2);

        $response2->assertStatus(201);

        // Assert that NewOrderPlaced event was dispatched twice
        Event::assertDispatched(NewOrderPlaced::class, 2);
    });

    test('admin can create order and event is dispatched', function () {
        Event::fake();

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

        // Assert event was dispatched for admin-created order
        Event::assertDispatched(function (NewOrderPlaced $event) {
            return $event->order->user_id === $this->admin->id;
        });
    });
});
