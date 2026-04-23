<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_driver_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tablet_id')->constrained('adv_tablets')->cascadeOnDelete();
            $table->string('driver_name', 80);
            $table->string('driver_code', 20)->nullable()->comment('Número de empleado o código interno');
            $table->timestamp('started_at')->comment('Inicio del turno');
            $table->timestamp('ended_at')->nullable()->comment('Null = turno activo actualmente');
            $table->timestamps();

            $table->index(['tablet_id', 'ended_at']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_driver_shifts');
    }
};
