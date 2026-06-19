<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('booking_id')->nullable()->nullOnDelete();

            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();

            $table->date('issue_date');
            $table->string('status')->default('unpaid'); // draft | unpaid | paid | cancelled
            $table->string('payment_method')->nullable(); // cash | card | ewallet | bank

            $table->string('discount_type')->default('amount'); // amount | percent
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('tax_label')->default('SST');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
