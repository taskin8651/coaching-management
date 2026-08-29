<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (! Schema::hasColumn('branches', 'weekly_off_day')) {
                // 0 = Sunday .. 6 = Saturday (matches Carbon::SUNDAY..SATURDAY). NULL = defaults
                // to Sunday at the WorkingDaysCalculator level, so existing branches need no backfill.
                $table->unsignedTinyInteger('weekly_off_day')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'weekly_off_day')) {
                $table->dropColumn('weekly_off_day');
            }
        });
    }
};
