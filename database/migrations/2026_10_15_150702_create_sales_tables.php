<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('mobile', 16)->index();
            $table->string('email')->nullable();
            $table->text('nid')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_plan_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('lines');
            $table->timestamps();
        });

        Schema::create('holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('token_amount', 12, 2)->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'released_at']);
        });

        Schema::create('document_series', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->unsignedBigInteger('next_number')->default(1);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_no')->unique();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('active_unit_id')->nullable()->unique();
            $table->foreignId('unit_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('confirmed');
            $table->json('price_snapshot');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_reason')->nullable();
            $table->decimal('net_price', 12, 2);
            $table->timestamp('booked_at')->useCurrent();
            $table->timestamp('agreement_signed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'booked_at']);
            $table->index('customer_id');
        });

        Schema::create('schedule_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->unsignedInteger('seq');
            $table->string('label');
            $table->string('kind')->default('installment');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->unique(['booking_id', 'version', 'seq']);
            $table->index(['booking_id', 'due_date']);
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('method');
            $table->string('reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('received_at');
            $table->string('status')->default('posted');
            $table->foreignId('reverses_receipt_id')->nullable()->constrained('receipts')->nullOnDelete();
            $table->string('reversal_reason')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'received_at']);
            $table->index('status');
        });

        Schema::create('allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_row_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['receipt_id', 'schedule_row_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('allocations');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('schedule_rows');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('document_series');
        Schema::dropIfExists('holds');
        Schema::dropIfExists('payment_plan_templates');
        Schema::dropIfExists('customers');
    }
};
