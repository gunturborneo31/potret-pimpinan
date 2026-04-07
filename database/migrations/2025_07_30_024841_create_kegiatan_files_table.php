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
          Schema::create('kegiatan_files', function (Blueprint $table) {
        $table->id();
        $table->uuid('kegiatan_folder_id');
        $table->foreign('kegiatan_folder_id')->references('id')->on('kegiatan_folders')->onDelete('cascade');
        $table->string('nama_file');
        $table->string('path');
        $table->boolean('checked')->default(false);
        $table->unsignedBigInteger('jumlah_download')->default(0);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_files');
    }
};
