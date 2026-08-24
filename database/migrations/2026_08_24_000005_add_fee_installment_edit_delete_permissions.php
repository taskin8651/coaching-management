<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            'fee_installment_edit',
            'fee_installment_delete',
        ])->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($permissions->values());
        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching($permissions->values());
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'fee_installment_edit',
            'fee_installment_delete',
        ])->delete();
    }
};
