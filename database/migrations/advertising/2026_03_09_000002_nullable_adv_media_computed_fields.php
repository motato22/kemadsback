<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adv_media', function (Blueprint $table) {
            // Estas columnas las rellena el Observer tras el INSERT o vía Job periódico.
            // Deben permitir NULL para que el Repeater de Filament pueda crear el registro.
            $table->string('filename', 200)->nullable()->change();
            $table->text('cdn_url')->nullable()->change();
            $table->string('md5_hash', 32)->nullable()->change();
            $table->unsignedInteger('file_size_kb')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('adv_media', function (Blueprint $table) {
            $table->string('filename', 200)->nullable(false)->change();
            $table->text('cdn_url')->nullable(false)->change();
            $table->string('md5_hash', 32)->nullable(false)->change();
            $table->unsignedInteger('file_size_kb')->nullable(false)->change();
        });
    }
};
