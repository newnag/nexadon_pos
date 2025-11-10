<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Table;
use App\Models\User;

uses()->group('payment');

beforeEach(function () {
    // Create roles
    $this->adminRole = Role::create(['name' => 'Admin']);
    $this->cashierRole = Role::create(['name' => 'Cashier']);
    $this->waiterRole = Role::create(['name' => 'Waiter']);

    // Create users
    $this->cashier = User::factory()->create([
        'role_id' => $this->cashierRole->id,
    ]);

    $this->waiter = User::factory()->create([
        'role_id' => $this->waiterRole->id,
    ]);

    // Create table
    $this->table = Table::factory()->occupied()->create();

    // Create category and menu items
    $this->category = Category::create(['name' => 'Food']);
    
    $this->menuItem1 = MenuItem::factory()->create([
        'name' => 'Burger',
        'price' => 150.00,
        'category_id' => $this->category->id,
    ]);

    $this->menuItem2 = MenuItem::factory()->create([
        'name' => 'Fries',
        'price' => 80.00,
        'category_id' => $this->category->id,
    ]);

    // Create an order with items
    $this->order = Order::create([
        'table_id' => $this->table->id,
        'user_id' => $this->waiter->id,
        'status' => 'pending',
        'total_amount' => 380.00, // (150 * 2) + (80 * 1)
    ]);

    // Create order items
    OrderItem::create([
        'order_id' => $this->order->id,
        'menu_item_id' => $this->menuItem1->id,
        'quantity' => 2,
    ]);

    OrderItem::create([
        'order_id' => $this->order->id,
        'menu_item_id' => $this->menuItem2->id,
        'quantity' => 1,
    ]);
});

describe('Processing a Payment', function () {
    test('it can process a full payment for an order', function () {
        // Ensure table is occupied before payment
        expect($this->table->fresh()->status)->toBe('occupied');
        expect($this->order->status)->toBe('pending');
        expect($this->order->payment)->toBeNull();

        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Payment processed successfully.',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'payment_method',
                    'amount',
                    'order' => [
                        'id',
                        'table_number',
                        'status',
                        'total_amount',
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Assert payment record was created
        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => '380.00',
        ]);

        // Assert order status was updated to completed
        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => 'completed',
        ]);

        // Assert table status was updated to available
        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'status' => 'available',
        ]);

        // Verify with model refresh
        expect($this->order->fresh()->status)->toBe('completed');
        expect($this->table->fresh()->status)->toBe('available');
        expect($this->order->fresh()->payment)->not->toBeNull();
    });

    test('it can process payment with credit card', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Credit Card',
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'payment_method' => 'Credit Card',
        ]);
    });

    test('it can process payment with QR Payment', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'QR Payment',
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'payment_method' => 'QR Payment',
        ]);
    });

    test('it fails to process payment if the order does not exist', function () {
        $paymentData = [
            'order_id' => 99999, // Non-existent order
            'payment_method' => 'Cash',
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);

        // Ensure no payment was created
        $this->assertDatabaseCount('payments', 0);
    });

    test('it fails if the payment amount is less than the total amount', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => 300.00, // Less than order total (380.00)
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Payment amount does not match order total.',
                'expected' => '380.00',
                'received' => '300.00',
            ]);

        // Ensure no payment was created
        $this->assertDatabaseCount('payments', 0);

        // Ensure order status was not changed
        expect($this->order->fresh()->status)->toBe('pending');
    });

    test('it fails if the payment amount is more than the total amount', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => 500.00, // More than order total (380.00)
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Payment amount does not match order total.',
            ]);

        // Ensure no payment was created
        $this->assertDatabaseCount('payments', 0);
    });

    test('it fails to process payment if order is already paid', function () {
        // First payment
        Payment::create([
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => 380.00,
        ]);

        // Attempt second payment
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Credit Card',
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'This order has already been paid.',
            ]);

        // Ensure only one payment exists
        $this->assertDatabaseCount('payments', 1);
    });

    test('it fails to process payment for a cancelled order', function () {
        $this->order->update(['status' => 'cancelled']);

        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot process payment for a cancelled order.',
            ]);

        // Ensure no payment was created
        $this->assertDatabaseCount('payments', 0);
    });

    test('it fails with invalid payment method', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Bitcoin', // Invalid payment method
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method']);
    });

    test('it fails with missing required fields', function () {
        $paymentData = [
            // Missing all fields
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id', 'payment_method', 'amount']);
    });

    test('it fails with negative amount', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => -100.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    });

    test('unauthenticated user cannot process payment', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => 380.00,
        ];

        $response = $this->postJson('/api/payments', $paymentData);

        $response->assertStatus(401);

        // Ensure no payment was created
        $this->assertDatabaseCount('payments', 0);
    });

    test('waiter can process payment', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
        ]);
    });

    test('payment response includes correct order information', function () {
        $paymentData = [
            'order_id' => $this->order->id,
            'payment_method' => 'Cash',
            'amount' => 380.00,
        ];

        $response = $this->actingAs($this->cashier, 'sanctum')
            ->postJson('/api/payments', $paymentData);

        $response->assertStatus(201)
            ->assertJsonPath('data.order.id', $this->order->id)
            ->assertJsonPath('data.order.table_number', (string)$this->table->table_number)
            ->assertJsonPath('data.order.status', 'completed')
            ->assertJsonPath('data.order.total_amount', '380.00')
            ->assertJsonPath('data.payment_method', 'Cash')
            ->assertJsonPath('data.amount', '380.00');
    });
});
