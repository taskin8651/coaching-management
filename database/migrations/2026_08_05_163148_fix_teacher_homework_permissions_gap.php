<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Teacher role was granted homework_edit/homework_delete by an earlier migration but
     * never actually had homework_access/homework_create/homework_show synced to the DB —
     * the seeder file lists them, but no migration ever applied that for existing installs.
     * Without this, teachers cannot create or view homework in the live app at all.
     */
    public function up(): void
    {
        $permissions = Permission::whereIn('title', ['homework_access', 'homework_create', 'homework_show'])->pluck('id');

        Role::where('title', 'Teacher')->first()?->permissions()->syncWithoutDetaching($permissions);
    }

    public function down(): void
    {
        // Intentionally left blank — this migration only repairs a data gap, nothing to revert safely.
    }
};
