<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRoom extends Model
{
    use HasFactory;

    public const EDUCATION_LEVELS = ['PAUD/TK', 'SD', 'SMP'];

    public const STATUSES = ['active', 'inactive'];

    protected $fillable = [
        'code',
        'name',
        'education_level',
        'grade_level',
        'capacity',
        'description',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function enrolledCount(): int
    {
        return $this->students()->count();
    }

    public function hasCapacity(): bool
    {
        return $this->students()->count() < $this->capacity;
    }

    public static function gradeOptions(string $educationLevel): array
    {
        return match ($educationLevel) {
            'PAUD/TK' => ['Kelompok A', 'Kelompok B'],
            'SD' => ['1', '2', '3', '4', '5', '6'],
            'SMP' => ['7', '8', '9'],
            default => [],
        };
    }
}
