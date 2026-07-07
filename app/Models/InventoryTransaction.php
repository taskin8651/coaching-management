<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = ['inventory_item_id', 'branch_id', 'expense_id', 'transaction_type', 'quantity', 'transaction_date', 'reference', 'remarks', 'created_by_id'];
    protected $dates = ['transaction_date', 'created_at', 'updated_at'];

    public function item() { return $this->belongsTo(InventoryItem::class, 'inventory_item_id'); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function expense() { return $this->belongsTo(Expense::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_id'); }
}
