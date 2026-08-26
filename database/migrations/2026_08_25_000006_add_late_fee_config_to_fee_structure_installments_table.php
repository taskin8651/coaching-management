<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structure_installments', function (Blueprint $table) {
            if (Schema::hasColumn('fee_structure_installments', 'late_fee_enabled')) {
                return;
            }

            $table->boolean('late_fee_enabled')->default(false)->after('due_date');
            $table->enum('late_fee_type', ['fixed', 'percentage', 'per_day'])->nullable()->after('late_fee_enabled');
            $table->decimal('late_fee_amount', 12, 2)->nullable()->after('late_fee_type');
            $table->decimal('late_fee_percentage', 5, 2)->nullable()->after('late_fee_amount');
            $table->unsignedInteger('late_fee_grace_days')->default(0)->after('late_fee_percentage');
            $table->decimal('late_fee_max_amount', 12, 2)->nullable()->after('late_fee_grace_days');
        });
    }

    public function down(): void
    {
        Schema::table('fee_structure_installments', function (Blueprint $table) {
            if (Schema::hasColumn('fee_structure_installments', 'late_fee_enabled')) {
                $table->dropColumn([
                    'late_fee_enabled', 'late_fee_type', 'late_fee_amount',
                    'late_fee_percentage', 'late_fee_grace_days', 'late_fee_max_amount',
                ]);
            }
        });
    }
};
