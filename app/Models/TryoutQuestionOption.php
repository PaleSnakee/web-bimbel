<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
