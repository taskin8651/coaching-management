<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE fee_payments MODIFY payment_mode ENUM('cash','upi','bank_transfer','cheque','card','other','credit_adjustment') NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("UPDATE fee_payments SET payment_mode = 'other' WHERE payment_mode = 'credit_adjustment'");
        DB::statement("ALTER TABLE fee_payments MODIFY payment_mode ENUM('cash','upi','bank_transfer','cheque','card','other') NOT NULL DEFAULT 'cash'");
    }
};
