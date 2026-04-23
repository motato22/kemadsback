<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adv_surveys', function (Blueprint $table) {
            // La columna 'title' viene de la migración original pero el formulario
            // usa 'name' (añadido en el Módulo 4). Se hace nullable para evitar el
            // error "Field 'title' doesn't have a default value".
            $table->string('title', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('adv_surveys', function (Blueprint $table) {
            $table->string('title', 120)->nullable(false)->change();
        });
    }
};
