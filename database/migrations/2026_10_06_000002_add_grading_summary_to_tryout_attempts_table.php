<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tryout_attempts', function (Blueprint $table) {
            $table->unsignedInteger('total_questions')->nullable()->after('status');
            $table->unsignedInteger('answered_questions')->nullable()->after('total_questions');
            $table->unsignedInteger('correct_answers')->nullable()->after('answered_questions');
            $table->unsignedInteger('incorrect_answers')->nullable()->after('correct_answers');
            $table->unsignedInteger('unanswered_questions')->nullable()->after('incorrect_answers');
            $table->unsignedInteger('total_points')->nullable()->after('unanswered_questions');
            $table->unsignedInteger('earned_points')->nullable()->after('total_points');
            $table->decimal('final_score', 7, 2)->nullable()->after('earned_points');
            $table->timestamp('graded_at')->nullable()->after('final_score');

            $table->index(['tryout_id', 'graded_at']);
            $table->index(['student_id', 'final_score']);
        });
    }

    public function down(): void
    {
        Schema::table('tryout_attempts', function (Blueprint $table) {
            $table->dropIndex(['tryout_id', 'graded_at']);
            $table->dropIndex(['student_id', 'final_score']);
            $table->dropColumn([
                'total_questions',
                'answered_questions',
                'correct_answers',
                'incorrect_answers',
                'unanswered_questions',
                'total_points',
                'earned_points',
                'final_score',
                'graded_at',
            ]);
        });
    }
};