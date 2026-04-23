<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('adv_campaigns')->cascadeOnDelete();
            $table->enum('type', ['video', 'image']);
            $table->string('filename', 200);
            $table->string('storage_path', 500)->comment('Path completo en S3/R2');
            $table->text('cdn_url')->comment('URL firmada, se regenera periódicamente');
            $table->string('md5_hash', 32)->comment('Validación de integridad en tablet');
            $table->unsignedInteger('file_size_kb');
            $table->unsignedSmallInteger('duration_secs')->nullable()->comment('Solo videos');
            $table->smallInteger('sort_order')->default(0)->comment('Orden de reproducción dentro de la campaña');
            $table->timestamps();

            $table->index(['campaign_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_media');
    }
};
