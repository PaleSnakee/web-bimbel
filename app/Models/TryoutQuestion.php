<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TryoutQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'tryout_id',
        'question_text',
        'points',
        'order_number',
    ];

    protected $casts = [
        'points' => 'integer',
        'order_number' => 'integer',
    ];

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(TryoutQuestionOption::class)->orderBy('order_number');
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(TryoutAttemptAnswer::class);
    }
}
