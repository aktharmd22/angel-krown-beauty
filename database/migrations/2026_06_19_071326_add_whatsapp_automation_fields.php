<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('wa_template_language')->nullable()->default('en')->after('wa_reminder_template');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('owner_notified_at')->nullable()->after('confirmed_at');
            $table->timestamp('confirmation_sent_at')->nullable()->after('owner_notified_at');
            $table->timestamp('reminded_at')->nullable()->after('confirmation_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('wa_template_language');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['owner_notified_at', 'confirmation_sent_at', 'reminded_at']);
        });
    }
};
