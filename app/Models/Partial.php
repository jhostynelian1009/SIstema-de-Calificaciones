<?php

namespace App\Models;

use Database\Factories\PartialFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partial extends Model
{
    /** @use HasFactory<PartialFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'academic_period_id',
        'number',
        'name',
        'weight',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'weight' => 'decimal:2',
        ];
    }

    /**
     * Get the academic period that owns the partial.
     */
    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    /**
     * Get the partial publication states for this partial.
     */
    public function partialPublications(): HasMany
    {
        return $this->hasMany(PartialPublication::class);
    }
}
