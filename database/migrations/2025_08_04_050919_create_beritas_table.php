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
      Schema::create('beritas', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('judul');
        $table->string('slug')->unique();
        $table->longText('isi');
        $table->date('tgl_terbit');
        $table->foreignId('penulis_id')->nullable()->constrained('users');
        $table->foreignId('editor_id')->nullable()->constrained('users');
        $table->json('lainnya_id')->nullable()->change();
        $table->foreignId('user_id')->constrained('users');
        $table->foreignId('kegiatan_id')->nullable()->constrained('kegiatan_folders');
        $table->string('file')->nullable();
        $table->boolean('is_public')->default(false);
        $table->unsignedBigInteger('jumlah_view')->default(0);
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beritas');
    }
};
