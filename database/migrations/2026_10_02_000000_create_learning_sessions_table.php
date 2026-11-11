<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_schedule_id')->constrained()->restrictOnDelete();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status', 20)->default('scheduled');
            $table->string('topic')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['learning_schedule_id', 'session_date'], 'learning_sessions_schedule_date_unique');
            $table->index(['session_date', 'status'], 'learning_sessions_date_status_index');
            $table->index(['learning_schedule_id', 'status'], 'learning_sessions_schedule_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_sessions');
    }
};