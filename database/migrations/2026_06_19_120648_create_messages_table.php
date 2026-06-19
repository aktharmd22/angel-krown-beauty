<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->string('direction'); // inbound | outbound
            $table->string('type')->default('text');
            $table->text('body')->nullable();
            $table->string('media_url')->nullable();
            $table->string('wa_message_id')->nullable()->index();
            $table->string('status')->nullable(); // received | sent | delivered | read | failed
            $table->string('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
