<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sambutan_files', function (Blueprint $table) {
            // 1) Pastikan tipe kolom sama persis (UUID). 
            // Jalankan baris di bawah HANYA jika sekarang bukan uuid/char(36):
            // $table->uuid('sambutan_id')->change(); // butuh doctrine/dbal

            // 2) Drop FK lama yang menunjuk ke `sambutans`
            $table->dropForeign('sambutan_files_sambutan_id_foreign');

            // 3) Tambah FK baru menunjuk ke tabel yang benar
            $table->foreign('sambutan_id')
                  ->references('id')
                  ->on('newsambutans')       // ✅ ganti sesuai tabel kamu
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sambutan_files', function (Blueprint $table) {
            $table->dropForeign(['sambutan_id']);
            $table->foreign('sambutan_id')
                  ->references('id')
                  ->on('sambutans')           // balik ke lama (jika perlu rollback)
                  ->cascadeOnDelete();
        });
    }
};
