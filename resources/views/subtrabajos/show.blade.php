@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper.form-control, .ts-wrapper.form-select { padding: 0; }
    .ts-control { border: 1px solid #dee2e6 !important; border-radius: .375rem !important; font-size: .875rem; min-height: 31px; }
    .ts-dropdown { font-size: .875rem; z-index: 9999 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    {{-- Cabecera --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('proyectos.show', $proyecto->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Ver Proyecto
        </a>
        <h4 class="page-title mb-0">
            <i class="bi bi-diagram-3-fill title-icon"></i>
            Subtrabajo #{{ $subtrabajo->id }} - {{ $subtrabajo->servicio?->nombre_servicio ?? 'Sin servicio' }}
        </h4>
        <span class="badge bg-{{ $subtrabajo->estado_badge }} ms-1">{{ $subtrabajo->estado_label }}</span>
        @can('aceptar', $subtrabajo)
        <form action="{{ route('subtrabajos.aceptar', [$proyecto->id, $subtrabajo->id]) }}" method="POST" class="ms-1">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-success btn-sm">
                <i class="bi bi-person-check-fill me-1"></i> Aceptar
            </button>
        </form>
        @endcan
        @can('asignarResponsable', $subtrabajo)
        <button type="button" class="btn btn-primary btn-sm ms-1"
                data-bs-toggle="modal" data-bs-target="#modalAsignarResponsable">
            <i class="bi bi-person-fill-add me-1"></i> Asignar Responsable
        </button>
        @endcan
        <form id="formTramite" action="{{ route('subtrabajos.tramite', [$proyecto->id, $subtrabajo->id]) }}" method="POST" class="ms-auto d-flex gap-2">
            @csrf @method('PATCH')
            <input type="hidden" name="numero_tramite" id="inputTramite">
            @if($subtrabajo->estado !== 'terminado' && $subtrabajo->estado !== 'cancelado')
            @can('update', $subtrabajo)
            <button type="button" class="btn btn-outline-secondary btn-sm"
                    onclick="editarTramite('{{ $subtrabajo->numero_tramite ?? '' }}')">
                <i class="bi bi-hash me-1"></i> N° Trámite
            </button>
            @endcan
            @can('finalizar', $subtrabajo)
            <button type="button" class="btn btn-success btn-sm"
                    form="formFinalizar"
                    onclick="confirmAction(event, this, {
                        title: 'Finalizar subtrabajo',
                        text: 'Se guardará la fecha de finalización y no podrá editarse.',
                        confirmButtonText: 'Sí, finalizar',
                        formId: 'formFinalizar'
                    })">
                <i class="bi bi-check2-all me-1"></i> Finalizar Subtrabajo
            </button>
            @endcan
            @endif
            @can('update', $subtrabajo)
            <button type="button" class="btn btn-primary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalEditarSubtrabajo">
                <i class="bi bi-pencil-square me-1"></i> Editar Todo
            </button>
            @endcan
        </form>
        <form id="formFinalizar" action="{{ route('subtrabajos.finalizar', [$proyecto->id, $subtrabajo->id]) }}" method="POST" class="d-none">
            @csrf @method('PATCH')
        </form>
    </div>

    <div class="row g-3">

        {{-- ══ COLUMNA IZQUIERDA ══ --}}
        <div class="col-lg-7">
            <div class="row g-3">

                {{-- Resumen del proyecto padre --}}
                <div class="col-12">
                    <div class="bg-white rounded shadow-sm p-4 card-section">
                        <h6 class="card-section-title"><i class="bi bi-kanban me-1"></i> Proyecto Principal #{{ $proyecto->id }}</h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Cliente</p>
                                <p class="mb-0 fw-bold">{{ $proyecto->cliente->nombre_completo }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Servicio</p>
                                <p class="mb-0 fw-bold">{{ $proyecto->servicio->nombre_servicio }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Departamento</p>
                                <p class="mb-0 small">{{ $proyecto->departamento->nombre_departamento }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Responsable del proyecto</p>
                                <p class="mb-0 small">{{ $proyecto->responsable?->nombre_completo ?? 'Sin asignar' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted">Estado</p>
                                <span class="badge bg-{{ $proyecto->estado_badge }}">{{ $proyecto->estado_label }}</span>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted">Urgencia</p>
                                @include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted">Monto Total</p>
                                <p class="mb-0 fw-bold">${{ number_format($proyecto->monto_total, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detalle del subtrabajo --}}
                <div class="col-12">
                    <div class="bg-white rounded shadow-sm p-4 card-section">
                        <h6 class="card-section-title d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-diagram-3 me-1"></i> Detalle del Subtrabajo</span>
                            @can('ver-historial-subtrabajo')
                            <button type="button"
                                    class="btn btn-sm py-0 px-2"
                                    style="font-size:.72rem;background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);"
                                    data-bs-toggle="modal" data-bs-target="#modalHistorialSub">
                                <i class="bi bi-clock-history me-1"></i> Historial
                            </button>
                            @endcan
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Departamento</p>
                                <p class="mb-0 fw-bold"><i class="bi bi-building me-1 text-muted"></i>{{ $subtrabajo->departamento->nombre_departamento }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Servicio a realizar</p>
                                <p class="mb-0 fw-bold"><i class="bi bi-briefcase me-1 text-muted"></i>{{ $subtrabajo->servicio?->nombre_servicio ?? '—' }}</p>
                            </div>
                            @if($subtrabajo->numero_tramite)
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">N° Trámite</p>
                                <p class="mb-0 fw-bold"><i class="bi bi-hash me-1 text-muted"></i>{{ $subtrabajo->numero_tramite }}</p>
                            </div>
                            @endif
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Responsable</p>
                                <p class="mb-0 small">
                                    <i class="bi bi-person me-1 text-muted"></i>
                                    @if($subtrabajo->responsable)
                                        {{ $subtrabajo->responsable->nombre_completo }}
                                    @else
                                        <span style="color:#fd7e14;" class="fw-semibold">Sin asignar</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1 small text-muted">Costo Especialista</p>
                                <p class="mb-0 fw-bold">
                                    ${{ number_format($subtrabajo->costo_especialista, 2) }}
                                    @if($subtrabajo->precio_incluido)
                                    <span class="badge bg-success ms-1" style="font-size:.65rem;">Incluido</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted"><i class="bi bi-calendar-event me-1"></i>Inicio</p>
                                <p class="mb-0 small fw-semibold">{{ $subtrabajo->fecha_inicio?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted"><i class="bi bi-calendar-check me-1"></i>Estimada</p>
                                <p class="mb-0 small fw-semibold">{{ $subtrabajo->fecha_estimada?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1 small text-muted"><i class="bi bi-calendar-x me-1"></i>Finalización</p>
                                <p class="mb-0 small fw-semibold">{{ $subtrabajo->fecha_fin?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            @if($subtrabajo->observaciones)
                            <div class="col-12">
                                <p class="mb-1 small text-muted">Observaciones</p>
                                <p class="mb-0 small">{{ $subtrabajo->observaciones }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ══ COLUMNA DERECHA: acciones ══ --}}
        <div class="col-lg-5">

            <div class="bg-white rounded shadow-sm card-section h-100">
                <div class="d-flex align-items-center justify-content-between"
                     style="background:linear-gradient(90deg,#1a1a1a,#2d2d2d);padding:.6rem 1rem;border-radius:.375rem .375rem 0 0;">
                    <span style="color:#fff;font-size:.8rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;">
                        <i class="bi bi-list-check me-1"></i> Acciones ({{ $subtrabajo->acciones->count() }})
                    </span>
                    @if($subtrabajo->estado !== 'terminado' && $subtrabajo->estado !== 'cancelado')
                    @can('create', [App\Models\Accion::class, $subtrabajo])
                    <button type="button" class="btn btn-primary btn-sm py-0 px-2"
                            data-bs-toggle="modal" data-bs-target="#modalAccion">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                    @endcan
                    @endif
                </div>

                <div class="p-3">
                    @forelse($subtrabajo->acciones as $accion)
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1 me-2">
                                <p class="mb-1 fw-semibold small">{{ $accion->descripcion }}</p>
                                <p class="mb-1 small text-muted">
                                    <i class="bi bi-person me-1"></i>{{ $accion->usuario->nombre_completo }}
                                </p>
                                @if($accion->observacion)
                                <p class="mb-1 small text-muted fst-italic">{{ $accion->observacion }}</p>
                                @endif
                                <div class="d-flex gap-3 mt-1">
                                    @if($accion->fecha_inicio)
                                    <span class="small text-muted">
                                        <i class="bi bi-calendar-event me-1"></i>{{ $accion->fecha_inicio->format('d/m/Y') }}
                                    </span>
                                    @endif
                                    @if($accion->fecha_fin)
                                    <span class="small text-muted">
                                        <i class="bi bi-calendar-x me-1"></i>{{ $accion->fecha_fin->format('d/m/Y') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1" style="min-width:110px;">
                                <span class="badge bg-{{ $accion->estado_badge }}">{{ $accion->estado_label }}</span>
                                @if($subtrabajo->estado !== 'terminado' && $subtrabajo->estado !== 'cancelado' && $accion->estado !== 'terminado' && $accion->estado !== 'cancelado')
                                @can('update', $accion)
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary py-0 w-100 mt-1"
                                        style="font-size:.75rem;"
                                        onclick="abrirEditarAccion(
                                            {{ $accion->id }},
                                            '{{ $accion->estado }}',
                                            '{{ $accion->fecha_fin?->format('Y-m-d') ?? '' }}',
                                            {{ json_encode($accion->observacion ?? '') }}
                                        )">
                                    <i class="bi bi-pencil me-1"></i> Editar
                                </button>
                                @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-list-check d-block mb-1" style="font-size:1.5rem;"></i>
                        No hay acciones registradas.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══ MODAL AGREGAR ACCIÓN ══ --}}
@can('create', [App\Models\Accion::class, $subtrabajo])
<div class="modal fade" id="modalAccion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('acciones.store', [$proyecto->id, $subtrabajo->id]) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#1a1a1a;">
                    <h6 class="modal-title text-white fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Nueva Acción
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Descripción <span class="text-danger">*</span></label>
                            <textarea name="descripcion" class="form-control form-control-sm" rows="3"
                                      placeholder="¿Qué se realizó o se planea realizar?" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Estado <span class="text-danger">*</span></label>
                            <select name="estado" class="form-select form-select-sm" required>
                                <option value="pendiente" selected>Pendiente</option>
                                <option value="proceso">En Proceso</option>
                                <option value="terminado">Terminado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Fecha Inicio</label>
                            <input type="text" class="form-control form-control-sm bg-light"
                                   value="{{ now()->format('d/m/Y') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Observación</label>
                            <textarea name="observacion" class="form-control form-control-sm" rows="2"
                                      placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- ══ MODAL EDITAR ACCIÓN ══ --}}
@can('create', [App\Models\Accion::class, $subtrabajo])
<div class="modal fade" id="modalEditarAccion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditarAccion" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header" style="background:#1a1a1a;">
                    <h6 class="modal-title text-white fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Editar Acción
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="edit-estado" class="form-select form-select-sm" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="proceso">En Proceso</option>
                                <option value="terminado">Terminado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Fecha de Finalización</label>
                            <input type="date" name="fecha_fin" id="edit-fecha-fin" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Observación</label>
                            <textarea name="observacion" id="edit-observacion" class="form-control form-control-sm"
                                      rows="3" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

@can('asignarResponsable', $subtrabajo)
<div class="modal fade" id="modalAsignarResponsable" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('subtrabajos.asignarResponsable', [$proyecto->id, $subtrabajo->id]) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header" style="background:#1a1a1a;">
                    <h6 class="modal-title text-white fw-semibold">
                        <i class="bi bi-person-fill-add me-1"></i> Asignar Responsable
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Solo se muestran usuarios activos del departamento
                        <strong>{{ $subtrabajo->departamento->nombre_departamento }}</strong>.
                    </p>
                    <label class="form-label small">Responsable <span class="text-danger">*</span></label>
                    <input type="text" id="buscarResponsableSub" class="form-control form-control-sm mb-2"
                           placeholder="Buscar por nombre...">
                    <select name="responsable_id" id="selectResponsableSub"
                            class="form-select form-select-sm" required size="6" style="height:auto;">
                        @foreach($usuariosDepartamento as $u)
                        <option value="{{ $u->id }}">{{ $u->nombre_completo }}</option>
                        @endforeach
                    </select>
                    @if($usuariosDepartamento->isEmpty())
                    <p class="text-muted small mt-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        No hay usuarios activos en este departamento.
                    </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-check me-1"></i> Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- ══ MODAL EDITAR SUBTRABAJO GENERAL (ADMIN) ══ --}}
@can('update', $subtrabajo)
<div class="modal fade" id="modalEditarSubtrabajo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('subtrabajos.update', [$proyecto->id, $subtrabajo->id]) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header" style="background:#1a1a1a;">
                    <h6 class="modal-title text-white fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Editar Subtrabajo
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento_id" id="edit-sub-depto" class="form-select form-select-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach($departamentos as $d)
                                <option value="{{ $d->id }}" {{ $subtrabajo->departamento_id == $d->id ? 'selected' : '' }}>{{ $d->nombre_departamento }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Servicio</label>
                            <select name="servicio_id" id="edit-sub-servicio" class="form-select form-select-sm">
                                <option value="">Seleccione un departamento primero...</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Responsable <span class="text-muted">(opcional)</span></label>
                            <select name="responsable_id" id="edit-sub-responsable" class="form-select form-select-sm">
                                <option value="">Sin asignar — quedará en bandeja</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">N° Trámite</label>
                            <input type="text" name="numero_tramite" class="form-control form-control-sm"
                                   maxlength="20" placeholder="Máx. 20 caracteres" value="{{ $subtrabajo->numero_tramite }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Costo Especialista</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="costo_especialista" class="form-control" value="{{ $subtrabajo->costo_especialista }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="precio_incluido" id="edit_precio_incluido" value="1" {{ $subtrabajo->precio_incluido ? 'checked' : '' }}>
                                <label class="form-check-label small" for="edit_precio_incluido">
                                    Precio incluido
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Estado</label>
                            <select name="estado" class="form-select form-select-sm" required>
                                <option value="pendiente" {{ $subtrabajo->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="proceso" {{ $subtrabajo->estado == 'proceso' ? 'selected' : '' }}>En Proceso</option>
                                <option value="terminado" {{ $subtrabajo->estado == 'terminado' ? 'selected' : '' }}>Terminado</option>
                                <option value="cancelado" {{ $subtrabajo->estado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="{{ $subtrabajo->fecha_inicio?->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Fecha Estimada</label>
                            <input type="date" name="fecha_estimada" class="form-control form-control-sm" value="{{ $subtrabajo->fecha_estimada?->format('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Observaciones</label>
                            <textarea name="observaciones" class="form-control form-control-sm" rows="2"
                                      placeholder="Descripción o notas del subtrabajo...">{{ $subtrabajo->observaciones }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

{{-- ══ MODAL HISTORIAL SUBTRABAJO ══ --}}
@can('ver-historial-subtrabajo')
@php
    $iconosHist = [
        'estado'         => ['bi-activity',       '#0d6efd'],
        'responsable_id' => ['bi-person-gear',    '#6f42c1'],
        'fecha_inicio'   => ['bi-calendar-event', '#198754'],
        'fecha_estimada' => ['bi-calendar-check', '#fd7e14'],
        'fecha_fin'      => ['bi-calendar-x',     '#dc3545'],
    ];
@endphp
<div class="modal fade" id="modalHistorialSub" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a1a1a;">
                <h6 class="modal-title text-white fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Historial — Subtrabajo #{{ $subtrabajo->id }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @forelse($historial as $entrada)
                @php [$icono, $color] = $iconosHist[$entrada->campo] ?? ['bi-pencil', '#6c757d']; @endphp
                <div class="d-flex gap-3 mb-3">
                    <div class="flex-shrink-0 pt-1">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:28px;height:28px;background:{{ $color }}1a;">
                            <i class="bi {{ $icono }}" style="font-size:.75rem;color:{{ $color }};"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-semibold small">{{ \App\Models\Historial::labelCampo($entrada->campo) }}</span>
                            @if($entrada->valor_anterior)
                            <span class="badge" style="background:#f0f0f0;color:#555;font-size:.68rem;">{{ $entrada->valor_anterior }}</span>
                            <i class="bi bi-arrow-right" style="font-size:.65rem;color:#aaa;"></i>
                            @endif
                            <span class="badge" style="background:{{ $color }}20;color:{{ $color }};font-size:.68rem;">{{ $entrada->valor_nuevo ?? '—' }}</span>
                        </div>
                        <div class="d-flex gap-2 mt-1" style="font-size:.7rem;color:#aaa;">
                            <span><i class="bi bi-person me-1"></i>{{ $entrada->usuario?->nombre_completo ?? 'Sistema' }}</span>
                            <span>·</span>
                            <span><i class="bi bi-clock me-1"></i>{{ $entrada->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">
                    <i class="bi bi-clock-history d-block mb-2" style="font-size:1.8rem;"></i>
                    Sin cambios registrados aún.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endcan

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
const baseUrl = '{{ url("proyectos/{$proyecto->id}/subtrabajos/{$subtrabajo->id}/acciones") }}';

document.getElementById('buscarResponsableSub')?.addEventListener('input', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#selectResponsableSub option').forEach(opt => {
        opt.hidden = !opt.textContent.toLowerCase().includes(term);
    });
});

function editarTramite(actual) {
    Swal.fire({
        title: 'N° de Trámite',
        input: 'text',
        inputValue: actual,
        inputAttributes: { maxlength: 20, placeholder: 'Máx. 20 caracteres' },
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-outline-secondary' },
        buttonsStyling: false,
        inputValidator(value) {
            if (value && value.length > 20) return 'Máximo 20 caracteres.';
        }
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('inputTramite').value = result.value ?? '';
            document.getElementById('formTramite').submit();
        }
    });
}

function abrirEditarAccion(accionId, estado, fechaFin, observacion) {
    document.getElementById('formEditarAccion').action  = baseUrl + '/' + accionId;
    document.getElementById('edit-estado').value        = estado;
    document.getElementById('edit-fecha-fin').value     = fechaFin;
    document.getElementById('edit-observacion').value   = observacion;
    new bootstrap.Modal(document.getElementById('modalEditarAccion')).show();
}

@can('update', $subtrabajo)
const subServicios = @json($servicios->map(fn($s) => ['id' => $s->id, 'text' => $s->nombre_servicio, 'depto' => $s->departamento_id]));
const subUsuarios  = @json($usuarios->map(fn($u) => ['id' => $u->id, 'text' => $u->nombre_completo, 'depto' => $u->departamento_id]));

const tsModalOpts = {
    allowEmptyOption: true,
    dropdownParent: 'body',
    openOnFocus: true,
    selectOnTab: true,
    maxOptions: null,
};

const servicioTs = new TomSelect('#edit-sub-servicio', {
    ...tsModalOpts,
    placeholder: 'Seleccione un departamento primero...',
    options: [],
    items: [],
});

const responsableTs = new TomSelect('#edit-sub-responsable', {
    ...tsModalOpts,
    placeholder: 'Sin asignar — quedará en bandeja',
    options: [{ value: '', text: 'Sin asignar — quedará en bandeja' }],
    items: [''],
});

function loadSubDeptLinkedFields(deptoId, currentServicioId = null, currentRespId = null) {
    servicioTs.clear(); servicioTs.clearOptions();
    responsableTs.clear(); responsableTs.clearOptions();
    responsableTs.addOption({ value: '', text: 'Sin asignar — quedará en bandeja' });

    if (deptoId) {
        subServicios
            .filter(s => String(s.depto) === String(deptoId))
            .forEach(s => servicioTs.addOption({ value: String(s.id), text: s.text }));

        subUsuarios
            .filter(u => String(u.depto) === String(deptoId))
            .forEach(u => responsableTs.addOption({ value: String(u.id), text: u.text }));
    }

    servicioTs.setValue(currentServicioId || '');
    responsableTs.setValue(currentRespId || '');
    servicioTs.refreshOptions(false);
    responsableTs.refreshOptions(false);
}

document.getElementById('edit-sub-depto').addEventListener('change', function () {
    loadSubDeptLinkedFields(this.value);
});

// Load init values
document.addEventListener('DOMContentLoaded', () => {
    loadSubDeptLinkedFields('{{ $subtrabajo->departamento_id }}', '{{ $subtrabajo->servicio_id }}', '{{ $subtrabajo->responsable_id }}');
});
@endcan
</script>
@endpush

@endsection
