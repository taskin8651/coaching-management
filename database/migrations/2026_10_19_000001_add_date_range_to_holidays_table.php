<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('date');
            $table->date('end_date')->nullable()->after('start_date');
        });

        DB::table('holidays')->whereNull('start_date')->update(['start_date' => DB::raw('date')]);
        DB::table('holidays')->whereNull('end_date')->update(['end_date' => DB::raw('date')]);
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
    }
};
