<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adv_tablet_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tablet_id')
                ->constrained('adv_tablets')
                ->cascadeOnDelete();
            $table->foreignId('media_id')
                ->constrained('adv_media')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('file_size_kb')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->string('status')->default('downloading'); // downloading|ready|failed
            $table->timestamps();

            $table->unique(['tablet_id', 'media_id']);
            $table->index(['tablet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adv_tablet_media');
    }
};

