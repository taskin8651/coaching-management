<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (Schema::hasColumn('fee_payments', 'fee_account_id')) {
                return;
            }

            $table->unsignedBigInteger('fee_account_id')->nullable()->after('fee_installment_id');

            $table->boolean('gst_applicable')->default(false)->after('payable_amount');
            $table->decimal('gst_percent', 5, 2)->nullable()->after('gst_applicable');
            $table->decimal('gst_amount', 12, 2)->default(0)->after('gst_percent');

            $table->string('cheque_number')->nullable()->after('payment_mode');
            $table->date('cheque_date')->nullable()->after('cheque_number');
            $table->string('cheque_bank_name')->nullable()->after('cheque_date');

            $table->string('upi_txn_ref')->nullable()->after('cheque_bank_name');

            $table->string('neft_rtgs_imps_utr')->nullable()->after('upi_txn_ref');
            $table->string('neft_rtgs_imps_bank_name')->nullable()->after('neft_rtgs_imps_utr');

            $table->string('card_gateway_ref')->nullable()->after('neft_rtgs_imps_bank_name');
            $table->string('other_reference')->nullable()->after('card_gateway_ref');

            $table->timestamp('cancelled_at')->nullable()->after('remarks');
            $table->unsignedBigInteger('cancelled_by_id')->nullable()->after('cancelled_at');
            $table->text('cancel_reason')->nullable()->after('cancelled_by_id');

            $table->string('receipt_academic_year')->nullable()->after('receipt_no');
            $table->unsignedInteger('receipt_sequence_no')->nullable()->after('receipt_academic_year');

            $table->foreign('fee_account_id')->references('id')->on('fee_accounts')->nullOnDelete();
            $table->foreign('cancelled_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_payments', 'fee_account_id')) {
                return;
            }

            $table->dropForeign(['fee_account_id']);
            $table->dropForeign(['cancelled_by_id']);

            $table->dropColumn([
                'fee_account_id',
                'gst_applicable',
                'gst_percent',
                'gst_amount',
                'cheque_number',
                'cheque_date',
                'cheque_bank_name',
                'upi_txn_ref',
                'neft_rtgs_imps_utr',
                'neft_rtgs_imps_bank_name',
                'card_gateway_ref',
                'other_reference',
                'cancelled_at',
                'cancelled_by_id',
                'cancel_reason',
                'receipt_academic_year',
                'receipt_sequence_no',
            ]);
        });
    }
};
