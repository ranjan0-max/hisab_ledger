<?php

namespace App\Traits;

use App\Scopes\TenantScope;

trait BelongsToClient
{
    /**
     * Boot the trait and register the TenantScope and auto-assignment.
     */
    protected static function bootBelongsToClient(): void
    {
        static::addGlobalScope(new TenantScope());

        // Automatically assign client_id when creating new records if not explicitly set
        static::creating(function ($model) {
            if (auth()->check() && empty($model->client_id)) {
                $user = auth()->user();
                if (!$user->isSuperAdmin()) {
                    $model->client_id = $user->client_id;
                } else if (session()->has('active_client_id')) {
                    $model->client_id = session('active_client_id');
                }
            }
        });
    }

    /**
     * Get the client that owns the model.
     */
    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id');
    }
}
