<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $all = [
            'refund_access', 'refund_create', 'refund_edit', 'refund_show', 'refund_delete', 'refund_approve', 'refund_complete',
            'credit_access', 'credit_apply',
            'late_fee_apply',
            'fee_payment_allocate',
        ];

        $ids = collect($all)->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($ids->values());

        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching($ids->only([
            'refund_access', 'refund_create', 'refund_edit', 'refund_show', 'refund_approve', 'refund_complete',
            'credit_access', 'credit_apply',
            'late_fee_apply',
            'fee_payment_allocate',
        ])->values());

        Role::where('title', 'Staff')->first()?->permissions()->syncWithoutDetaching($ids->only([
            'refund_access', 'refund_create', 'refund_show',
            'credit_access',
        ])->values());

        foreach (['Teacher', 'Student', 'Parent'] as $title) {
            Role::where('title', $title)->first()?->permissions()->syncWithoutDetaching($ids->only([
                'refund_access', 'refund_show',
                'credit_access',
            ])->values());
        }
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'refund_access', 'refund_create', 'refund_edit', 'refund_show', 'refund_delete', 'refund_approve', 'refund_complete',
            'credit_access', 'credit_apply',
            'late_fee_apply',
            'fee_payment_allocate',
        ])->delete();
    }
};
