<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_payments', 'concession_id')) {
                $table->unsignedBigInteger('concession_id')->nullable()->after('discount');
                $table->foreign('concession_id')->references('id')->on('concessions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (Schema::hasColumn('fee_payments', 'concession_id')) {
                $table->dropForeign(['concession_id']);
                $table->dropColumn('concession_id');
            }
        });
    }
};
