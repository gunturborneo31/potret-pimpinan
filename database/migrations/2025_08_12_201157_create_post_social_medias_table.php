<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('post_social_medias', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->string('link');
            $table->enum('option', [
                'Instagram Reels',
                'Instagram Post',
                'Youtube Video',
                'Youtube Short',
                'Tiktok',
                'Facebook Post',
            ]);
            // Sesuaikan tipe FK ke users: kalau users.id bertipe UUID gunakan foreignUuid,
            // kalau integer (auto increment) gunakan foreignId.
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_social_medias');
    }
};

