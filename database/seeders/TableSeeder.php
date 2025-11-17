<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            ['table_number' => 'โต๊ะ 1', 'status' => 'available'],
            ['table_number' => 'โต๊ะ 2', 'status' => 'available'],
            ['table_number' => 'โต๊ะ 3', 'status' => 'available'],
            ['table_number' => 'โต๊ะ 4', 'status' => 'available'],
            ['table_number' => 'โต๊ะ 5', 'status' => 'available'],
            ['table_number' => 'โต๊ะ 6', 'status' => 'available'],
            ['table_number' => 'โต๊ะ 7', 'status' => 'available'],
            ['table_number' => 'โต๊ะ 8', 'status' => 'available'],
        ];

        foreach ($tables as $table) {
            Table::firstOrCreate(
                ['table_number' => $table['table_number']],
                $table
            );
        }

        $this->command->info('Tables seeded successfully!');
    }
}
