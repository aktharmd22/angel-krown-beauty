<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->string('header_type')->default('none')->after('category'); // none | text
            $table->string('header_text')->nullable()->after('header_type');
            $table->string('footer_text')->nullable()->after('header_text');
            $table->json('buttons')->nullable()->after('footer_text'); // [{type, text, url, phone}]
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->dropColumn(['header_type', 'header_text', 'footer_text', 'buttons']);
        });
    }
};
