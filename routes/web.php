<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TrabajoController;
use App\Http\Controllers\SubtrabajoController;
use App\Http\Controllers\AccionController;
use App\Http\Controllers\PropiedadController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CuentaCobrarController;
use App\Http\Controllers\PagoController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return view('welcome');
});

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::resource('roles', RolController::class);
    Route::resource('usuarios', UsuarioController::class);
    Route::resource('departamentos', DepartamentoController::class);
    Route::resource('servicios', ServicioController::class)->except(['create', 'edit', 'show']);
    Route::get('proyectos/bandeja',              [TrabajoController::class, 'bandeja'])->name('proyectos.bandeja');
    Route::get('proyectos/mis-proyectos',        [TrabajoController::class, 'misProyectos'])->name('proyectos.mis');
    Route::get('proyectos/mis-ventas',           [TrabajoController::class, 'misVentas'])->name('proyectos.ventas');
    Route::get('proyectos/vendedores',           [TrabajoController::class, 'vendedores'])->name('proyectos.vendedores');
    Route::get('proyectos/solicitudes',          [TrabajoController::class, 'solicitudes'])->name('proyectos.solicitudes');
    Route::get('proyectos/{proyecto}/aprobar',   [TrabajoController::class, 'mostrarAprobar'])->name('proyectos.aprobar');
    Route::post('proyectos/{proyecto}/aprobar',  [TrabajoController::class, 'confirmarAprobacion'])->name('proyectos.confirmarAprobacion');
    Route::patch('proyectos/{proyecto}/rechazar',[TrabajoController::class, 'rechazar'])->name('proyectos.rechazar');
    Route::patch('proyectos/{proyecto}/aceptar',                        [TrabajoController::class,    'aceptar'])->name('proyectos.aceptar');
    Route::patch('proyectos/{proyecto}/asignar-responsable',            [TrabajoController::class,    'asignarResponsable'])->name('proyectos.asignarResponsable');
    Route::patch('proyectos/{proyecto}/gestionar',                      [TrabajoController::class,    'gestionar'])->name('proyectos.gestionar');
    Route::patch('proyectos/{proyecto}/finalizar',                      [TrabajoController::class,    'finalizar'])->name('proyectos.finalizar');
    Route::patch('proyectos/{proyecto}/tramite',                        [TrabajoController::class,    'actualizarTramite'])->name('proyectos.tramite');
    Route::patch('proyectos/{proyecto}/comision',                       [TrabajoController::class,    'actualizarComision'])->name('proyectos.comision');
    Route::patch('proyectos/{proyecto}/asignar-vendedor',               [TrabajoController::class,    'asignarVendedor'])->name('proyectos.asignarVendedor');
    Route::get('subtrabajos',                                                [SubtrabajoController::class, 'index'])->name('subtrabajos.index');
    Route::get('subtrabajos/mis-subtrabajos',                                [SubtrabajoController::class, 'misSubtrabajos'])->name('subtrabajos.mis');
    Route::get('proyectos/{proyecto}/subtrabajos/{subtrabajo}',              [SubtrabajoController::class, 'show'])->name('subtrabajos.show');
    Route::put('proyectos/{proyecto}/subtrabajos/{subtrabajo}',              [SubtrabajoController::class, 'update'])->name('subtrabajos.update');
    Route::post('proyectos/{proyecto}/subtrabajos',                          [SubtrabajoController::class, 'store'])->name('subtrabajos.store');
    Route::patch('proyectos/{proyecto}/subtrabajos/{subtrabajo}/aceptar',    [SubtrabajoController::class, 'aceptar'])->name('subtrabajos.aceptar');
    Route::patch('proyectos/{proyecto}/subtrabajos/{subtrabajo}/finalizar',          [SubtrabajoController::class, 'finalizar'])->name('subtrabajos.finalizar');
    Route::patch('proyectos/{proyecto}/subtrabajos/{subtrabajo}/asignar-responsable', [SubtrabajoController::class, 'asignarResponsable'])->name('subtrabajos.asignarResponsable');
    Route::patch('proyectos/{proyecto}/subtrabajos/{subtrabajo}/tramite',             [SubtrabajoController::class, 'actualizarTramite'])->name('subtrabajos.tramite');
    Route::post('proyectos/{proyecto}/subtrabajos/{subtrabajo}/acciones',                       [AccionController::class, 'store'])->name('acciones.store');
    Route::patch('proyectos/{proyecto}/subtrabajos/{subtrabajo}/acciones/{accion}',            [AccionController::class, 'update'])->name('acciones.update');
    Route::resource('proyectos', TrabajoController::class);
    Route::get('clientes/consultar/{identificacion}', [ClienteController::class, 'consultar'])->name('clientes.consultar');
    Route::resource('clientes', ClienteController::class)->except(['create', 'edit']);

    // ── Cobros ────────────────────────────────────────────────
    Route::get('cobros',                                    [CuentaCobrarController::class, 'index'])->name('cobros.index');
    Route::get('cobros/{cobro}',                            [CuentaCobrarController::class, 'show'])->name('cobros.show');
    Route::patch('cobros/{cobro}',                          [CuentaCobrarController::class, 'update'])->name('cobros.update');
    Route::post('cobros/{cobro}/pagos',                     [PagoController::class, 'store'])->name('cobros.pagos.store');
    Route::patch('cobros/{cobro}/pagos/{pago}',             [PagoController::class, 'update'])->name('cobros.pagos.update');
    Route::patch('cobros/{cobro}/pagos/{pago}/anular',      [PagoController::class, 'anular'])->name('cobros.pagos.anular');

    Route::delete('propiedades/{propiedad}/fotos/{foto}',        [PropiedadController::class, 'destroyFoto'])->name('propiedades.fotos.destroy');
    Route::patch('propiedades/{propiedad}/fotos/{foto}/portada', [PropiedadController::class, 'setPortada'])->name('propiedades.fotos.portada');
    Route::resource('propiedades', PropiedadController::class)->parameters(['propiedades' => 'propiedad']);
});

// Catálogo público (sin auth) — listo para subdomain cuando configures DNS
Route::prefix('catalogo')->name('catalogo.')->group(function () {
    Route::get('/',             [CatalogoController::class, 'index'])->name('index');
    Route::get('/{propiedad}',  [CatalogoController::class, 'show'])->name('show')->where('propiedad', '[0-9]+');
});
