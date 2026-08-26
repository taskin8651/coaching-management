<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(['title' => 'fee_payment_cancel']);

        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching([$permission->id]);
        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching([$permission->id]);
    }

    public function down(): void
    {
        Permission::where('title', 'fee_payment_cancel')->delete();
    }
};
