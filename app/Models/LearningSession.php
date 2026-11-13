<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningSession extends Model
{
    use HasFactory;

    public const STATUSES = ['scheduled', 'completed', 'cancelled'];

    protected $fillable = [
        'learning_schedule_id',
        'session_date',
        'start_time',
        'end_time',
        'status',
        'topic',
        'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function learningSchedule(): BelongsTo
    {
        return $this->belongsTo(LearningSchedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }
}