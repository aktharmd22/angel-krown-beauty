<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedBigInteger('admin_template_id')->nullable()->after('whatsapp_enabled');
            $table->unsignedBigInteger('customer_template_id')->nullable()->after('admin_template_id');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['admin_template_id', 'customer_template_id']);
        });
    }
};
