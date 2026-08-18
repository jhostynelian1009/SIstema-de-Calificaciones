<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Reopened = 'reopened';

    /**
     * Get human-readable Spanish label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Published => 'Publicado',
            self::Reopened => 'Reabierto',
        };
    }

    /**
     * Get Bootstrap badge class for UI styling.
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-secondary',
            self::Published => 'bg-success',
            self::Reopened => 'bg-warning text-dark',
        };
    }
}
