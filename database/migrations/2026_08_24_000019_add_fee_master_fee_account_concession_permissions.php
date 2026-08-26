<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $all = [
            'fee_master_access', 'fee_master_create', 'fee_master_edit', 'fee_master_show', 'fee_master_delete',
            'fee_account_access', 'fee_account_create', 'fee_account_edit', 'fee_account_show', 'fee_account_delete',
            'concession_access', 'concession_create', 'concession_edit', 'concession_show', 'concession_delete', 'concession_approve',
            'student_fee_ledger_access', 'student_fee_ledger_show', 'student_fee_ledger_create',
        ];

        $ids = collect($all)->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($ids->values());

        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching($ids->only([
            'fee_master_access', 'fee_master_create', 'fee_master_edit', 'fee_master_show',
            'fee_account_access', 'fee_account_create', 'fee_account_edit', 'fee_account_show',
            'concession_access', 'concession_create', 'concession_edit', 'concession_show', 'concession_approve',
            'student_fee_ledger_access', 'student_fee_ledger_show', 'student_fee_ledger_create',
        ])->values());

        Role::where('title', 'Staff')->first()?->permissions()->syncWithoutDetaching($ids->only([
            'fee_master_access', 'fee_account_access',
            'concession_access', 'concession_create', 'concession_show',
            'student_fee_ledger_access', 'student_fee_ledger_show',
        ])->values());

        foreach (['Teacher', 'Student', 'Parent'] as $title) {
            Role::where('title', $title)->first()?->permissions()->syncWithoutDetaching($ids->only([
                'student_fee_ledger_access', 'student_fee_ledger_show',
            ])->values());
        }
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'fee_master_access', 'fee_master_create', 'fee_master_edit', 'fee_master_show', 'fee_master_delete',
            'fee_account_access', 'fee_account_create', 'fee_account_edit', 'fee_account_show', 'fee_account_delete',
            'concession_access', 'concession_create', 'concession_edit', 'concession_show', 'concession_delete', 'concession_approve',
            'student_fee_ledger_access', 'student_fee_ledger_show', 'student_fee_ledger_create',
        ])->delete();
    }
};
