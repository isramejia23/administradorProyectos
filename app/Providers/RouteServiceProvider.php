<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Este namespace se aplica a las rutas del controlador.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Definir las rutas para la aplicación.
     *
     * @return void
     */
    public function boot()
    {
        // No es necesario llamar a parent::boot() en versiones recientes
        $this->routes(function () {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));
        });
        Route::resource('roles', RolController::class);
    }
}
