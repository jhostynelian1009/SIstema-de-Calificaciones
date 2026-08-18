<?php

namespace App\Models;

use App\Enums\PublicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartialPublication extends Model
{
    use HasFactory;

    protected $fillable = [
        'teaching_assignment_id',
        'partial_id',
        'status',
        'published_by',
        'published_at',
        'reopened_by',
        'reopened_at',
        'reopen_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublicationStatus::class,
            'published_at' => 'datetime',
            'reopened_at' => 'datetime',
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
     * User who published the partial.
     */
    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    /**
     * User who reopened the partial publication.
     */
    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    /**
     * Scope for a specific status.
     */
    public function scopeWithStatus($query, PublicationStatus|string $status)
    {
        $val = $status instanceof PublicationStatus ? $status->value : $status;

        return $query->where('status', $val);
    }
}
