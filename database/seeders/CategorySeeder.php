<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Appetizers'],
            ['name' => 'Main Courses'],
            ['name' => 'Pasta & Rice'],
            ['name' => 'Pizza'],
            ['name' => 'Burgers & Sandwiches'],
            ['name' => 'Salads'],
            ['name' => 'Desserts'],
            ['name' => 'Hot Drinks'],
            ['name' => 'Cold Drinks'],
            ['name' => 'Soft Drinks'],
            ['name' => 'Alcoholic Beverages'],
            ['name' => 'Smoothies & Juices'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }

        $this->command->info('Categories seeded successfully!');
    }
}
