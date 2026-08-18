<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Sensitive fields that must never be recorded in audit logs.
     */
    protected array $hiddenFields = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'secret',
        'api_key',
    ];

    /**
     * Log an audit action on a model or entity.
     */
    public function log(string $action, Model $auditable, ?array $oldValues = null, ?array $newValues = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->getKey(),
            'old_values' => $oldValues ? $this->sanitize($oldValues) : null,
            'new_values' => $newValues ? $this->sanitize($newValues) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Sanitize array values to remove sensitive keys.
     */
    protected function sanitize(array $values): array
    {
        foreach ($this->hiddenFields as $field) {
            unset($values[$field]);
        }

        return $values;
    }
}
