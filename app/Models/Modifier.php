<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modifier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'price_change',
    ];

    protected $casts = [
        'price_change' => 'decimal:2',
    ];

    /**
     * Get the menu items that have this modifier.
     */
    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_modifiers');
    }

    /**
     * Get the order items that have this modifier.
     */
    public function orderItems(): BelongsToMany
    {
        return $this->belongsToMany(OrderItem::class, 'order_item_modifiers');
    }
}
