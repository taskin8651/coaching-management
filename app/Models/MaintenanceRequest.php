<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'reported_by_id', 'assigned_to_id', 'expense_id', 'title', 'category', 'priority', 'status', 'description', 'repair_notes', 'reported_date', 'resolved_date'];
    protected $dates = ['reported_date', 'resolved_date', 'created_at', 'updated_at'];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function reportedBy() { return $this->belongsTo(User::class, 'reported_by_id'); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to_id'); }
    public function expense() { return $this->belongsTo(Expense::class); }
}
