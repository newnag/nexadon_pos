# 🧪 Testing Documentation

This directory contains all testing-related documentation for the Nexadon POS project.

## 📄 Available Documentation

### Test Guides

- **[AUTHENTICATION_TEST.md](./AUTHENTICATION_TEST.md)**
  - Authentication test scenarios
  - Test cases for login, logout, and user profile
  - Edge cases and error handling

- **[AuthTest-README.md](./AuthTest-README.md)**
  - Comprehensive authentication testing guide
  - Detailed test coverage
  - Test execution instructions

- **[MenuItemTest-README.md](./MenuItemTest-README.md)**
  - Menu item CRUD testing
  - Role-based access testing
  - Filter and search test cases

### Performance Testing

- **[PERFORMANCE_COMPARISON.md](./PERFORMANCE_COMPARISON.md)**
  - API performance benchmarks
  - Before/after index optimization comparison
  - Database query performance analysis
  - Response time metrics

---

## 🧪 Test Coverage

### Current Test Statistics

| Test Suite | Tests | Assertions | Status |
|-------------|-------|------------|--------|
| Authentication | 20 | 56 | ✅ 100% |
| Menu Items | 26 | 78 | ✅ 100% |
| Orders | 23 | 137 | ✅ 100% |
| Payments | 14 | 66 | ✅ 100% |
| Broadcasting | 13 | 50 | ✅ 100% |
| Performance | 13 | 47 | ✅ 100% |
| **Total** | **109** | **434** | **✅ 100%** |

---

## 🚀 Running Tests

### All Tests

```bash
# Run all tests with SQLite (faster)
vendor/bin/pest

# Run all tests with MySQL (production-like)
vendor/bin/pest --configuration=phpunit.mysql.xml
```

### Specific Test Suites

```bash
# Authentication tests
vendor/bin/pest tests/Feature/AuthTest.php

# Menu item tests
vendor/bin/pest tests/Feature/MenuItemTest.php

# Order tests
vendor/bin/pest tests/Feature/OrderTest.php

# Payment tests
vendor/bin/pest tests/Feature/PaymentTest.php

# Broadcasting tests
vendor/bin/pest tests/Feature/OrderBroadcastingTest.php

# Performance tests
vendor/bin/pest tests/Feature/ApiPerformanceTest.php
```

### Filtered Tests

```bash
# Run only login tests
vendor/bin/pest --filter "login"

# Run only admin tests
vendor/bin/pest --filter "admin"

# Run only performance tests
vendor/bin/pest --filter "performance"
```

---

## 📊 Performance Metrics

### API Response Times (MySQL)

| Endpoint | Empty | With Data | Status |
|----------|-------|-----------|--------|
| GET /api/categories | 14.70ms | 2.78ms (50 items) | ✅ |
| GET /api/modifiers | 2.29ms | 4.98ms (100 items) | ✅ |
| GET /api/menu-items | 6.19ms | 14.15ms (50+rel) | ✅ |
| GET /api/tables | 3.01ms | 6.73ms (50 items) | ✅ |
| GET /api/orders/active | 2.65ms | 23.60ms (20+rel) | ✅ |
| GET /api/orders/{id} | - | 6.36ms (full) | ✅ |

**All endpoints respond within 25ms** ⚡

---

## 🎯 Test Types

### Feature Tests

Test complete features and API endpoints:
- User authentication flow
- CRUD operations
- Business logic validation
- Authorization checks

### Unit Tests

Test individual components:
- Model methods
- Helper functions
- Validation rules

### Performance Tests

Measure API response times:
- Empty data scenarios
- Large dataset scenarios
- Complex relationship queries
- Index optimization validation

### Broadcasting Tests

Test real-time event system:
- Event dispatching
- Channel broadcasting
- Data structure validation

---

## 📝 Writing New Tests

### Test Structure

```php
use function Pest\Laravel\{actingAs, getJson, postJson};

describe('Feature Name', function () {
    beforeEach(function () {
        // Setup code
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    });

    test('it performs expected behavior', function () {
        // Arrange
        $data = ['key' => 'value'];
        
        // Act
        $response = $this->postJson('/api/endpoint', $data);
        
        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        
        expect($response->json('data.key'))->toBe('value');
    });
});
```

### Best Practices

1. **Use descriptive test names**
   - ✅ `test('it can create an order with menu items and modifiers')`
   - ❌ `test('test1')`

2. **Follow AAA pattern**
   - Arrange: Setup test data
   - Act: Perform the action
   - Assert: Verify the result

3. **Test one thing at a time**
   - Each test should verify one specific behavior

4. **Use factories for test data**
   ```php
   $user = User::factory()->create();
   $menuItem = MenuItem::factory()->unavailable()->create();
   ```

5. **Clean up after tests**
   - Use `RefreshDatabase` trait
   - Use database transactions

---

## 🐛 Debugging Tests

### View Test Output

```bash
# Verbose output
vendor/bin/pest -v

# Very verbose output
vendor/bin/pest -vv

# Show test names
vendor/bin/pest --display-success
```

### Debug Specific Test

```php
test('debugging example', function () {
    dump($data); // Print data
    dd($result); // Dump and die
    
    // Use ray() if installed
    ray($data);
});
```

### Check Database State

```php
test('check database', function () {
    $this->assertDatabaseHas('orders', [
        'status' => 'pending',
    ]);
    
    expect(Order::count())->toBe(1);
});
```

---

## 🔍 Code Coverage

Generate code coverage report:

```bash
# HTML coverage report
vendor/bin/pest --coverage-html coverage

# Terminal coverage report
vendor/bin/pest --coverage

# Minimum coverage threshold
vendor/bin/pest --coverage --min=80
```

---

## 📚 Additional Resources

- [Pest PHP Documentation](https://pestphp.com/)
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/)

---

**Last Updated:** November 1, 2025
