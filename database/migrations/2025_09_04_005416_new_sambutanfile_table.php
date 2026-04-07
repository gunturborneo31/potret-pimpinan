<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
       Schema::create('sambutan_files', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('sambutan_id')->constrained('sambutans')->cascadeOnDelete(); // UUID FK

    $table->string('type');
    $table->string('path');
    $table->string('original_name');
    $table->unsignedBigInteger('size')->nullable();
    $table->string('mime_type')->nullable();

    // Karena users.id juga UUID:
    $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

    $table->timestamps();
    $table->index(['sambutan_id','type']);
});

    }

    public function down(): void {
        Schema::dropIfExists('sambutan_files');
    }
};
