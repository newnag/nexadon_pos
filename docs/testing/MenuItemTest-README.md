# Menu Item API Test Suite

## Overview
Comprehensive test suite for Menu Item CRUD operations in the POS system, covering authorization, validation, and filtering capabilities.

## Test Coverage Summary

### ✅ **26 Tests Passing** | Total Assertions: 121

---

## Test Suites

### 1. Fetching Menu Items (4 tests - ALL PASSING ✅)

Tests read operations for menu items:

- ✅ **Fetch All Menu Items:** Authenticated user can retrieve list of all menu items with pagination
- ✅ **Fetch Single Menu Item:** Retrieve detailed information for a specific menu item including category and modifiers
- ✅ **Non-existent Item:** Returns 404 when requesting item that doesn't exist
- ✅ **Unauthenticated Access:** Unauthenticated requests return 401 unauthorized

**Endpoints:**
- `GET /api/menu-items` - List all menu items (with optional filters)
- `GET /api/menu-items/{id}` - Get single menu item details

**Authentication:** Required for all read operations

**Response Structure:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Coffee",
      "price": "50.00",
      "is_available": true,
      "category": {
        "id": 1,
        "name": "Beverages"
      },
      "modifiers": [],
      "created_at": "2025-10-31 12:00:00",
      "updated_at": "2025-10-31 12:00:00"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

---

### 2. Creating Menu Items (8 tests - ALL PASSING ✅)

Tests menu item creation with various authorization levels:

- ✅ **Admin Can Create:** Admin role can successfully create menu items (201 status)
- ✅ **Manager Can Create:** Manager role can successfully create menu items
- ✅ **Create with Modifiers:** Admin can create menu items with attached modifiers
- ✅ **Waiter Cannot Create:** Waiter role receives 403 forbidden
- ✅ **Unauthenticated Cannot Create:** Returns 401 unauthorized
- ✅ **Invalid Data Validation:** Returns 422 when required fields (name, price, category_id) are missing
- ✅ **Invalid Price:** Returns 422 for negative or non-numeric prices
- ✅ **Non-existent Category:** Returns 422 when category_id doesn't exist

**Endpoint:** `POST /api/menu-items`

**Authorization:** Admin, Manager only (Waiter: 403)

**Required Fields:**
- `name` (string)
- `price` (numeric, positive)
- `category_id` (exists in categories table)

**Optional Fields:**
- `is_available` (boolean, default: true)
- `modifier_ids` (array of modifier IDs)

**Success Response (201):**
```json
{
  "message": "Menu item created successfully.",
  "data": {
    "id": 1,
    "name": "Pad Thai",
    "price": "120.00",
    "is_available": true,
    "category": {...},
    "modifiers": [...],
    "created_at": "...",
    "updated_at": "..."
  }
}
```

---

### 3. Updating Menu Items (6 tests - ALL PASSING ✅)

Tests menu item update operations:

- ✅ **Admin Can Update:** Admin can update all menu item fields (200 status)
- ✅ **Manager Can Update:** Manager can update menu items
- ✅ **Update Availability Only:** Can update just the is_available field
- ✅ **Waiter Cannot Update:** Waiter role receives 403 forbidden
- ✅ **Unauthenticated Cannot Update:** Returns 401 unauthorized
- ✅ **Invalid Update Data:** Returns 422 for empty name or invalid price

**Endpoints:**
- `PUT /api/menu-items/{id}` - Full update
- `PATCH /api/menu-items/{id}` - Partial update

**Authorization:** Admin, Manager only (Waiter: 403)

**Success Response (200):**
```json
{
  "message": "Menu item updated successfully.",
  "data": {
    "id": 1,
    "name": "Premium Ice Cream",
    "price": "80.00",
    ...
  }
}
```

---

### 4. Deleting Menu Items (5 tests - ALL PASSING ✅)

Tests menu item deletion:

- ✅ **Admin Can Delete:** Admin can successfully delete menu items (200 status)
- ✅ **Manager Can Delete:** Manager can delete menu items
- ✅ **Waiter Cannot Delete:** Waiter role receives 403 forbidden
- ✅ **Unauthenticated Cannot Delete:** Returns 401 unauthorized
- ✅ **Non-existent Item:** Returns 404 when trying to delete non-existent item

**Endpoint:** `DELETE /api/menu-items/{id}`

**Authorization:** Admin, Manager only (Waiter: 403)

**Success Response (200):**
```json
{
  "message": "Menu item deleted successfully."
}
```

**Note:** Controller returns 200 instead of 204 (includes message body)

---

### 5. Filtering and Search (3 tests - ALL PASSING ✅)

Tests advanced filtering and search capabilities:

- ✅ **Filter by Category:** Filter menu items by category_id query parameter
- ✅ **Filter by Availability:** Filter items by is_available status (true/false)
- ✅ **Search by Name:** Search menu items using partial name matching

**Query Parameters:**
- `category_id` (integer) - Filter by category
- `is_available` (boolean) - Filter by availability status
- `search` (string) - Search by item name (partial match)
- `sort_by` (string) - Sort field (default: 'name')
- `sort_order` (string) - Sort direction: 'asc' or 'desc' (default: 'asc')
- `per_page` (integer) - Items per page (default: 15)

**Example Requests:**
```bash
# Filter by category
GET /api/menu-items?category_id=1

# Filter available items
GET /api/menu-items?is_available=1

# Search by name
GET /api/menu-items?search=Coffee

# Combined filters
GET /api/menu-items?category_id=1&is_available=1&search=Ice&sort_by=price&sort_order=desc&per_page=20
```

---

## Authorization Matrix

| Action | Endpoint | Admin | Manager | Cashier | Waiter | Unauthenticated |
|--------|----------|-------|---------|---------|--------|-----------------|
| List Items | GET /api/menu-items | ✅ | ✅ | ✅ | ✅ | ❌ (401) |
| View Item | GET /api/menu-items/{id} | ✅ | ✅ | ✅ | ✅ | ❌ (401) |
| Create Item | POST /api/menu-items | ✅ | ✅ | ❌ (403) | ❌ (403) | ❌ (401) |
| Update Item | PUT /api/menu-items/{id} | ✅ | ✅ | ❌ (403) | ❌ (403) | ❌ (401) |
| Delete Item | DELETE /api/menu-items/{id} | ✅ | ✅ | ❌ (403) | ❌ (403) | ❌ (401) |

---

## Database Schema

### menu_items Table
```php
Schema::create('menu_items', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('price', 10, 2);
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->boolean('is_available')->default(true);
    $table->timestamps();
});
```

### Relationships
- **BelongsTo:** Category (category_id)
- **BelongsToMany:** Modifiers (via menu_item_modifiers pivot table)

---

## Running the Tests

### Run Menu Item Tests Only
```bash
php artisan test tests/Feature/MenuItemTest.php
# or
vendor/bin/pest tests/Feature/MenuItemTest.php
```

### Run Specific Test Suite
```bash
php artisan test --filter="Fetching Menu Items"
php artisan test --filter="Creating Menu Items"
```

### Run All Feature Tests
```bash
php artisan test tests/Feature/
```

---

## Test Environment

- **Framework:** Laravel 11.37 with Pest PHP
- **Database:** SQLite (`:memory:` for tests via `RefreshDatabase` trait)
- **Authentication:** Laravel Sanctum (session-based)
- **Authorization:** CheckRole middleware
- **Models:** MenuItem, Category, Modifier, User, Role
- **Resources:** MenuItemResource (transforms API responses)

---

## Key Testing Patterns

### 1. Role-Based Authorization Testing
```php
test('an admin can create a new menu item', function () {
    $admin = User::factory()->create([
        'role_id' => Role::where('name', 'Admin')->first()->id,
    ]);
    
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/menu-items', $data);
        
    $response->assertStatus(201);
});
```

### 2. Validation Testing
```php
test('it fails to create with invalid data', function () {
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/menu-items', ['name' => '']); // Missing price
        
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['price']);
});
```

### 3. Database Assertions
```php
$this->assertDatabaseHas('menu_items', [
    'name' => 'Pad Thai',
    'price' => 120.00,
]);

$this->assertDatabaseMissing('menu_items', [
    'name' => 'Deleted Item',
]);
```

### 4. Relationship Testing
```php
test('can create menu item with modifiers', function () {
    $data = [
        'name' => 'Coffee',
        'price' => 50.00,
        'modifier_ids' => [$modifier1->id, $modifier2->id],
    ];
    
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/menu-items', $data);
        
    $menuItem = MenuItem::find($response->json('data.id'));
    expect($menuItem->modifiers)->toHaveCount(2);
});
```

---

## Validation Rules

### Create/Update Menu Item

**StoreMenuItemRequest / UpdateMenuItemRequest:**
```php
[
    'name' => 'required|string|max:255',
    'price' => 'required|numeric|min:0',
    'category_id' => 'required|exists:categories,id',
    'is_available' => 'nullable|boolean',
    'modifier_ids' => 'nullable|array',
    'modifier_ids.*' => 'exists:modifiers,id',
]
```

**Error Responses (422):**
```json
{
  "message": "The name field is required. (and 1 more error)",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price field is required."]
  }
}
```

---

## API Response Structures

### List Response (Paginated)
```json
{
  "data": [...],
  "links": {
    "first": "http://localhost/api/menu-items?page=1",
    "last": "http://localhost/api/menu-items?page=3",
    "prev": null,
    "next": "http://localhost/api/menu-items?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 45
  }
}
```

### Single Item Response
```json
{
  "data": {
    "id": 1,
    "name": "Coffee",
    "price": "50.00",
    "is_available": true,
    "category": {
      "id": 1,
      "name": "Beverages"
    },
    "modifiers": [
      {
        "id": 1,
        "name": "Extra Shot",
        "price_change": "15.00"
      }
    ],
    "created_at": "2025-10-31 12:00:00",
    "updated_at": "2025-10-31 12:00:00"
  }
}
```

### Error Response (403 Forbidden)
```json
{
  "message": "Unauthorized. This action requires one of the following roles: Admin, Manager",
  "required_roles": ["Admin", "Manager"],
  "your_role": "Waiter"
}
```

---

## Test Statistics

- **Total Tests:** 26
- **Passing:** 26 (100%)
- **Failed:** 0 (0%)
- **Total Assertions:** 121
- **Average Duration:** ~0.05s per test
- **Total Suite Duration:** ~1.4s

---

## Coverage Areas

### ✅ Fully Covered
- CRUD operations (Create, Read, Update, Delete)
- Role-based authorization (Admin, Manager, Waiter)
- Authentication requirements
- Validation errors (missing fields, invalid data)
- Relationship handling (Category, Modifiers)
- Filtering and search functionality
- HTTP status codes (200, 201, 401, 403, 404, 422)

### 🔄 Additional Coverage Opportunities
- Pagination edge cases (first page, last page, out of range)
- Sorting by different fields (price, created_at, etc.)
- Concurrent updates
- Soft deletes (if implemented)
- Image uploads (if added to menu items)
- Bulk operations
- Performance testing with large datasets

---

## Related Documentation

- [Authentication Tests](./AuthTest-README.md)
- [API Routes](../routes/api.php)
- [MenuItem Model](../app/Models/MenuItem.php)
- [MenuItemController](../app/Http/Controllers/Api/MenuItemController.php)
- [CheckRole Middleware](../app/Http/Middleware/CheckRole.php)

---

## Maintenance

**Last Updated:** October 31, 2025  
**Laravel Version:** 11.37  
**Pest Version:** Latest  
**Test File:** `tests/Feature/MenuItemTest.php`

For questions or issues with these tests, refer to:
- [Pest Documentation](https://pestphp.com)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Laravel Authorization Documentation](https://laravel.com/docs/authorization)
