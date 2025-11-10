<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status'); // For filtering active orders
            $table->index('table_id'); // For lookups by table
            $table->index('user_id'); // For lookups by user
            $table->index('created_at'); // For sorting by date
            $table->index(['status', 'created_at']); // Composite for active orders sorted by date
        });

        // Menu Items table indexes
        Schema::table('menu_items', function (Blueprint $table) {
            $table->index('category_id'); // For filtering by category
            $table->index('is_available'); // For filtering available items
            $table->index(['category_id', 'is_available']); // Composite for category + availability
        });

        // Order Items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id'); // For loading order items
            $table->index('menu_item_id'); // For menu item lookups
            $table->index('status'); // For filtering by status
        });

        // Tables table indexes
        Schema::table('tables', function (Blueprint $table) {
            $table->index('status'); // For filtering available tables
            $table->index('table_number'); // For quick lookups by table number
        });

        // Payments table indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index('order_id'); // For order payment lookups
            $table->index('payment_method'); // For filtering by payment method
            $table->index('created_at'); // For reporting
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('role_id'); // For filtering by role
            $table->index('email'); // Already unique, but explicit index for searches
        });

        // Order Item Modifiers table indexes (pivot)
        Schema::table('order_item_modifiers', function (Blueprint $table) {
            $table->index('order_item_id'); // For loading modifiers
            $table->index('modifier_id'); // For modifier lookups
        });

        // Menu Item Modifiers table indexes (pivot)
        Schema::table('menu_item_modifiers', function (Blueprint $table) {
            $table->index('menu_item_id'); // For loading modifiers
            $table->index('modifier_id'); // For modifier lookups
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['table_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        // Menu Items table
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            $table->dropIndex(['is_available']);
            $table->dropIndex(['category_id', 'is_available']);
        });

        // Order Items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['menu_item_id']);
            $table->dropIndex(['status']);
        });

        // Tables table
        Schema::table('tables', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['table_number']);
        });

        // Payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['payment_method']);
            $table->dropIndex(['created_at']);
        });

        // Users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
            $table->dropIndex(['email']);
        });

        // Order Item Modifiers table
        Schema::table('order_item_modifiers', function (Blueprint $table) {
            $table->dropIndex(['order_item_id']);
            $table->dropIndex(['modifier_id']);
        });

        // Menu Item Modifiers table
        Schema::table('menu_item_modifiers', function (Blueprint $table) {
            $table->dropIndex(['menu_item_id']);
            $table->dropIndex(['modifier_id']);
        });
    }
};
