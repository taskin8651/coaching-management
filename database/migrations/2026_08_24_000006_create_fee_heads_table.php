<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_heads', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->boolean('gst_applicable')->default(false);
            $table->decimal('default_gst_percent', 5, 2)->default(0);

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
        });

        $now = now();

        DB::table('fee_heads')->insert(
            collect([
                ['code' => 'admission', 'name' => 'Admission Fee'],
                ['code' => 'tuition', 'name' => 'Tuition Fee'],
                ['code' => 'exam', 'name' => 'Examination Fee'],
                ['code' => 'material', 'name' => 'Study Material Fee'],
                ['code' => 'other', 'name' => 'Miscellaneous Fee'],
            ])->map(fn ($row) => $row + [
                'description' => null,
                'gst_applicable' => false,
                'default_gst_percent' => 0,
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ])->toArray()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_heads');
    }
};
