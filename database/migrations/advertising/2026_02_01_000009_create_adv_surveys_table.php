<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('adv_campaigns')->nullOnDelete();
            $table->string('title', 120);
            $table->string('locale', 5)->default('es')->comment('es | en');
            $table->boolean('is_trivia')->default(false)->comment('Modo trivia: tiene respuesta correcta');
            $table->unsignedSmallInteger('display_after_secs')->default(60)->comment('Segundos de reproducción antes de mostrar');
            $table->unsignedSmallInteger('return_timer_secs')->default(30)->comment('Segundos para volver al reproductor si no hay interacción');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('adv_survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('adv_surveys')->cascadeOnDelete();
            $table->text('question_text');
            $table->json('options')->comment('Array de strings con las opciones de respuesta');
            $table->tinyInteger('correct_option_index')->nullable()->comment('Índice 0-based de la opción correcta en modo trivia');
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['survey_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_survey_questions');
        Schema::dropIfExists('adv_surveys');
    }
};
