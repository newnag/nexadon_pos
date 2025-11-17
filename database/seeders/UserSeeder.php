<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get role IDs
        $adminRole = Role::where('name', 'Admin')->first();
        $managerRole = Role::where('name', 'Manager')->first();
        $cashierRole = Role::where('name', 'Cashier')->first();
        $waiterRole = Role::where('name', 'Waiter')->first();

        // Create default admin user
        User::firstOrCreate(
            ['email' => 'inrada.nkb@gmail.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
            ]
        );

        // Create test users for each role
        $users = [
            // Managers
            [
                'name' => 'John Manager',
                'email' => 'manager@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
            ],
            [
                'name' => 'Sarah Manager',
                'email' => 'sarah.manager@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $managerRole->id,
            ],
            
            // Cashiers
            [
                'name' => 'Mike Cashier',
                'email' => 'cashier@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $cashierRole->id,
            ],
            [
                'name' => 'Emma Cashier',
                'email' => 'emma.cashier@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $cashierRole->id,
            ],
            [
                'name' => 'David Cashier',
                'email' => 'david.cashier@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $cashierRole->id,
            ],
            
            // Waiters
            [
                'name' => 'Lisa Waiter',
                'email' => 'waiter@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $waiterRole->id,
            ],
            [
                'name' => 'Tom Waiter',
                'email' => 'tom.waiter@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $waiterRole->id,
            ],
            [
                'name' => 'Anna Waiter',
                'email' => 'anna.waiter@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $waiterRole->id,
            ],
            [
                'name' => 'James Waiter',
                'email' => 'james.waiter@nexadon.com',
                'password' => Hash::make('password'),
                'role_id' => $waiterRole->id,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Users seeded successfully!');
    }
}
