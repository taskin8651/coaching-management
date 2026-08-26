<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_installments', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_installments', 'student_fee_ledger_id')) {
                $table->unsignedBigInteger('student_fee_ledger_id')->nullable()->after('fee_structure_id');
                $table->unsignedBigInteger('fee_structure_installment_id')->nullable()->after('student_fee_ledger_id');
                $table->unsignedBigInteger('fee_account_id')->nullable()->after('fee_structure_installment_id');

                $table->foreign('student_fee_ledger_id')->references('id')->on('student_fee_ledgers')->nullOnDelete();
                $table->foreign('fee_structure_installment_id')->references('id')->on('fee_structure_installments')->nullOnDelete();
                $table->foreign('fee_account_id')->references('id')->on('fee_accounts')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_installments', function (Blueprint $table) {
            if (Schema::hasColumn('fee_installments', 'student_fee_ledger_id')) {
                $table->dropForeign(['student_fee_ledger_id']);
                $table->dropForeign(['fee_structure_installment_id']);
                $table->dropForeign(['fee_account_id']);
                $table->dropColumn(['student_fee_ledger_id', 'fee_structure_installment_id', 'fee_account_id']);
            }
        });
    }
};
