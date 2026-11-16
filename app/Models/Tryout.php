<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tryout extends Model
{
    use HasFactory;

    public const STATUSES = ['draft', 'published', 'completed', 'cancelled'];

    protected $fillable = [
        'title',
        'description',
        'subject_id',
        'tutor_id',
        'start_at',
        'end_at',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function classRooms(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'tryout_class_room')
            ->withTimestamps();
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TryoutQuestion::class)->orderBy('order_number');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TryoutAttempt::class);
    }
}
