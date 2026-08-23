<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_types', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
        });

        $now = now();

        DB::table('exam_types')->insert(
            collect(['Weekly Test', 'Monthly Test', 'Unit Test', 'Mock Test', 'Final Test', 'Other'])
                ->map(fn ($name) => [
                    'name'       => $name,
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->toArray()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_types');
    }
};
