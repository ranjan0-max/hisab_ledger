<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the tenant global scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Non-SuperAdmin users are strictly scoped to their own client_id
            if (!$user->isSuperAdmin()) {
                if ($user->client_id) {
                    $builder->where($model->getTable() . '.client_id', $user->client_id);
                }
            } else {
                // SuperAdmin users are scoped to active_client_id if selected in session
                if (session()->has('active_client_id') && session('active_client_id')) {
                    $builder->where($model->getTable() . '.client_id', session('active_client_id'));
                }
            }
        }
    }
}
