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
        // Get categories
        $appetizers = Category::where('name', 'Appetizers')->first();
        $mainCourses = Category::where('name', 'Main Courses')->first();
        $pasta = Category::where('name', 'Pasta & Rice')->first();
        $pizza = Category::where('name', 'Pizza')->first();
        $burgers = Category::where('name', 'Burgers & Sandwiches')->first();
        $salads = Category::where('name', 'Salads')->first();
        $desserts = Category::where('name', 'Desserts')->first();
        $hotDrinks = Category::where('name', 'Hot Drinks')->first();
        $coldDrinks = Category::where('name', 'Cold Drinks')->first();
        $softDrinks = Category::where('name', 'Soft Drinks')->first();
        $alcohol = Category::where('name', 'Alcoholic Beverages')->first();
        $smoothies = Category::where('name', 'Smoothies & Juices')->first();

        // Menu items data
        $menuItems = [
            // Appetizers
            ['name' => 'Chicken Wings', 'price' => 8.99, 'category_id' => $appetizers->id, 'is_available' => true],
            ['name' => 'Mozzarella Sticks', 'price' => 7.99, 'category_id' => $appetizers->id, 'is_available' => true],
            ['name' => 'Garlic Bread', 'price' => 5.99, 'category_id' => $appetizers->id, 'is_available' => true],
            ['name' => 'Calamari Rings', 'price' => 10.99, 'category_id' => $appetizers->id, 'is_available' => true],
            ['name' => 'Spring Rolls', 'price' => 6.99, 'category_id' => $appetizers->id, 'is_available' => true],
            
            // Main Courses
            ['name' => 'Grilled Salmon', 'price' => 18.99, 'category_id' => $mainCourses->id, 'is_available' => true],
            ['name' => 'Ribeye Steak', 'price' => 24.99, 'category_id' => $mainCourses->id, 'is_available' => true],
            ['name' => 'Grilled Chicken Breast', 'price' => 14.99, 'category_id' => $mainCourses->id, 'is_available' => true],
            ['name' => 'BBQ Ribs', 'price' => 19.99, 'category_id' => $mainCourses->id, 'is_available' => true],
            ['name' => 'Fish and Chips', 'price' => 13.99, 'category_id' => $mainCourses->id, 'is_available' => true],
            
            // Pasta & Rice
            ['name' => 'Spaghetti Carbonara', 'price' => 12.99, 'category_id' => $pasta->id, 'is_available' => true],
            ['name' => 'Fettuccine Alfredo', 'price' => 11.99, 'category_id' => $pasta->id, 'is_available' => true],
            ['name' => 'Penne Arrabbiata', 'price' => 10.99, 'category_id' => $pasta->id, 'is_available' => true],
            ['name' => 'Seafood Paella', 'price' => 16.99, 'category_id' => $pasta->id, 'is_available' => true],
            ['name' => 'Chicken Fried Rice', 'price' => 9.99, 'category_id' => $pasta->id, 'is_available' => true],
            
            // Pizza
            ['name' => 'Margherita Pizza', 'price' => 11.99, 'category_id' => $pizza->id, 'is_available' => true],
            ['name' => 'Pepperoni Pizza', 'price' => 13.99, 'category_id' => $pizza->id, 'is_available' => true],
            ['name' => 'Hawaiian Pizza', 'price' => 13.99, 'category_id' => $pizza->id, 'is_available' => true],
            ['name' => 'Quattro Formaggi', 'price' => 14.99, 'category_id' => $pizza->id, 'is_available' => true],
            ['name' => 'BBQ Chicken Pizza', 'price' => 14.99, 'category_id' => $pizza->id, 'is_available' => true],
            
            // Burgers & Sandwiches
            ['name' => 'Classic Beef Burger', 'price' => 10.99, 'category_id' => $burgers->id, 'is_available' => true],
            ['name' => 'Cheeseburger', 'price' => 11.99, 'category_id' => $burgers->id, 'is_available' => true],
            ['name' => 'Bacon Burger', 'price' => 12.99, 'category_id' => $burgers->id, 'is_available' => true],
            ['name' => 'Chicken Burger', 'price' => 10.99, 'category_id' => $burgers->id, 'is_available' => true],
            ['name' => 'Veggie Burger', 'price' => 9.99, 'category_id' => $burgers->id, 'is_available' => true],
            ['name' => 'Club Sandwich', 'price' => 11.99, 'category_id' => $burgers->id, 'is_available' => true],
            ['name' => 'BLT Sandwich', 'price' => 9.99, 'category_id' => $burgers->id, 'is_available' => true],
            
            // Salads
            ['name' => 'Caesar Salad', 'price' => 8.99, 'category_id' => $salads->id, 'is_available' => true],
            ['name' => 'Greek Salad', 'price' => 9.99, 'category_id' => $salads->id, 'is_available' => true],
            ['name' => 'Garden Salad', 'price' => 7.99, 'category_id' => $salads->id, 'is_available' => true],
            ['name' => 'Chicken Caesar Salad', 'price' => 11.99, 'category_id' => $salads->id, 'is_available' => true],
            
            // Desserts
            ['name' => 'Chocolate Lava Cake', 'price' => 6.99, 'category_id' => $desserts->id, 'is_available' => true],
            ['name' => 'Tiramisu', 'price' => 7.99, 'category_id' => $desserts->id, 'is_available' => true],
            ['name' => 'New York Cheesecake', 'price' => 6.99, 'category_id' => $desserts->id, 'is_available' => true],
            ['name' => 'Ice Cream Sundae', 'price' => 5.99, 'category_id' => $desserts->id, 'is_available' => true],
            ['name' => 'Apple Pie', 'price' => 5.99, 'category_id' => $desserts->id, 'is_available' => true],
            
            // Hot Drinks
            ['name' => 'Espresso', 'price' => 2.99, 'category_id' => $hotDrinks->id, 'is_available' => true],
            ['name' => 'Cappuccino', 'price' => 3.99, 'category_id' => $hotDrinks->id, 'is_available' => true],
            ['name' => 'Latte', 'price' => 4.49, 'category_id' => $hotDrinks->id, 'is_available' => true],
            ['name' => 'Americano', 'price' => 3.49, 'category_id' => $hotDrinks->id, 'is_available' => true],
            ['name' => 'Hot Chocolate', 'price' => 3.99, 'category_id' => $hotDrinks->id, 'is_available' => true],
            ['name' => 'Green Tea', 'price' => 2.99, 'category_id' => $hotDrinks->id, 'is_available' => true],
            
            // Cold Drinks
            ['name' => 'Iced Coffee', 'price' => 4.49, 'category_id' => $coldDrinks->id, 'is_available' => true],
            ['name' => 'Iced Latte', 'price' => 4.99, 'category_id' => $coldDrinks->id, 'is_available' => true],
            ['name' => 'Iced Tea', 'price' => 3.49, 'category_id' => $coldDrinks->id, 'is_available' => true],
            ['name' => 'Lemonade', 'price' => 3.99, 'category_id' => $coldDrinks->id, 'is_available' => true],
            
            // Soft Drinks
            ['name' => 'Coca Cola', 'price' => 2.49, 'category_id' => $softDrinks->id, 'is_available' => true],
            ['name' => 'Sprite', 'price' => 2.49, 'category_id' => $softDrinks->id, 'is_available' => true],
            ['name' => 'Fanta Orange', 'price' => 2.49, 'category_id' => $softDrinks->id, 'is_available' => true],
            ['name' => 'Sparkling Water', 'price' => 2.99, 'category_id' => $softDrinks->id, 'is_available' => true],
            
            // Alcoholic Beverages
            ['name' => 'House Red Wine', 'price' => 7.99, 'category_id' => $alcohol->id, 'is_available' => true],
            ['name' => 'House White Wine', 'price' => 7.99, 'category_id' => $alcohol->id, 'is_available' => true],
            ['name' => 'Draft Beer', 'price' => 5.99, 'category_id' => $alcohol->id, 'is_available' => true],
            ['name' => 'Bottled Beer', 'price' => 4.99, 'category_id' => $alcohol->id, 'is_available' => true],
            ['name' => 'Mojito', 'price' => 8.99, 'category_id' => $alcohol->id, 'is_available' => true],
            ['name' => 'Margarita', 'price' => 9.99, 'category_id' => $alcohol->id, 'is_available' => true],
            
            // Smoothies & Juices
            ['name' => 'Strawberry Smoothie', 'price' => 5.99, 'category_id' => $smoothies->id, 'is_available' => true],
            ['name' => 'Mango Smoothie', 'price' => 5.99, 'category_id' => $smoothies->id, 'is_available' => true],
            ['name' => 'Mixed Berry Smoothie', 'price' => 6.49, 'category_id' => $smoothies->id, 'is_available' => true],
            ['name' => 'Orange Juice', 'price' => 3.99, 'category_id' => $smoothies->id, 'is_available' => true],
            ['name' => 'Apple Juice', 'price' => 3.99, 'category_id' => $smoothies->id, 'is_available' => true],
        ];

        foreach ($menuItems as $itemData) {
            MenuItem::firstOrCreate(
                ['name' => $itemData['name'], 'category_id' => $itemData['category_id']],
                $itemData
            );
        }

        // Attach modifiers to menu items
        $this->attachModifiersToMenuItems();

        $this->command->info('Menu items seeded successfully!');
    }

    /**
     * Attach appropriate modifiers to menu items
     */
    private function attachModifiersToMenuItems(): void
    {
        // Get modifiers
        $sizeModifiers = Modifier::whereIn('name', ['Extra Large', 'Large', 'Small'])->get();
        $cheeseModifier = Modifier::where('name', 'Extra Cheese')->first();
        $baconModifier = Modifier::where('name', 'Extra Bacon')->first();
        $avocadoModifier = Modifier::where('name', 'Avocado')->first();
        $eggModifier = Modifier::where('name', 'Fried Egg')->first();
        $mushroomsModifier = Modifier::where('name', 'Mushrooms')->first();
        $veggieModifiers = Modifier::whereIn('name', ['Jalapeños', 'Onions', 'Tomatoes', 'Lettuce'])->get();
        $proteinModifiers = Modifier::whereIn('name', ['Extra Chicken', 'Extra Beef', 'Grilled Shrimp', 'Salmon'])->get();
        $sauceModifiers = Modifier::whereIn('name', ['BBQ Sauce', 'Ranch Dressing', 'Hot Sauce', 'Garlic Aioli', 'Truffle Mayo'])->get();
        $drinkModifiers = Modifier::whereIn('name', ['Extra Shot Espresso', 'Almond Milk', 'Oat Milk', 'Soy Milk', 'Sugar Free', 'Extra Ice', 'No Ice', 'Whipped Cream', 'Caramel Drizzle', 'Chocolate Syrup'])->get();
        $glutenFreeModifier = Modifier::where('name', 'Gluten Free Bun')->first();
        $veganCheeseModifier = Modifier::where('name', 'Vegan Cheese')->first();
        $cookingModifiers = Modifier::whereIn('name', ['Well Done', 'Medium', 'Rare', 'Extra Spicy', 'Mild'])->get();

        // Burgers - add typical burger modifiers
        $burgers = MenuItem::whereHas('category', function($query) {
            $query->where('name', 'Burgers & Sandwiches');
        })->get();
        
        foreach ($burgers as $burger) {
            $burger->modifiers()->syncWithoutDetaching($cheeseModifier->id);
            $burger->modifiers()->syncWithoutDetaching($baconModifier->id);
            $burger->modifiers()->syncWithoutDetaching($avocadoModifier->id);
            $burger->modifiers()->syncWithoutDetaching($eggModifier->id);
            $burger->modifiers()->syncWithoutDetaching($mushroomsModifier->id);
            $burger->modifiers()->syncWithoutDetaching($veggieModifiers->pluck('id'));
            $burger->modifiers()->syncWithoutDetaching($sauceModifiers->pluck('id'));
            $burger->modifiers()->syncWithoutDetaching($glutenFreeModifier->id);
        }

        // Pizza - add pizza modifiers
        $pizzas = MenuItem::whereHas('category', function($query) {
            $query->where('name', 'Pizza');
        })->get();
        
        foreach ($pizzas as $pizza) {
            $pizza->modifiers()->syncWithoutDetaching($sizeModifiers->pluck('id'));
            $pizza->modifiers()->syncWithoutDetaching($cheeseModifier->id);
            $pizza->modifiers()->syncWithoutDetaching($veggieModifiers->pluck('id'));
        }

        // Salads - add protein options
        $salads = MenuItem::whereHas('category', function($query) {
            $query->where('name', 'Salads');
        })->get();
        
        foreach ($salads as $salad) {
            $salad->modifiers()->syncWithoutDetaching($proteinModifiers->pluck('id'));
            $salad->modifiers()->syncWithoutDetaching($sauceModifiers->pluck('id'));
        }

        // Main courses - cooking preferences
        $mainCourses = MenuItem::whereHas('category', function($query) {
            $query->where('name', 'Main Courses');
        })->get();
        
        foreach ($mainCourses as $mainCourse) {
            $mainCourse->modifiers()->syncWithoutDetaching($cookingModifiers->pluck('id'));
            $mainCourse->modifiers()->syncWithoutDetaching($sauceModifiers->pluck('id'));
        }

        // Hot drinks - drink modifiers
        $hotDrinks = MenuItem::whereHas('category', function($query) {
            $query->where('name', 'Hot Drinks');
        })->get();
        
        foreach ($hotDrinks as $drink) {
            $drink->modifiers()->syncWithoutDetaching($drinkModifiers->pluck('id'));
            $drink->modifiers()->syncWithoutDetaching($sizeModifiers->pluck('id'));
        }

        // Cold drinks - drink modifiers
        $coldDrinks = MenuItem::whereHas('category', function($query) {
            $query->where('name', 'Cold Drinks');
        })->get();
        
        foreach ($coldDrinks as $drink) {
            $drink->modifiers()->syncWithoutDetaching($drinkModifiers->pluck('id'));
            $drink->modifiers()->syncWithoutDetaching($sizeModifiers->pluck('id'));
        }

        // Smoothies - size modifiers
        $smoothies = MenuItem::whereHas('category', function($query) {
            $query->where('name', 'Smoothies & Juices');
        })->get();
        
        foreach ($smoothies as $smoothie) {
            $smoothie->modifiers()->syncWithoutDetaching($sizeModifiers->pluck('id'));
        }

        $this->command->info('Modifiers attached to menu items successfully!');
    }
}

