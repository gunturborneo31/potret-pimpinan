<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
     // Migration: create_komentars_table.php
Schema::create('komentars', function (Blueprint $table) {
    $table->id();
    $table->uuid('permohonan_id');
    $table->uuid('user_id');
    $table->text('komentar');
    $table->string('file')->nullable();
    $table->timestamps();

    // Foreign keys
    $table->foreign('permohonan_id')->references('id')->on('permohonans')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komentars');
    }
};
