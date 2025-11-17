<?php

namespace Database\Seeders;

use App\Models\Modifier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ModifierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modifiers = [
            ['name' => 'ไข่ดาว', 'price_change' => 10],
            ['name' => 'พิเศษ', 'price_change' => 10],
            ['name' => 'ทะเล', 'price_change' => 20],
            ['name' => 'หมูสับ', 'price_change' => 10],
            ['name' => 'ไข่เยี่ยวม้า', 'price_change' => 20],
            ['name' => 'เนื้อ', 'price_change' => 20],
        ];

        foreach ($modifiers as $modifier) {
            Modifier::firstOrCreate(
                ['name' => $modifier['name']],
                $modifier
            );
        }

        $this->command->info('Modifiers seeded successfully!');
    }
}
