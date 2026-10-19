<?php

namespace Tests\Unit;

use App\Models\FeePayment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FeePaymentRefundableAmountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('payment_status')->default('completed');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('student_credit_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fee_payment_id')->nullable();
            $table->string('type');
            $table->string('source');
            $table->decimal('amount', 10, 2)->default(0);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fee_payment_id')->nullable();
            $table->string('approval_status');
            $table->string('status');
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->unsignedInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->text('properties')->nullable();
            $table->string('host')->nullable();
            $table->timestamps();
        });
    }

    public function test_pending_approved_and_completed_refunds_reserve_the_payment_amount(): void
    {
        $payment = FeePayment::create(['payment_status' => 'completed', 'paid_amount' => 100]);
        $payment->refunds()->create(['approval_status' => 'pending', 'status' => 'pending', 'amount' => 20]);
        $payment->refunds()->create(['approval_status' => 'approved', 'status' => 'pending', 'amount' => 30]);
        $payment->refunds()->create(['approval_status' => 'approved', 'status' => 'completed', 'amount' => 10]);

        $this->assertSame(40.0, $payment->refundableAmount());
    }

    public function test_rejected_and_cancelled_refunds_do_not_reserve_payment_amount(): void
    {
        $payment = FeePayment::create(['payment_status' => 'completed', 'paid_amount' => 100]);
        $payment->refunds()->create(['approval_status' => 'rejected', 'status' => 'pending', 'amount' => 40]);
        $payment->refunds()->create(['approval_status' => 'pending', 'status' => 'cancelled', 'amount' => 30]);

        $this->assertSame(100.0, $payment->refundableAmount());
    }

    public function test_cancelled_payments_are_not_refundable_and_editing_ignores_its_own_reservation(): void
    {
        $payment = FeePayment::create(['payment_status' => 'cancelled', 'paid_amount' => 100]);
        $this->assertSame(0.0, $payment->refundableAmount());

        $payment->update(['payment_status' => 'completed']);
        $refund = $payment->refunds()->create(['approval_status' => 'pending', 'status' => 'pending', 'amount' => 60]);

        $this->assertSame(40.0, $payment->refundableAmount());
        $this->assertSame(100.0, $payment->refundableAmount($refund->id));
    }
}
