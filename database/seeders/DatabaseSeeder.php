<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding database for Nexadon POS System...');
        $this->command->newLine();

        // Seed in order: roles, users, tables, categories, modifiers, menu items
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            TableSeeder::class,
            CategorySeeder::class,
            ModifierSeeder::class,
            MenuItemSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->info('   - 4 Roles created');
        $this->command->info('   - 10 Users created (1 Admin, 2 Managers, 3 Cashiers, 4 Waiters)');
        $this->command->info('   - 15 Tables created');
        $this->command->info('   - 12 Categories created');
        $this->command->info('   - 40+ Modifiers created');
        $this->command->info('   - 60+ Menu items created with modifiers');
        $this->command->newLine();
    }
}
