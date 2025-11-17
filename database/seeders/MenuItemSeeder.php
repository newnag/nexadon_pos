<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Modifier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get category
        $category = Category::where('name', 'อาหารตามสั่ง')->first();

        if (!$category) {
            $this->command->error('Category "อาหารตามสั่ง" not found. Please run CategorySeeder first.');
            return;
        }

        // Menu items data
        $menuItems = [
            ['name' => 'กะเพรา', 'price' => 40, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ข้าวผัด', 'price' => 40, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดเผ็ด', 'price' => 40, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดพริกแกง', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดพริกหยวก', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดมาม่า', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ไข่เจียว', 'price' => 40, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ทอดกระเทียม', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
        ];

        foreach ($menuItems as $itemData) {
            MenuItem::firstOrCreate(
                ['name' => $itemData['name'], 'category_id' => $itemData['category_id']],
                $itemData
            );
        }

        // Attach all modifiers to all menu items
        $this->attachModifiersToMenuItems();

        $this->command->info('Menu items seeded successfully!');
    }

    /**
     * Attach all modifiers to all menu items
     */
    private function attachModifiersToMenuItems(): void
    {
        $allModifiers = Modifier::all();
        $menuItems = MenuItem::all();

        foreach ($menuItems as $menuItem) {
            $menuItem->modifiers()->syncWithoutDetaching($allModifiers->pluck('id'));
        }

        $this->command->info('Modifiers attached to menu items successfully!');
    }
}

