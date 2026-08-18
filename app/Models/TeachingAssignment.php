<?php

namespace App\Models;

use Database\Factories\TeachingAssignmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeachingAssignment extends Model
{
    /** @use HasFactory<TeachingAssignmentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'teacher_id',
        'course_id',
        'subject_id',
        'academic_period_id',
        'active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    /**
     * Get the teacher user for this assignment.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the course for this assignment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the subject for this assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the academic period for this assignment.
     */
    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    /**
     * Get the partial publication states for this assignment.
     */
    public function partialPublications(): HasMany
    {
        return $this->hasMany(PartialPublication::class);
    }

    /**
     * Scope query to active teaching assignments.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope query to assignments for a specific academic period.
     */
    public function scopeForPeriod(Builder $query, $periodId): Builder
    {
        return $query->where('academic_period_id', $periodId);
    }

    /**
     * Scope query to assignments for a specific course.
     */
    public function scopeForCourse(Builder $query, $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope query to assignments assigned to a specific teacher user or ID.
     */
    public function scopeAssignedTo(Builder $query, $teacher): Builder
    {
        $teacherId = $teacher instanceof User ? $teacher->id : $teacher;

        return $query->where('teacher_id', $teacherId);
    }
}
