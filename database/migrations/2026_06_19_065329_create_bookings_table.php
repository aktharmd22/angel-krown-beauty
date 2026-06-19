<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('service')->nullable();
            $table->string('package')->nullable();
            $table->string('location')->default('salon'); // salon | home
            $table->string('address')->nullable();
            $table->string('specialist')->nullable();
            $table->string('preferred_date')->nullable();
            $table->string('preferred_time')->nullable();
            $table->string('status')->default('new'); // new | confirmed | done | cancelled
            $table->text('message')->nullable();       // composed WhatsApp message
            $table->string('source')->default('website');
            $table->string('wa_message_id')->nullable(); // for Cloud API later
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
