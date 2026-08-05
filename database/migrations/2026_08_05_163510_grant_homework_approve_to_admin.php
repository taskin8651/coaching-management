<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::where('title', 'homework_approve')->first();
        if ($permission) {
            Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching([$permission->id]);
        }
    }

    public function down(): void
    {
        // Intentionally left blank — this migration only repairs a data gap, nothing to revert safely.
    }
};
