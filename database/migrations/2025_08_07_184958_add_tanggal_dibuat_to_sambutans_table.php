<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('sambutans', function (Blueprint $table) {
        $table->date('tanggal_dibuat')->nullable()->after('judul');
    });
}

public function down()
{
    Schema::table('sambutans', function (Blueprint $table) {
        $table->dropColumn('tanggal_dibuat');
    });
}

};
