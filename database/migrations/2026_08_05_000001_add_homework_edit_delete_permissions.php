<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            'homework_edit',
            'homework_delete',
        ])->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        // Same roles that already have homework_create.
        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($permissions->values());
        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching($permissions->values());
        Role::where('title', 'Teacher')->first()?->permissions()->syncWithoutDetaching($permissions->values());
    }

    public function down(): void
    {
        Permission::whereIn('title', ['homework_edit', 'homework_delete'])->delete();
    }
};
