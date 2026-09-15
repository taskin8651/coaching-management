<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        $now = now();
        $currentStartYear = $now->month >= 4 ? $now->year : $now->year - 1;
        $currentName = $currentStartYear . '-' . substr((string) ($currentStartYear + 1), -2);

        $years = collect([$currentName]);

        if (Schema::hasTable('fee_structures') && Schema::hasColumn('fee_structures', 'academic_year')) {
            $years = $years->merge(
                DB::table('fee_structures')
                    ->whereNotNull('academic_year')
                    ->where('academic_year', '!=', '')
                    ->distinct()
                    ->pluck('academic_year')
            );
        }

        foreach ($years->filter()->unique()->values() as $year) {
            DB::table('academic_years')->insert([
                'name' => $year,
                'is_current' => $year === $currentName,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
