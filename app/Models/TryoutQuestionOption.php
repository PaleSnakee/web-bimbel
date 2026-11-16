<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TryoutQuestionOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'tryout_question_id',
        'option_label',
        'option_text',
        'is_correct',
        'order_number',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'order_number' => 'integer',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(TryoutQuestion::class, 'tryout_question_id');
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(TryoutAttemptAnswer::class, 'tryout_question_option_id');
    }
}
