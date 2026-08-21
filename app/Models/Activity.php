<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'teaching_assignment_id',
        'partial_id',
        'name',
        'description',
        'due_date',
        'percentage',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'due_date' => 'date',
            'active' => 'boolean',
        ];
    }

    /**
     * Relationship to TeachingAssignment.
     */
    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    /**
     * Relationship to Partial.
     */
    public function partial(): BelongsTo
    {
        return $this->belongsTo(Partial::class);
    }

    /**
     * Relationship to Grades.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Scope to filter active activities.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter activities for a specific teaching assignment.
     */
    public function scopeForAssignment(Builder $query, $assignment): Builder
    {
        $id = $assignment instanceof TeachingAssignment ? $assignment->id : $assignment;

        return $query->where('teaching_assignment_id', $id);
    }

    /**
     * Scope to filter activities for a specific partial.
     */
    public function scopeForPartial(Builder $query, $partial): Builder
    {
        $id = $partial instanceof Partial ? $partial->id : $partial;

        return $query->where('partial_id', $id);
    }
}
