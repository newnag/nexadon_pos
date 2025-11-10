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
            ['table_number' => 'T01', 'status' => 'available'],
            ['table_number' => 'T02', 'status' => 'available'],
            ['table_number' => 'T03', 'status' => 'available'],
            ['table_number' => 'T04', 'status' => 'available'],
            ['table_number' => 'T05', 'status' => 'available'],
            ['table_number' => 'T06', 'status' => 'available'],
            ['table_number' => 'T07', 'status' => 'available'],
            ['table_number' => 'T08', 'status' => 'available'],
            ['table_number' => 'T09', 'status' => 'available'],
            ['table_number' => 'T10', 'status' => 'available'],
            ['table_number' => 'T11', 'status' => 'available'],
            ['table_number' => 'T12', 'status' => 'available'],
            ['table_number' => 'VIP01', 'status' => 'available'],
            ['table_number' => 'VIP02', 'status' => 'available'],
            ['table_number' => 'BAR01', 'status' => 'available'],
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
