<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_contact_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id')->index();
            $table->unsignedBigInteger('contact_group_id')->index();
            $table->unique(['contact_id', 'contact_group_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_contact_group');
    }
};
