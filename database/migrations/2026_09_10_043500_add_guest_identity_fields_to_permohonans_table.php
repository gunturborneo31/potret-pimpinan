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
        Schema::table('permohonans', function (Blueprint $table) {
            $table->string('nama_pemohon')->nullable()->after('user_id');
            $table->string('email_pemohon')->nullable()->after('nama_pemohon');
            $table->string('no_hp_pemohon', 30)->nullable()->after('email_pemohon');
            $table->string('instansi_pemohon')->nullable()->after('no_hp_pemohon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permohonans', function (Blueprint $table) {
            $table->dropColumn([
                'nama_pemohon',
                'email_pemohon',
                'no_hp_pemohon',
                'instansi_pemohon',
            ]);
        });
    }
};
