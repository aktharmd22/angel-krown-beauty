<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('id')->index();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('customer_id')->nullable()->after('booking_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
    }
};
