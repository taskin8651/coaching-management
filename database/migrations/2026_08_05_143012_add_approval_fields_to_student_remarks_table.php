<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_remarks', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('visible_to_parent');
            $table->unsignedBigInteger('approved_by_id')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by_id');

            $table->foreign('approved_by_id')->references('id')->on('users')->nullOnDelete();
        });

        // Existing remarks were already effectively live before this feature existed.
        \Illuminate\Support\Facades\DB::table('student_remarks')->update(['approval_status' => 'approved']);

        $permission = Permission::firstOrCreate(['title' => 'student_remark_approve']);
        Role::where('title', 'Branch Manager')->first()?->permissions()->syncWithoutDetaching([$permission->id]);
    }

    public function down(): void
    {
        Schema::table('student_remarks', function (Blueprint $table) {
            $table->dropForeign(['approved_by_id']);
            $table->dropColumn(['approval_status', 'approved_by_id', 'approved_at']);
        });

        Permission::where('title', 'student_remark_approve')->delete();
    }
};
