<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            'staff_timetable_access',
            'staff_timetable_create',
            'staff_timetable_edit',
            'staff_timetable_delete',
        ])->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($permissions->values());
        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching($permissions->values());

        Role::where('title', 'Staff')->first()?->permissions()->syncWithoutDetaching(
            $permissions->only(['staff_timetable_access'])->values()
        );
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'staff_timetable_access',
            'staff_timetable_create',
            'staff_timetable_edit',
            'staff_timetable_delete',
        ])->delete();
    }
};
