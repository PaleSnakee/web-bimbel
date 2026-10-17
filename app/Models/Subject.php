<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use HasFactory;

    public const EDUCATION_LEVELS = ['PAUD/TK', 'SD', 'SMP'];

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'code',
        'name',
        'education_level',
        'description',
        'status',
    ];

    public function tutors(): BelongsToMany
    {
        return $this->belongsToMany(Tutor::class)->withTimestamps();
    }
}
