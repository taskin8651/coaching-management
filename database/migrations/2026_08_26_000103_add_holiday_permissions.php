<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $all = ['holiday_access', 'holiday_create', 'holiday_edit', 'holiday_show', 'holiday_delete'];

        $ids = collect($all)->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($ids->values());

        // Scoped to their own branch at the controller level, not the permission itself.
        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching($ids->values());

        foreach (['Staff', 'Teacher', 'Student', 'Parent'] as $title) {
            Role::where('title', $title)->first()?->permissions()->syncWithoutDetaching(
                $ids->only(['holiday_access', 'holiday_show'])->values()
            );
        }
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'holiday_access', 'holiday_create', 'holiday_edit', 'holiday_show', 'holiday_delete',
        ])->delete();
    }
};
