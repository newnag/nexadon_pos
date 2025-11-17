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
        Schema::table('orders', function (Blueprint $table) {
            // Add order_type column
            $table->enum('order_type', ['dine-in', 'takeaway'])->default('dine-in')->after('user_id');
            
            // Add customer_name for takeaway orders
            $table->string('customer_name')->nullable()->after('order_type');
            
            // Add customer_phone for takeaway orders
            $table->string('customer_phone')->nullable()->after('customer_name');
            
            // Make table_id nullable to support takeaway orders
            $table->unsignedBigInteger('table_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn(['order_type', 'customer_name', 'customer_phone']);
            
            // Revert table_id to not nullable (if needed)
            // Note: This might fail if there are null values
            // $table->unsignedBigInteger('table_id')->nullable(false)->change();
        });
    }
};
