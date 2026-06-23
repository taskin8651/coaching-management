<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            'teacher_attendance_access', 'teacher_attendance_create',
            'staff_attendance_access', 'staff_attendance_create',
        ])->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($permissions->values());
        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching($permissions->values());
        Role::where('title', 'Teacher')->first()?->permissions()->syncWithoutDetaching([$permissions['teacher_attendance_access']]);
        Role::where('title', 'Staff')->first()?->permissions()->syncWithoutDetaching([$permissions['staff_attendance_access']]);
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'teacher_attendance_access', 'teacher_attendance_create',
            'staff_attendance_access', 'staff_attendance_create',
        ])->delete();
    }
};
