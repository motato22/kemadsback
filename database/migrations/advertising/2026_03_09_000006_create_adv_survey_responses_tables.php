<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('adv_surveys')->cascadeOnDelete();
            $table->foreignId('tablet_id')->constrained('adv_tablets')->cascadeOnDelete();
            $table->string('email', 120)->nullable()->comment('Lead capturado');
            $table->timestamp('completed_at')->useCurrent();
        });

        Schema::create('adv_survey_response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('adv_survey_responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('adv_questions')->cascadeOnDelete();
            $table->foreignId('option_id')->constrained('adv_options')->cascadeOnDelete();
            $table->boolean('is_correct')->nullable()->comment('Snapshot de si acertó en caso de trivia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_survey_response_answers');
        Schema::dropIfExists('adv_survey_responses');
    }
};
