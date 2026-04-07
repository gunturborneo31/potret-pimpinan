<?php
// Migration: create_file_uploads_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('filename');
            $table->string('filepath'); // lokasi di storage
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // ukuran dalam bytes
            $table->uuid('folder_id')->nullable(); // boleh kosong
            $table->uuid('user_id'); // pemilik file
            $table->timestamps();

            // Relasi
            $table->foreign('folder_id')->references('id')->on('folders')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};
