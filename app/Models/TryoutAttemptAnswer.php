<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TryoutAttemptAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tryout_attempt_id',
        'tryout_question_id',
        'tryout_question_option_id',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TryoutAttempt::class, 'tryout_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(TryoutQuestion::class, 'tryout_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(TryoutQuestionOption::class, 'tryout_question_option_id');
    }
}