<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_payments', 'fee_installment_id')) {
                $table->unsignedBigInteger('fee_installment_id')->nullable()->after('fee_structure_id');
                $table->foreign('fee_installment_id')->references('id')->on('fee_installments')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (Schema::hasColumn('fee_payments', 'fee_installment_id')) {
                $table->dropForeign(['fee_installment_id']);
                $table->dropColumn('fee_installment_id');
            }
        });
    }
};
