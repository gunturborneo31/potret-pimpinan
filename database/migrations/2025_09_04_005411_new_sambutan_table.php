<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('newsambutans', function (Blueprint $table) {
           $table->uuid('id')->primary();   // ⬅️ UUID
            $table->string('judul');
            $table->string('slug')->unique();
            $table->date('tanggal_terbit');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('newsambutans');
    }
};
