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
            ['name' => 'พิเศษ', 'price_change' => 10],
            ['name' => 'ไข่ดาว', 'price_change' => 10],
            ['name' => 'ไข่เจียว', 'price_change' => 10],
            ['name' => 'กุ้ง/หมึก', 'price_change' => 10],
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
