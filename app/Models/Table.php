<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;
    protected $fillable = [
        'table_number',
        'status',
    ];

    /**
     * Get the orders for this table.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
