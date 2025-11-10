<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Menu Item API Tests
 * 
 * Tests the CRUD operations for menu items including:
 * - Fetching all menu items and single item
 * - Creating menu items (Admin/Manager only)
 * - Updating menu items (Admin/Manager only)
 * - Deleting menu items (Admin/Manager only)
 * - Authorization checks for different roles
 */

describe('Fetching Menu Items', function () {
    beforeEach(function () {
        // Create roles
        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->waiterRole = Role::create(['name' => 'Waiter']);

        // Create test user
        $this->user = User::factory()->create([
            'role_id' => $this->waiterRole->id,
        ]);

        // Create category
        $this->category = Category::create([
            'name' => 'Beverages',
        ]);

        // Create menu items
        $this->menuItem1 = MenuItem::create([
            'name' => 'Coffee',
            'price' => 50.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ]);

        $this->menuItem2 = MenuItem::create([
            'name' => 'Tea',
            'price' => 40.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ]);
    });

    test('it can fetch a list of all menu items', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/menu-items');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'is_available',
                        'category' => [
                            'id',
                            'name',
                        ],
                        'modifiers',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data');
    });

    test('it can fetch a single menu item', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/menu-items/' . $this->menuItem1->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'price',
                    'is_available',
                    'category' => [
                        'id',
                        'name',
                    ],
                    'modifiers',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $this->menuItem1->id,
                    'name' => 'Coffee',
                    'price' => '50.00',
                ],
            ]);
    });

    test('it returns 404 when fetching non-existent menu item', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/menu-items/99999');

        $response->assertStatus(404);
    });

    test('unauthenticated user cannot fetch menu items', function () {
        $response = $this->getJson('/api/menu-items');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });
});

describe('Creating Menu Items', function () {
    beforeEach(function () {
        // Create roles
        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->managerRole = Role::create(['name' => 'Manager']);
        $this->waiterRole = Role::create(['name' => 'Waiter']);

        // Create users with different roles
        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $this->managerRole->id,
        ]);

        $this->waiter = User::factory()->create([
            'role_id' => $this->waiterRole->id,
        ]);

        // Create category
        $this->category = Category::create([
            'name' => 'Main Course',
        ]);

        // Create modifier
        $this->modifier = Modifier::create([
            'name' => 'Extra Spicy',
            'price_change' => 10.00,
        ]);
    });

    test('an admin can create a new menu item', function () {
        $menuItemData = [
            'name' => 'Pad Thai',
            'price' => 120.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/menu-items', $menuItemData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'price',
                    'is_available',
                    'category' => [
                        'id',
                        'name',
                    ],
                    'modifiers',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'message' => 'Menu item created successfully.',
                'data' => [
                    'name' => 'Pad Thai',
                    'price' => '120.00',
                ],
            ]);

        $this->assertDatabaseHas('menu_items', [
            'name' => 'Pad Thai',
            'price' => 120.00,
            'category_id' => $this->category->id,
        ]);
    });

    test('a manager can create a new menu item', function () {
        $menuItemData = [
            'name' => 'Green Curry',
            'price' => 150.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ];

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/menu-items', $menuItemData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Menu item created successfully.',
            ]);

        $this->assertDatabaseHas('menu_items', [
            'name' => 'Green Curry',
        ]);
    });

    test('an admin can create a menu item with modifiers', function () {
        $menuItemData = [
            'name' => 'Tom Yum Soup',
            'price' => 100.00,
            'category_id' => $this->category->id,
            'is_available' => true,
            'modifier_ids' => [$this->modifier->id],
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/menu-items', $menuItemData);

        $response->assertStatus(201);

        // Verify modifier is attached
        $menuItem = MenuItem::where('name', 'Tom Yum Soup')->first();
        expect($menuItem->modifiers)->toHaveCount(1);
        expect($menuItem->modifiers->first()->id)->toBe($this->modifier->id);
    });

    test('a waiter cannot create a new menu item', function () {
        $menuItemData = [
            'name' => 'Fried Rice',
            'price' => 80.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->postJson('/api/menu-items', $menuItemData);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Unauthorized. This action requires one of the following roles: Admin, Manager',
            ]);

        $this->assertDatabaseMissing('menu_items', [
            'name' => 'Fried Rice',
        ]);
    });

    test('an unauthenticated user cannot create a menu item', function () {
        $menuItemData = [
            'name' => 'Spring Rolls',
            'price' => 60.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ];

        $response = $this->postJson('/api/menu-items', $menuItemData);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);

        $this->assertDatabaseMissing('menu_items', [
            'name' => 'Spring Rolls',
        ]);
    });

    test('it fails to create a menu item with invalid data', function () {
        // Missing name
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/menu-items', [
                'price' => 100.00,
                'category_id' => $this->category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Missing price
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/menu-items', [
                'name' => 'Test Item',
                'category_id' => $this->category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);

        // Missing category_id
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/menu-items', [
                'name' => 'Test Item',
                'price' => 100.00,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    });

    test('it fails to create a menu item with invalid price', function () {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/menu-items', [
                'name' => 'Test Item',
                'price' => -50.00, // Negative price
                'category_id' => $this->category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    });

    test('it fails to create a menu item with non-existent category', function () {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/menu-items', [
                'name' => 'Test Item',
                'price' => 100.00,
                'category_id' => 99999, // Non-existent category
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    });
});

describe('Updating Menu Items', function () {
    beforeEach(function () {
        // Create roles
        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->managerRole = Role::create(['name' => 'Manager']);
        $this->waiterRole = Role::create(['name' => 'Waiter']);

        // Create users
        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $this->managerRole->id,
        ]);

        $this->waiter = User::factory()->create([
            'role_id' => $this->waiterRole->id,
        ]);

        // Create category
        $this->category = Category::create([
            'name' => 'Desserts',
        ]);

        // Create menu item
        $this->menuItem = MenuItem::create([
            'name' => 'Ice Cream',
            'price' => 60.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ]);
    });

    test('an admin can update a menu item', function () {
        $updateData = [
            'name' => 'Premium Ice Cream',
            'price' => 80.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/menu-items/' . $this->menuItem->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Menu item updated successfully.',
                'data' => [
                    'name' => 'Premium Ice Cream',
                    'price' => '80.00',
                ],
            ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $this->menuItem->id,
            'name' => 'Premium Ice Cream',
            'price' => 80.00,
        ]);
    });

    test('a manager can update a menu item', function () {
        $updateData = [
            'name' => 'Chocolate Ice Cream',
            'price' => 70.00,
            'category_id' => $this->category->id,
            'is_available' => false,
        ];

        $response = $this->actingAs($this->manager, 'sanctum')
            ->putJson('/api/menu-items/' . $this->menuItem->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Menu item updated successfully.',
            ]);

        $this->assertDatabaseHas('menu_items', [
            'id' => $this->menuItem->id,
            'name' => 'Chocolate Ice Cream',
            'is_available' => false,
        ]);
    });

    test('an admin can update menu item availability only', function () {
        $updateData = [
            'name' => $this->menuItem->name,
            'price' => $this->menuItem->price,
            'category_id' => $this->menuItem->category_id,
            'is_available' => false,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/menu-items/' . $this->menuItem->id, $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('menu_items', [
            'id' => $this->menuItem->id,
            'is_available' => false,
        ]);
    });

    test('a waiter cannot update a menu item', function () {
        $updateData = [
            'name' => 'Updated by Waiter',
            'price' => 100.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ];

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->putJson('/api/menu-items/' . $this->menuItem->id, $updateData);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Unauthorized. This action requires one of the following roles: Admin, Manager',
            ]);

        // Verify data was not updated
        $this->assertDatabaseHas('menu_items', [
            'id' => $this->menuItem->id,
            'name' => 'Ice Cream', // Original name
            'price' => 60.00, // Original price
        ]);
    });

    test('an unauthenticated user cannot update a menu item', function () {
        $updateData = [
            'name' => 'Unauthorized Update',
            'price' => 100.00,
            'category_id' => $this->category->id,
        ];

        $response = $this->putJson('/api/menu-items/' . $this->menuItem->id, $updateData);

        $response->assertStatus(401);
    });

    test('it fails to update a menu item with invalid data', function () {
        // Empty name
        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/menu-items/' . $this->menuItem->id, [
                'name' => '',
                'price' => 100.00,
                'category_id' => $this->category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);

        // Invalid price
        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson('/api/menu-items/' . $this->menuItem->id, [
                'name' => 'Test',
                'price' => 'invalid',
                'category_id' => $this->category->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    });
});

describe('Deleting Menu Items', function () {
    beforeEach(function () {
        // Create roles
        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->managerRole = Role::create(['name' => 'Manager']);
        $this->waiterRole = Role::create(['name' => 'Waiter']);

        // Create users
        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $this->manager = User::factory()->create([
            'role_id' => $this->managerRole->id,
        ]);

        $this->waiter = User::factory()->create([
            'role_id' => $this->waiterRole->id,
        ]);

        // Create category
        $this->category = Category::create([
            'name' => 'Snacks',
        ]);

        // Create menu item
        $this->menuItem = MenuItem::create([
            'name' => 'French Fries',
            'price' => 45.00,
            'category_id' => $this->category->id,
            'is_available' => true,
        ]);
    });

    test('an admin can delete a menu item', function () {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson('/api/menu-items/' . $this->menuItem->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Menu item deleted successfully.',
            ]);

        $this->assertDatabaseMissing('menu_items', [
            'id' => $this->menuItem->id,
        ]);
    });

    test('a manager can delete a menu item', function () {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->deleteJson('/api/menu-items/' . $this->menuItem->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Menu item deleted successfully.',
            ]);

        $this->assertDatabaseMissing('menu_items', [
            'id' => $this->menuItem->id,
        ]);
    });

    test('a waiter cannot delete a menu item', function () {
        $response = $this->actingAs($this->waiter, 'sanctum')
            ->deleteJson('/api/menu-items/' . $this->menuItem->id);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'message' => 'Unauthorized. This action requires one of the following roles: Admin, Manager',
            ]);

        // Verify item was not deleted
        $this->assertDatabaseHas('menu_items', [
            'id' => $this->menuItem->id,
            'name' => 'French Fries',
        ]);
    });

    test('an unauthenticated user cannot delete a menu item', function () {
        $response = $this->deleteJson('/api/menu-items/' . $this->menuItem->id);

        $response->assertStatus(401);

        // Verify item was not deleted
        $this->assertDatabaseHas('menu_items', [
            'id' => $this->menuItem->id,
        ]);
    });

    test('it returns 404 when deleting non-existent menu item', function () {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson('/api/menu-items/99999');

        $response->assertStatus(404);
    });
});

describe('Menu Items with Filters and Search', function () {
    beforeEach(function () {
        // Create role and user
        $this->role = Role::create(['name' => 'Cashier']);
        $this->user = User::factory()->create([
            'role_id' => $this->role->id,
        ]);

        // Create categories
        $this->beverageCategory = Category::create(['name' => 'Beverages']);
        $this->foodCategory = Category::create(['name' => 'Food']);

        // Create menu items
        MenuItem::create([
            'name' => 'Coffee',
            'price' => 50.00,
            'category_id' => $this->beverageCategory->id,
            'is_available' => true,
        ]);

        MenuItem::create([
            'name' => 'Tea',
            'price' => 40.00,
            'category_id' => $this->beverageCategory->id,
            'is_available' => true,
        ]);

        MenuItem::create([
            'name' => 'Pizza',
            'price' => 200.00,
            'category_id' => $this->foodCategory->id,
            'is_available' => false,
        ]);
    });

    test('it can filter menu items by category', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/menu-items?category_id=' . $this->beverageCategory->id);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    });

    test('it can filter menu items by availability', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/menu-items?is_available=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/menu-items?is_available=0');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    });

    test('it can search menu items by name', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/menu-items?search=Coffee');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Coffee');
    });
});
