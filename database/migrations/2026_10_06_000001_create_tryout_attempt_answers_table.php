<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tryout_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tryout_question_id')->constrained()->restrictOnDelete();
            $table->foreignId('tryout_question_option_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['tryout_attempt_id', 'tryout_question_id'], 'tryout_attempt_answers_attempt_question_unique');
            $table->index('tryout_question_id');
            $table->index('tryout_question_option_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tryout_attempt_answers');
    }
};