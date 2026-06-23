<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('staff_attendances', 'batch_id')) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('branch_id');
                $table->foreign('batch_id')->references('id')->on('batches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('staff_attendances', 'batch_id')) {
                $table->dropForeign(['batch_id']);
                $table->dropColumn('batch_id');
            }
        });
    }
};
