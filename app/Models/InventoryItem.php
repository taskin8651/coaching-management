<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['branch_id', 'name', 'category', 'unit', 'opening_stock', 'current_stock', 'low_stock_level', 'unit_cost', 'status'];
    protected $casts = ['unit_cost' => 'decimal:2'];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function transactions() { return $this->hasMany(InventoryTransaction::class); }
    public function getIsLowStockAttribute(): bool { return $this->current_stock <= $this->low_stock_level; }
}
