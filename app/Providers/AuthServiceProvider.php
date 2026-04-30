<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Trabajo;
use App\Models\Subtrabajo;
use App\Models\Accion;
use App\Policies\TrabajoPolicy;
use App\Policies\SubtrabajoPolicy;
use App\Policies\AccionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Trabajo::class    => TrabajoPolicy::class,
        Subtrabajo::class => SubtrabajoPolicy::class,
        Accion::class     => AccionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin bypasa todas las policies
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super Admin')) return true;
        });
    }
}