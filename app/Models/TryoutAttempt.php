<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TryoutAttempt extends Model
{
    use HasFactory;

    public const STATUSES = ['in_progress', 'submitted', 'expired'];

    protected $fillable = [
        'tryout_id',
        'student_id',
        'started_at',
        'expires_at',
        'submitted_at',
        'status',
        'total_questions',
        'answered_questions',
        'correct_answers',
        'incorrect_answers',
        'unanswered_questions',
        'total_points',
        'earned_points',
        'final_score',
        'graded_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'total_questions' => 'integer',
        'answered_questions' => 'integer',
        'correct_answers' => 'integer',
        'incorrect_answers' => 'integer',
        'unanswered_questions' => 'integer',
        'total_points' => 'integer',
        'earned_points' => 'integer',
        'final_score' => 'decimal:2',
    ];

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TryoutAttemptAnswer::class);
    }
}