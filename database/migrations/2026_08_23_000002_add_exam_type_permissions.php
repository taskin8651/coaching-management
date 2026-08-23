<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = collect([
            'exam_type_create',
            'exam_type_edit',
            'exam_type_show',
            'exam_type_delete',
            'exam_type_access',
        ])->mapWithKeys(fn ($title) => [$title => Permission::firstOrCreate(['title' => $title])->id]);

        // Exam types are managed by Admin only.
        Role::where('title', 'Admin')->first()?->permissions()->syncWithoutDetaching($permissions->values());
    }

    public function down(): void
    {
        Permission::whereIn('title', [
            'exam_type_create',
            'exam_type_edit',
            'exam_type_show',
            'exam_type_delete',
            'exam_type_access',
        ])->delete();
    }
};
