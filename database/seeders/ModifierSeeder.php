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
            // Size modifiers
            ['name' => 'Extra Large', 'price_change' => 3.00],
            ['name' => 'Large', 'price_change' => 2.00],
            ['name' => 'Small', 'price_change' => -1.00],
            
            // Toppings & Add-ons
            ['name' => 'Extra Cheese', 'price_change' => 1.50],
            ['name' => 'Extra Bacon', 'price_change' => 2.00],
            ['name' => 'Avocado', 'price_change' => 1.50],
            ['name' => 'Fried Egg', 'price_change' => 1.00],
            ['name' => 'Mushrooms', 'price_change' => 1.00],
            ['name' => 'Jalapeños', 'price_change' => 0.50],
            ['name' => 'Onions', 'price_change' => 0.50],
            ['name' => 'Tomatoes', 'price_change' => 0.50],
            ['name' => 'Lettuce', 'price_change' => 0.50],
            
            // Protein add-ons
            ['name' => 'Extra Chicken', 'price_change' => 3.00],
            ['name' => 'Extra Beef', 'price_change' => 3.50],
            ['name' => 'Grilled Shrimp', 'price_change' => 4.00],
            ['name' => 'Salmon', 'price_change' => 5.00],
            
            // Sauces & Dressings
            ['name' => 'BBQ Sauce', 'price_change' => 0.00],
            ['name' => 'Ranch Dressing', 'price_change' => 0.00],
            ['name' => 'Hot Sauce', 'price_change' => 0.00],
            ['name' => 'Garlic Aioli', 'price_change' => 0.50],
            ['name' => 'Truffle Mayo', 'price_change' => 1.00],
            
            // Drink modifiers
            ['name' => 'Extra Shot Espresso', 'price_change' => 0.75],
            ['name' => 'Almond Milk', 'price_change' => 0.50],
            ['name' => 'Oat Milk', 'price_change' => 0.50],
            ['name' => 'Soy Milk', 'price_change' => 0.50],
            ['name' => 'Sugar Free', 'price_change' => 0.00],
            ['name' => 'Extra Ice', 'price_change' => 0.00],
            ['name' => 'No Ice', 'price_change' => 0.00],
            ['name' => 'Whipped Cream', 'price_change' => 0.75],
            ['name' => 'Caramel Drizzle', 'price_change' => 0.50],
            ['name' => 'Chocolate Syrup', 'price_change' => 0.50],
            
            // Dietary modifications
            ['name' => 'Gluten Free Bun', 'price_change' => 1.50],
            ['name' => 'Vegan Cheese', 'price_change' => 1.50],
            ['name' => 'Whole Wheat', 'price_change' => 0.50],
            
            // Cooking preferences
            ['name' => 'Well Done', 'price_change' => 0.00],
            ['name' => 'Medium', 'price_change' => 0.00],
            ['name' => 'Rare', 'price_change' => 0.00],
            ['name' => 'Extra Spicy', 'price_change' => 0.00],
            ['name' => 'Mild', 'price_change' => 0.00],
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
