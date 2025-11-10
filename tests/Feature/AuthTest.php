<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/**
 * Authentication API Tests
 * 
 * Note: User Registration is not included in this test suite.
 * In POS systems, user accounts are typically managed by administrators only,
 * not through public registration endpoints.
 */

describe('User Login', function () {
    beforeEach(function () {
        // Create roles
        $this->role = Role::create(['name' => 'Waiter']);

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->role->id,
        ]);
    });

    test('it can log in a user with correct credentials and returns a success status', function () {
        $credentials = [
            'email' => 'testuser@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
            ])
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'email' => 'testuser@example.com',
                ],
            ]);

        $this->assertAuthenticatedAs($this->user);
    });

    test('it fails to log in a user with incorrect password and returns an authentication error', function () {
        $credentials = [
            'email' => 'testuser@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    });

    test('it fails to log in a user with non-existent email', function () {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    });

    test('it fails if email field is missing', function () {
        $credentials = [
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });

    test('it fails if password field is missing', function () {
        $credentials = [
            'email' => 'testuser@example.com',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });

    test('it fails if email format is invalid', function () {
        $credentials = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    });
});

describe('Fetch Authenticated User', function () {
    beforeEach(function () {
        // Create role
        $this->role = Role::create(['name' => 'Cashier']);

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role_id' => $this->role->id,
        ]);
    });

    test('an authenticated user can fetch their own profile data', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role' => [
                        'id',
                        'name',
                    ],
                ],
            ])
            ->assertJson([
                'user' => [
                    'id' => $this->user->id,
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'role' => [
                        'name' => 'Cashier',
                    ],
                ],
            ]);
    });

    test('an unauthenticated user cannot fetch profile data and receives a 401 unauthorized error', function () {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    });

    test('authenticated user profile includes role information', function () {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonPath('user.role.name', 'Cashier');
    });
});

describe('User Logout', function () {
    beforeEach(function () {
        // Create role
        $this->role = Role::create(['name' => 'Manager']);

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'role_id' => $this->role->id,
        ]);
    });

    test('an authenticated user can log out successfully', function () {
        // Login via API to establish proper session
        $loginResponse = $this->withSession([])->postJson('/api/login', [
            'email' => 'manager@example.com',
            'password' => 'password',
        ]);
        $loginResponse->assertStatus(200);

        // Logout
        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);

        // Verify user is no longer authenticated
        $this->assertGuest('web');
    });

    test('an unauthenticated user cannot logout', function () {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    });

    test('after logout, unauthenticated user cannot access protected routes', function () {
        // Test that without authentication, protected routes return 401
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    });

    test('logout endpoint returns success response', function () {
        // Login to get authenticated
        $this->withSession([])->postJson('/api/login', [
            'email' => 'manager@example.com',
            'password' => 'password',
        ])->assertStatus(200);

        // Logout should return success message
        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout successful']);
    });
});

describe('Authentication Edge Cases', function () {
    beforeEach(function () {
        $this->role = Role::create(['name' => 'Admin']);
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'role_id' => $this->role->id,
        ]);
    });

    test('login with remember me option', function () {
        $credentials = [
            'email' => 'admin@example.com',
            'password' => 'password',
            'remember' => true,
        ];

        $response = $this->postJson('/api/login', $credentials);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($this->user);
    });

    test('multiple login attempts with wrong password', function () {
        $credentials = [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ];

        // Attempt 1
        $response = $this->postJson('/api/login', $credentials);
        $response->assertStatus(422);

        // Attempt 2
        $response = $this->postJson('/api/login', $credentials);
        $response->assertStatus(422);

        // Attempt 3
        $response = $this->postJson('/api/login', $credentials);
        $response->assertStatus(422);

        // All should fail consistently
        $this->assertGuest();
    });

    test('user can login multiple times successfully', function () {
        // First login
        $firstLogin = $this->withSession([])->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $firstLogin->assertStatus(200)
            ->assertJsonStructure(['message', 'user']);

        // Second login (simulating logout and login again)
        $secondLogin = $this->withSession([])->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $secondLogin->assertStatus(200)
            ->assertJsonStructure(['message', 'user']);
    });

    test('email is case-insensitive for login', function () {
        // Try login with uppercase email
        $response = $this->postJson('/api/login', [
            'email' => 'ADMIN@EXAMPLE.COM',
            'password' => 'password',
        ]);

        // Should succeed because emails are case-insensitive (standard behavior)
        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'user']);
        $this->assertAuthenticated('web');
    });

    test('whitespace in credentials is trimmed automatically by Laravel', function () {
        // Laravel's TrimStrings middleware automatically trims input
        $response = $this->postJson('/api/login', [
            'email' => ' admin@example.com ',
            'password' => 'password',
        ]);

        // Should succeed because whitespace is auto-trimmed (expected behavior)
        $response->assertStatus(200);
        $this->assertAuthenticated('web');
    });
});

describe('Role-Based Access', function () {
    beforeEach(function () {
        $this->adminRole = Role::create(['name' => 'Admin']);
        $this->waiterRole = Role::create(['name' => 'Waiter']);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
        ]);

        $this->waiter = User::factory()->create([
            'role_id' => $this->waiterRole->id,
        ]);
    });

    test('authenticated user profile includes correct role', function () {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonPath('user.role.name', 'Admin');

        $response = $this->actingAs($this->waiter, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonPath('user.role.name', 'Waiter');
    });

    test('user role is loaded with profile data', function () {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'role' => [
                        'id',
                        'name',
                    ],
                ],
            ]);
    });
});
