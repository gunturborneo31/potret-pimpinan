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
        // Migration: create_permohonans_table.php
        Schema::create('permohonans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('kategori');
            $table->enum('priority', ['Critical/Urgent', 'Medium', 'Low']);
            $table->text('description');
            $table->string('file')->nullable();
            $table->enum('status', ['Diajukan', 'Diproses', 'Selesai','Ditolak'])->default('Diajukan');

            $table->uuid('user_id');       // Pengaju
            $table->uuid('disposisi')->nullable(); // Staff yang ditugaskan

            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('disposisi')->references('id')->on('users')->onDelete('set null');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permohonans');
    }
};
