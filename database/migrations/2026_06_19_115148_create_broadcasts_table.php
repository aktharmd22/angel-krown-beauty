<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('whatsapp_template_id')->nullable();
            $table->string('audience_type')->default('group'); // group | all_contacts | all_customers
            $table->unsignedBigInteger('contact_group_id')->nullable();
            $table->string('status')->default('draft'); // draft | queued | sending | sent | failed
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
