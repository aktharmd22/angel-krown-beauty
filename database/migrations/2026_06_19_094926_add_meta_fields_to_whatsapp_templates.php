<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->string('category')->default('UTILITY')->after('language'); // UTILITY | MARKETING | AUTHENTICATION
            $table->string('meta_template_id')->nullable()->after('category');
            $table->string('meta_status')->default('local')->after('meta_template_id'); // local|pending|approved|rejected
            $table->string('meta_rejected_reason')->nullable()->after('meta_status');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->dropColumn(['category', 'meta_template_id', 'meta_status', 'meta_rejected_reason']);
        });
    }
};
