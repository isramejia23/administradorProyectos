<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use App\Models\Trabajo;
use App\Models\Subtrabajo;
use App\Models\Pago;
use App\Observers\TrabajoObserver;
use App\Observers\SubtrabajoObserver;
use App\Observers\PagoObserver;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        Carbon::setLocale('es');

        Trabajo::observe(TrabajoObserver::class);
        Subtrabajo::observe(SubtrabajoObserver::class);
        Pago::observe(PagoObserver::class);
    }
}
