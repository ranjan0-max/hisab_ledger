<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public static function log(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void
    {
        $user = auth()->user();
        $request = request();

        AuditLog::create([
            'client_id'   => $user?->client_id,
            'user_id'     => $user?->id,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => $request?->ip(),
        ]);
    }
}
