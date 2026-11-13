<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tryouts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('tutor_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->unsignedInteger('duration_minutes');
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index(['status', 'start_at'], 'tryouts_status_start_at_index');
            $table->index(['subject_id', 'start_at'], 'tryouts_subject_start_at_index');
            $table->index('tutor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tryouts');
    }
};