<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tryout_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('order_number');
            $table->timestamps();

            $table->unique(['tryout_id', 'order_number'], 'tryout_questions_tryout_order_unique');
            $table->index('tryout_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tryout_questions');
    }
};