<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('adv_surveys')->cascadeOnDelete();
            $table->string('question_text');
            $table->enum('type', ['single_choice', 'multiple_choice'])->default('single_choice');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_questions');
    }
};
