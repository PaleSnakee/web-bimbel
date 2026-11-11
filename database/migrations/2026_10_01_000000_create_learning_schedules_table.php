<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_room_id')->constrained()->restrictOnDelete();
            $table->foreignId('tutor_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->string('day_of_week', 12);
            $table->time('start_time');
            $table->time('end_time');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['class_room_id', 'day_of_week', 'status'], 'learning_schedules_class_day_status_index');
            $table->index(['tutor_id', 'day_of_week', 'status'], 'learning_schedules_tutor_day_status_index');
            $table->index(['subject_id', 'status'], 'learning_schedules_subject_status_index');
            $table->index(['effective_from', 'effective_until'], 'learning_schedules_effective_period_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_schedules');
    }
};
