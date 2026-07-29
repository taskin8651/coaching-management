<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            'dashboard_access',
            'my_portal_access',
        ])->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        // Both are universal landing pages today (every role reaches one of them right after
        // login), so grant to every existing role to keep current behavior unchanged.
        Role::all()->each(function (Role $role) use ($permissions) {
            $role->permissions()->syncWithoutDetaching($permissions->values());
        });
    }

    public function down(): void
    {
        Permission::whereIn('title', ['dashboard_access', 'my_portal_access'])->delete();
    }
};
