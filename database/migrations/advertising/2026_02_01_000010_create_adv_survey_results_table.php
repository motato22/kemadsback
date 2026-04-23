<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_survey_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('adv_surveys')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('adv_survey_questions')->cascadeOnDelete();
            $table->foreignId('tablet_id')->constrained('adv_tablets')->cascadeOnDelete();
            $table->foreignId('driver_shift_id')->nullable()->constrained('adv_driver_shifts')->nullOnDelete();
            $table->tinyInteger('selected_option_index')->comment('Opción seleccionada 0-based');
            $table->boolean('is_correct')->nullable()->comment('null si no es modo trivia');
            $table->enum('completion_status', ['shown', 'started', 'completed'])->default('completed');
            $table->timestamp('answered_at');

            $table->index(['survey_id', 'answered_at']);
            $table->index(['tablet_id', 'answered_at']);
            $table->index('driver_shift_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_survey_results');
    }
};
