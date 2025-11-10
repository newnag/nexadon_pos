# Database Seeders Documentation

## Overview
This document describes all database seeders created for the Nexadon POS System.

---

## Seeders Created

### 1. RoleSeeder
**File:** `database/seeders/RoleSeeder.php`

Creates 4 user roles for the POS system:

| Role ID | Role Name | Description |
|---------|-----------|-------------|
| 1 | Admin | Full system access, can manage everything |
| 2 | Manager | Can manage operations, view reports |
| 3 | Cashier | Can process orders and payments |
| 4 | Waiter | Can create orders and manage tables |

---

### 2. UserSeeder
**File:** `database/seeders/UserSeeder.php`

Creates default users for testing and development:

#### Default Admin Account
- **Email:** admin@nexadon.com
- **Password:** password
- **Role:** Admin
- **Name:** System Administrator

#### Test Users

**Managers:**
- manager@nexadon.com (John Manager)
- sarah.manager@nexadon.com (Sarah Manager)

**Cashiers:**
- cashier@nexadon.com (Mike Cashier)
- emma.cashier@nexadon.com (Emma Cashier)
- david.cashier@nexadon.com (David Cashier)

**Waiters:**
- waiter@nexadon.com (Lisa Waiter)
- tom.waiter@nexadon.com (Tom Waiter)
- anna.waiter@nexadon.com (Anna Waiter)
- james.waiter@nexadon.com (James Waiter)

All test users have the password: **password**

---

### 3. TableSeeder
**File:** `database/seeders/TableSeeder.php`

Creates 15 sample restaurant tables:

**Regular Tables:** T01 - T12
**VIP Tables:** VIP01, VIP02
**Bar Table:** BAR01

All tables are initially set to `available` status.

---

### 4. CategorySeeder
**File:** `database/seeders/CategorySeeder.php`

Creates 12 menu categories:

| Category | Type |
|----------|------|
| Appetizers | Food |
| Main Courses | Food |
| Pasta & Rice | Food |
| Pizza | Food |
| Burgers & Sandwiches | Food |
| Salads | Food |
| Desserts | Food |
| Hot Drinks | Beverage |
| Cold Drinks | Beverage |
| Soft Drinks | Beverage |
| Alcoholic Beverages | Beverage |
| Smoothies & Juices | Beverage |

---

### 5. ModifierSeeder
**File:** `database/seeders/ModifierSeeder.php`

Creates 40+ modifiers organized by type:

#### Size Modifiers
- Extra Large (+$3.00)
- Large (+$2.00)
- Small (-$1.00)

#### Toppings & Add-ons
- Extra Cheese (+$1.50)
- Extra Bacon (+$2.00)
- Avocado (+$1.50)
- Fried Egg (+$1.00)
- Mushrooms (+$1.00)
- Jalapeños, Onions, Tomatoes, Lettuce (+$0.50 each)

#### Protein Add-ons
- Extra Chicken (+$3.00)
- Extra Beef (+$3.50)
- Grilled Shrimp (+$4.00)
- Salmon (+$5.00)

#### Sauces & Dressings
- BBQ Sauce, Ranch Dressing, Hot Sauce (Free)
- Garlic Aioli (+$0.50)
- Truffle Mayo (+$1.00)

#### Drink Modifiers
- Extra Shot Espresso (+$0.75)
- Almond/Oat/Soy Milk (+$0.50 each)
- Sugar Free, Extra Ice, No Ice (Free)
- Whipped Cream (+$0.75)
- Caramel Drizzle, Chocolate Syrup (+$0.50 each)

#### Dietary Modifications
- Gluten Free Bun (+$1.50)
- Vegan Cheese (+$1.50)
- Whole Wheat (+$0.50)

#### Cooking Preferences
- Well Done, Medium, Rare (Free)
- Extra Spicy, Mild (Free)

---

### 6. MenuItemSeeder
**File:** `database/seeders/MenuItemSeeder.php`

Creates 60+ menu items across all categories with appropriate modifiers attached.

#### Sample Menu Items by Category:

**Appetizers (5 items)**
- Chicken Wings - $8.99
- Mozzarella Sticks - $7.99
- Garlic Bread - $5.99
- Calamari Rings - $10.99
- Spring Rolls - $6.99

**Main Courses (5 items)**
- Grilled Salmon - $18.99
- Ribeye Steak - $24.99
- Grilled Chicken Breast - $14.99
- BBQ Ribs - $19.99
- Fish and Chips - $13.99

**Pasta & Rice (5 items)**
- Spaghetti Carbonara - $12.99
- Fettuccine Alfredo - $11.99
- Penne Arrabbiata - $10.99
- Seafood Paella - $16.99
- Chicken Fried Rice - $9.99

**Pizza (5 items)**
- Margherita Pizza - $11.99
- Pepperoni Pizza - $13.99
- Hawaiian Pizza - $13.99
- Quattro Formaggi - $14.99
- BBQ Chicken Pizza - $14.99

**Burgers & Sandwiches (7 items)**
- Classic Beef Burger - $10.99
- Cheeseburger - $11.99
- Bacon Burger - $12.99
- Chicken Burger - $10.99
- Veggie Burger - $9.99
- Club Sandwich - $11.99
- BLT Sandwich - $9.99

**Salads (4 items)**
- Caesar Salad - $8.99
- Greek Salad - $9.99
- Garden Salad - $7.99
- Chicken Caesar Salad - $11.99

**Desserts (5 items)**
- Chocolate Lava Cake - $6.99
- Tiramisu - $7.99
- New York Cheesecake - $6.99
- Ice Cream Sundae - $5.99
- Apple Pie - $5.99

**Hot Drinks (6 items)**
- Espresso - $2.99
- Cappuccino - $3.99
- Latte - $4.49
- Americano - $3.49
- Hot Chocolate - $3.99
- Green Tea - $2.99

**Cold Drinks (4 items)**
- Iced Coffee - $4.49
- Iced Latte - $4.99
- Iced Tea - $3.49
- Lemonade - $3.99

**Soft Drinks (4 items)**
- Coca Cola - $2.49
- Sprite - $2.49
- Fanta Orange - $2.49
- Sparkling Water - $2.99

**Alcoholic Beverages (6 items)**
- House Red Wine - $7.99
- House White Wine - $7.99
- Draft Beer - $5.99
- Bottled Beer - $4.99
- Mojito - $8.99
- Margarita - $9.99

**Smoothies & Juices (5 items)**
- Strawberry Smoothie - $5.99
- Mango Smoothie - $5.99
- Mixed Berry Smoothie - $6.49
- Orange Juice - $3.99
- Apple Juice - $3.99

#### Modifier Attachments

The seeder automatically attaches appropriate modifiers to menu items:

- **Burgers** → Cheese, bacon, veggies, sauces, gluten-free bun
- **Pizza** → Size options, extra cheese, vegetable toppings
- **Salads** → Protein add-ons, dressing options
- **Main Courses** → Cooking preferences, sauce options
- **Hot/Cold Drinks** → Size options, milk alternatives, syrups
- **Smoothies** → Size options

---

## Running the Seeders

### Seed All Data
```bash
php artisan db:seed
```

### Seed Specific Seeder
```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=TableSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=ModifierSeeder
php artisan db:seed --class=MenuItemSeeder
```

### Fresh Migration with Seeding
```bash
php artisan migrate:fresh --seed
```

This will drop all tables, run migrations, and seed the database.

---

## Seeding Order

The seeders must run in this specific order due to foreign key dependencies:

1. **RoleSeeder** - No dependencies
2. **UserSeeder** - Depends on RoleSeeder
3. **TableSeeder** - No dependencies
4. **CategorySeeder** - No dependencies
5. **ModifierSeeder** - No dependencies
6. **MenuItemSeeder** - Depends on CategorySeeder and ModifierSeeder

This order is already configured in `DatabaseSeeder.php`.

---

## Data Statistics

After seeding, your database will contain:

| Entity | Count | Notes |
|--------|-------|-------|
| Roles | 4 | Admin, Manager, Cashier, Waiter |
| Users | 10 | 1 admin, 9 test users |
| Tables | 15 | All available initially |
| Categories | 12 | Food and beverage categories |
| Modifiers | 40+ | Various add-ons and customizations |
| Menu Items | 60+ | Complete menu with prices |
| Menu Item Modifiers | 200+ | Appropriate modifiers per item |

---

## Customization

To customize the seeded data:

1. **Edit existing seeders** in `database/seeders/`
2. **Add new items** to the arrays in each seeder
3. **Adjust prices** in the MenuItemSeeder
4. **Add more users** in the UserSeeder
5. **Modify modifier prices** in the ModifierSeeder

After making changes, run:
```bash
php artisan migrate:fresh --seed
```

---

## Production Considerations

⚠️ **Important:** These seeders are designed for development and testing.

For production:
1. **Remove or modify test users**
2. **Use strong passwords** (not "password")
3. **Don't seed sensitive data**
4. **Consider using separate seeders for production data**
5. **Create a production-specific DatabaseSeeder**

Example production seeder:
```php
// database/seeders/ProductionSeeder.php
public function run(): void
{
    $this->call([
        RoleSeeder::class,
        // Only create essential production data
    ]);
}
```

---

## Troubleshooting

### Issue: Foreign key constraint errors
**Solution:** Ensure seeders run in the correct order (as defined in DatabaseSeeder)

### Issue: Duplicate entry errors
**Solution:** Run `php artisan migrate:fresh --seed` to start with a clean database

### Issue: Memory errors with large datasets
**Solution:** Use chunking in seeders or increase PHP memory limit

### Issue: Missing relationships
**Solution:** Check that all required data is seeded before dependent data

---

## Testing the Seeded Data

You can verify the seeded data using tinker:

```bash
php artisan tinker
```

```php
// Check roles
Role::all();

// Check users with roles
User::with('role')->get();

// Check tables
Table::count();

// Check menu items with categories and modifiers
MenuItem::with(['category', 'modifiers'])->first();

// Login test
Auth::attempt(['email' => 'admin@nexadon.com', 'password' => 'password']);
```

---

## Next Steps

With the database seeded, you can now:

1. Test the authentication system with real users
2. Create API endpoints for menu management
3. Build order management functionality
4. Develop the frontend UI with real data
5. Test the complete POS workflow

Happy coding! 🚀
