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
            ['name' => 'กะเพราหมูกรอบ', 'price' => 70, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'กะเพราหมูสับ', 'price' => 40, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'กะเพราไก่', 'price' => 40, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'กะเพราหมูชิ้น', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'กะเพราเนื้อ', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'กะเพราหมูป่า', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'กะเพราปลาดุก', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'กะเพราทะเล', 'price' => 60, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ข้าวผัดหมู/ไก่', 'price' => 40, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ข้าวผัดทะเล', 'price' => 60, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ไข่เจียวหมูสับ', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดมาม่า หมู/ไก่', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดมาม่าทะเล', 'price' => 60, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดเผ็ดหมูป่า', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดเผ็ดปลาดุก', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดเผ็ด หมู/ไก่/เนื้อ', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดเผ็ดทะเล', 'price' => 60, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดพริกแกง หมู/ไก่/เนื้อ', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดพริกแกงหน่อไม้', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
            ['name' => 'ผัดพริกหยวก หมู/ไก่/เนื้อ', 'price' => 50, 'category_id' => $category->id, 'is_available' => true],
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
     * Attach specific modifiers to all menu items
     */
    private function attachModifiersToMenuItems(): void
    {
        // Get only the 3 specific modifiers
        $modifiers = Modifier::whereIn('name', ['พิเศษ', 'ไข่ดาว', 'ไข่เจียว'])->get();
        $menuItems = MenuItem::all();

        foreach ($menuItems as $menuItem) {
            $menuItem->modifiers()->syncWithoutDetaching($modifiers->pluck('id'));
        }

        $this->command->info('Modifiers attached to menu items successfully!');
    }
}

