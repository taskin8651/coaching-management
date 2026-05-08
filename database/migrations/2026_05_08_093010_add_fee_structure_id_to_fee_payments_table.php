<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeeStructureIdToFeePaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('fee_structure_id')->nullable()->after('batch_id');

            $table->foreign('fee_structure_id')
                ->references('id')
                ->on('fee_structures')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['fee_structure_id']);
            $table->dropColumn('fee_structure_id');
        });
    }
}