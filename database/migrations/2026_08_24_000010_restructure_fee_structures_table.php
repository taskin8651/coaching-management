<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            if (Schema::hasColumn('fee_structures', 'admission_fee')) {
                $table->dropColumn(['admission_fee', 'tuition_fee', 'exam_fee', 'material_fee', 'other_fee']);
            }
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_structures', 'academic_year')) {
                $table->string('academic_year')->nullable()->after('title');
                $table->string('board')->nullable()->after('academic_year');
                $table->string('standard')->nullable()->after('board');

                $table->unsignedInteger('version_no')->default(1)->after('standard');
                $table->unsignedBigInteger('root_fee_structure_id')->nullable()->after('version_no');

                $table->date('effective_from')->nullable()->after('root_fee_structure_id');
                $table->date('effective_to')->nullable()->after('effective_from');

                $table->boolean('installment_allocation_override')->default(false)->after('effective_to');

                $table->foreign('root_fee_structure_id')->references('id')->on('fee_structures')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_structures', function (Blueprint $table) {
            if (Schema::hasColumn('fee_structures', 'academic_year')) {
                $table->dropForeign(['root_fee_structure_id']);
                $table->dropColumn([
                    'academic_year',
                    'board',
                    'standard',
                    'version_no',
                    'root_fee_structure_id',
                    'effective_from',
                    'effective_to',
                    'installment_allocation_override',
                ]);
            }
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_structures', 'admission_fee')) {
                $table->decimal('admission_fee', 12, 2)->default(0);
                $table->decimal('tuition_fee', 12, 2)->default(0);
                $table->decimal('exam_fee', 12, 2)->default(0);
                $table->decimal('material_fee', 12, 2)->default(0);
                $table->decimal('other_fee', 12, 2)->default(0);
            }
        });
    }
};
