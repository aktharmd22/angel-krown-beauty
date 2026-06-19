<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('broadcast_id')->index();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('status')->default('pending'); // pending | sent | failed
            $table->string('wa_message_id')->nullable();
            $table->string('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['broadcast_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_recipients');
    }
};
