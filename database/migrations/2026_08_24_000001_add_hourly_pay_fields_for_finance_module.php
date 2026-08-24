<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (! Schema::hasColumn('staff', 'hourly_rate')) {
                $table->decimal('hourly_rate', 10, 2)->default(0)->after('salary_type');
            }
        });

        Schema::table('salary_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('salary_payments', 'salary_type')) {
                $table->string('salary_type')->nullable()->after('employee_type');
            }
        });

        // Standardize terminology: Teacher's old 'lecture' salary_type value becomes 'hourly'.
        DB::table('teachers')->where('salary_type', 'lecture')->update(['salary_type' => 'hourly']);

        // Staff role never received the salary permissions Teacher role already has.
        $permissionIds = Permission::whereIn('title', [
            'salary_report_access',
            'salary_payment_access',
            'salary_payment_show',
        ])->pluck('id');

        Role::where('title', 'Staff')->first()?->permissions()->syncWithoutDetaching($permissionIds);
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'hourly_rate')) {
                $table->dropColumn('hourly_rate');
            }
        });

        Schema::table('salary_payments', function (Blueprint $table) {
            if (Schema::hasColumn('salary_payments', 'salary_type')) {
                $table->dropColumn('salary_type');
            }
        });
    }
};
