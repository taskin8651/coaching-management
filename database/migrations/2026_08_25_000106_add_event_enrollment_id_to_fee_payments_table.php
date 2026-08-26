<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_payments', 'event_enrollment_id')) {
                $table->unsignedBigInteger('event_enrollment_id')->nullable()->after('fee_installment_id');
                $table->foreign('event_enrollment_id')->references('id')->on('event_enrollments')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (Schema::hasColumn('fee_payments', 'event_enrollment_id')) {
                $table->dropForeign(['event_enrollment_id']);
                $table->dropColumn('event_enrollment_id');
            }
        });
    }
};
