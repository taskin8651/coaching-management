<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_fee_ledgers', function (Blueprint $table) {
            if (! Schema::hasColumn('student_fee_ledgers', 'advance_balance')) {
                $table->decimal('advance_balance', 12, 2)->default(0)->after('outstanding_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_fee_ledgers', function (Blueprint $table) {
            if (Schema::hasColumn('student_fee_ledgers', 'advance_balance')) {
                $table->dropColumn('advance_balance');
            }
        });
    }
};
