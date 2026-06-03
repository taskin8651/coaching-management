<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'guardian_user_id')) {
                $table->unsignedBigInteger('guardian_user_id')->nullable()->after('user_id');
                $table->foreign('guardian_user_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::table('admissions', function (Blueprint $table) {
            if (! Schema::hasColumn('admissions', 'guardian_whatsapp')) {
                $table->string('guardian_whatsapp')->nullable()->after('guardian_phone');
            }
        });

        Schema::create('erp_alert_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('module_name');
            $table->string('alert_type');
            $table->string('title');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->json('payload')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_alert_logs');

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'guardian_user_id')) {
                $table->dropForeign(['guardian_user_id']);
                $table->dropColumn('guardian_user_id');
            }
        });

        Schema::table('admissions', function (Blueprint $table) {
            if (Schema::hasColumn('admissions', 'guardian_whatsapp')) {
                $table->dropColumn('guardian_whatsapp');
            }
        });
    }
};
