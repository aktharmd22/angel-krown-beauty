<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Public site
            $table->string('business_whatsapp_number')->nullable(); // wa.me number for the public site
            $table->string('owner_notify_number')->nullable();      // where the salon receives booking alerts

            // WhatsApp Cloud API (Meta)
            $table->boolean('whatsapp_enabled')->default(false);
            $table->string('wa_phone_number_id')->nullable();
            $table->string('wa_business_account_id')->nullable();
            $table->text('wa_access_token')->nullable();
            $table->string('wa_verify_token')->nullable();
            $table->string('wa_confirm_template')->nullable();
            $table->string('wa_reminder_template')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
