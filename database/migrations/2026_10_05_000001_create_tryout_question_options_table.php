<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tryout_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_question_id')->constrained()->cascadeOnDelete();
            $table->string('option_label', 10);
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('order_number');
            $table->timestamps();

            $table->unique(['tryout_question_id', 'option_label'], 'tryout_question_options_question_label_unique');
            $table->unique(['tryout_question_id', 'order_number'], 'tryout_question_options_question_order_unique');
            $table->index(['tryout_question_id', 'is_correct'], 'tryout_question_options_question_correct_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tryout_question_options');
    }
};