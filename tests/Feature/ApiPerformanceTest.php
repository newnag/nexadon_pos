<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create authenticated user
    $this->user = User::factory()->create(['role_id' => 1]); // Admin
    $this->actingAs($this->user, 'web');
});

describe('API Performance Tests', function () {
    
    test('GET /api/categories - performance with empty data', function () {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/categories');
        
        $executionTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        
        $response->assertStatus(200);
        
        expect($executionTime)->toBeLessThan(100) // Should be under 100ms
            ->and($response->json('data'))->toBeArray();
        
        echo "\n✓ GET /api/categories (empty): {$executionTime}ms";
    });

    test('GET /api/categories - performance with 50 categories', function () {
        // Create 50 categories
        Category::factory()->count(50)->create();
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/categories');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200)
            ->assertJsonCount(50, 'data');
        
        expect($executionTime)->toBeLessThan(200) // Should be under 200ms
            ->and($response->json('data'))->toHaveCount(50);
        
        echo "\n✓ GET /api/categories (50 items): {$executionTime}ms";
    });

    test('GET /api/modifiers - performance with empty data', function () {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/modifiers');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        expect($executionTime)->toBeLessThan(100);
        
        echo "\n✓ GET /api/modifiers (empty): {$executionTime}ms";
    });

    test('GET /api/modifiers - performance with 100 modifiers', function () {
        // Create 100 modifiers
        Modifier::factory()->count(100)->create();
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/modifiers');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200)
            ->assertJsonCount(100, 'data');
        
        expect($executionTime)->toBeLessThan(200);
        
        echo "\n✓ GET /api/modifiers (100 items): {$executionTime}ms";
    });

    test('GET /api/menu-items - performance with empty data', function () {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/menu-items');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        expect($executionTime)->toBeLessThan(100);
        
        echo "\n✓ GET /api/menu-items (empty): {$executionTime}ms";
    });

    test('GET /api/menu-items - performance with 50 items and relationships', function () {
        // Create categories, menu items, and modifiers
        $categories = Category::factory()->count(10)->create();
        $modifiers = Modifier::factory()->count(20)->create();
        
        // Create 50 menu items with relationships
        MenuItem::factory()->count(50)->create()->each(function ($menuItem) use ($modifiers) {
            // Attach 2-3 random modifiers to each menu item
            $menuItem->modifiers()->attach($modifiers->random(rand(2, 3)));
        });
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/menu-items?per_page=100');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        $count = count($response->json('data', []));
        
        expect($executionTime)->toBeLessThan(500) // Allow more time for relationships
            ->and($count)->toBe(50);
        
        echo "\n✓ GET /api/menu-items (50 items + relationships): {$executionTime}ms";
    });

    test('GET /api/menu-items - performance with filters and search', function () {
        $category = Category::factory()->create();
        MenuItem::factory()->count(50)->create([
            'category_id' => $category->id,
            'is_available' => true,
        ]);
        MenuItem::factory()->count(30)->create([
            'is_available' => false,
        ]);
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/menu-items?category_id=' . $category->id . '&available=1&per_page=100');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        $count = count($response->json('data', []));
        
        expect($executionTime)->toBeLessThan(300)
            ->and($count)->toBe(50);
        
        echo "\n✓ GET /api/menu-items (with filters): {$executionTime}ms";
    });

    test('GET /api/tables - performance with empty data', function () {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/tables');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        expect($executionTime)->toBeLessThan(100);
        
        echo "\n✓ GET /api/tables (empty): {$executionTime}ms";
    });

    test('GET /api/tables - performance with 50 tables', function () {
        Table::factory()->count(50)->create();
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/tables');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200)
            ->assertJsonCount(50, 'data');
        
        expect($executionTime)->toBeLessThan(200);
        
        echo "\n✓ GET /api/tables (50 items): {$executionTime}ms";
    });

    test('GET /api/orders/active - performance with empty data', function () {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/orders/active');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200);
        
        expect($executionTime)->toBeLessThan(100);
        
        echo "\n✓ GET /api/orders/active (empty): {$executionTime}ms";
    });

    test('GET /api/orders/active - performance with 20 orders and full relationships', function () {
        // Create necessary data
        $categories = Category::factory()->count(5)->create();
        $menuItems = MenuItem::factory()->count(20)->create();
        $modifiers = Modifier::factory()->count(10)->create();
        $tables = Table::factory()->count(10)->create();
        
        // Create 20 active orders with items and modifiers
        for ($i = 0; $i < 20; $i++) {
            $order = Order::factory()->create([
                'table_id' => $tables->random()->id,
                'user_id' => $this->user->id,
                'status' => 'pending',
            ]);
            
            // Add 3-5 order items per order
            for ($j = 0; $j < rand(3, 5); $j++) {
                $menuItem = $menuItems->random();
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => rand(1, 3),
                    'notes' => null,
                ]);
                
                // Add 1-2 modifiers per order item
                $orderItem->modifiers()->attach($modifiers->random(rand(1, 2)));
            }
        }
        
        // Create some completed orders (should not be included)
        foreach ($tables->random(5) as $table) {
            Order::factory()->create([
                'table_id' => $table->id,
                'user_id' => $this->user->id,
                'status' => 'completed',
            ]);
        }
        
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/orders/active');
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200)
            ->assertJsonCount(20, 'data');
        
        expect($executionTime)->toBeLessThan(1000); // Allow 1 second for complex query
        
        echo "\n✓ GET /api/orders/active (20 orders + relationships): {$executionTime}ms";
    });

    test('GET /api/orders/{id} - performance for single order with all relationships', function () {
        // Create order with full relationships
        $category = Category::factory()->create();
        $menuItems = MenuItem::factory()->count(5)->create(['category_id' => $category->id]);
        $modifiers = Modifier::factory()->count(5)->create();
        $table = Table::factory()->create();
        
        $order = Order::factory()->create([
            'table_id' => $table->id,
            'user_id' => $this->user->id,
        ]);
        
        // Add 5 order items with modifiers
        foreach ($menuItems as $menuItem) {
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => rand(1, 3),
                'notes' => null,
            ]);
            $orderItem->modifiers()->attach($modifiers->random(2));
        }
        
        $startTime = microtime(true);
        
        $response = $this->getJson("/api/orders/{$order->id}");
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'table',
                    'user',
                    'order_items',
                ]
            ]);
        
        expect($executionTime)->toBeLessThan(300);
        
        echo "\n✓ GET /api/orders/{id} (with relationships): {$executionTime}ms";
    });

    test('Performance summary - compare all list endpoints', function () {
        // Setup data for all endpoints
        Category::factory()->count(20)->create();
        Modifier::factory()->count(30)->create();
        $tables = Table::factory()->count(20)->create();
        
        $menuItems = MenuItem::factory()->count(30)->create();
        $menuItems->each(function ($item) {
            $item->modifiers()->attach(Modifier::inRandomOrder()->limit(2)->pluck('id'));
        });
        
        for ($i = 0; $i < 10; $i++) {
            $order = Order::factory()->create([
                'table_id' => $tables->random()->id,
                'user_id' => $this->user->id,
                'status' => 'pending',
            ]);
            
            // Create order items manually without factory
            for ($j = 0; $j < 3; $j++) {
                $menuItem = $menuItems->random();
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => rand(1, 3),
                    'notes' => null,
                ]);
            }
        }
        
        $endpoints = [
            'GET /api/categories' => '/api/categories',
            'GET /api/modifiers' => '/api/modifiers',
            'GET /api/menu-items' => '/api/menu-items',
            'GET /api/tables' => '/api/tables',
            'GET /api/orders/active' => '/api/orders/active',
        ];
        
        $results = [];
        
        echo "\n\n📊 API Performance Summary:";
        echo "\n" . str_repeat('=', 70);
        
        foreach ($endpoints as $name => $url) {
            $startTime = microtime(true);
            $response = $this->getJson($url);
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            $response->assertStatus(200);
            $count = count($response->json('data', []));
            
            $results[$name] = $executionTime;
            
            echo sprintf("\n%-30s | %6.2fms | %3d items", $name, $executionTime, $count);
        }
        
        echo "\n" . str_repeat('=', 70);
        
        // Find slowest endpoint
        $slowest = array_keys($results, max($results))[0];
        $fastest = array_keys($results, min($results))[0];
        
        echo "\n⚡ Fastest: {$fastest} (" . number_format($results[$fastest], 2) . "ms)";
        echo "\n🐌 Slowest: {$slowest} (" . number_format($results[$slowest], 2) . "ms)";
        echo "\n";
        
        // All endpoints should be reasonably fast
        foreach ($results as $endpoint => $time) {
            expect($time)->toBeLessThan(1000); // All under 1 second
        }
    });
});
