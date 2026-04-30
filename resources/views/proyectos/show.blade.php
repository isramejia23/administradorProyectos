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
        <a href="{{ auth()->user()->canany(['ver-trabajo', 'ver-trabajo-departamento']) ? route('proyectos.index') : route('proyectos.mis') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="page-title mb-0">
            <i class="bi bi-kanban-fill title-icon"></i>
            Proyecto #{{ $proyecto->id }} - {{ $proyecto->servicio?->nombre_servicio ?? 'Sin servicio' }}
        </h4>
        <div class="ms-auto d-flex gap-2">
            @can('aceptar', $proyecto)
            <form action="{{ route('proyectos.aceptar', $proyecto->id) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="bi bi-person-check-fill me-1"></i> Aceptar Proyecto
                </button>
            </form>
            @endcan
            @can('asignarResponsable', $proyecto)
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignarResponsable">
                <i class="bi bi-person-fill-add me-1"></i> Asignar Responsable
            </button>
            @endcan
            @can('gestionar', $proyecto)
            <button type="button" class="btn btn-outline-secondary btn-sm"
                    onclick="editarTramite('{{ $proyecto->numero_tramite ?? '' }}')">
                <i class="bi bi-hash me-1"></i> N° Trámite
            </button>
            @endcan
            @if($proyecto->estado_trabajo !== 'terminado' && $proyecto->estado_trabajo !== 'cancelado')
            @can('gestionar', $proyecto)
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGestionar">
                <i class="bi bi-pencil-square me-1"></i> Actualizar
            </button>
            @php $subtrabajosActivos = $proyecto->subtrabajos->whereNotIn('estado', ['terminado', 'cancelado'])->count(); @endphp
            <button type="button" class="btn btn-success btn-sm"
                    form="formFinalizarProyecto"
                    onclick="confirmarFinalizarProyecto(event, this)">
                <i class="bi bi-check2-all me-1"></i> Finalizar
            </button>
            @endcan
            @endif
            @can('update', $proyecto)
            <a href="{{ route('proyectos.edit', $proyecto->id) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            @endcan
        </div>
    </div>

    <div class="row g-3">

        {{-- ══ COLUMNA IZQUIERDA: detalles del proyecto ══ --}}
        <div class="col-lg-7">

            <div class="row g-3">
                {{-- Cliente --}}
                <div class="col-12">
                    <div class="bg-white rounded shadow-sm p-3 card-section">
                        <h6 class="card-section-title"><i class="bi bi-person-vcard me-1"></i> Cliente</h6>
                        <div class="row g-2">
                            <div class="col-md-6 border-end">
                                <p class="mb-1 fw-bold text-dark">{{ $proyecto->cliente->nombre_completo }}</p>
                                <p class="mb-0 small"><i class="bi bi-envelope me-1 text-muted"></i>{{ $proyecto->cliente->email_cliente ?? '—' }}</p>
                            </div>
                            <div class="col-md-6 ps-md-3">
                                <p class="mb-1 text-muted small"><i class="bi bi-credit-card me-1"></i>{{ $proyecto->cliente->identificacion_clientes }}</p>
                                <p class="mb-0 small"><i class="bi bi-telephone me-1 text-muted"></i>{{ $proyecto->cliente->celular_clientes ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Servicio --}}
                <div class="col-12">
                    <div class="bg-white rounded shadow-sm p-3 card-section">
                        <h6 class="card-section-title d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-briefcase me-1"></i> Servicio</span>
                            @can('ver-historial-trabajo')
                            <button type="button"
                                    class="btn btn-sm py-0 px-2"
                                    style="font-size:.72rem;background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.25);"
                                    data-bs-toggle="modal" data-bs-target="#modalHistorial">
                                <i class="bi bi-clock-history me-1"></i> Historial
                            </button>
                            @endcan
                        </h6>
                        <p class="mb-1 fw-bold text-dark">{{ $proyecto->servicio->nombre_servicio }}</p>
                        <p class="mb-1 small"><i class="bi bi-building me-1 text-muted"></i><strong>{{ $proyecto->departamento->nombre_departamento }}</strong></p>
                        @if($proyecto->numero_tramite)
                        <p class="mb-1 small"><i class="bi bi-hash me-1 text-muted"></i><span class="text-muted">N° Trámite:</span> <strong>{{ $proyecto->numero_tramite }}</strong></p>
                        @endif
                        <hr class="my-2">
                        <div class="row g-2">
                            <div class="col-md-6 border-end">
                                <p class="mb-2 small d-flex align-items-center">
                                    <span class="text-muted me-2"><i class="bi bi-activity me-1"></i>Estado del proyecto:</span>
                                    <span class="badge bg-{{ $proyecto->estado_badge }}">{{ $proyecto->estado_label }}</span>
                                </p>
                                <p class="mb-0 small">
                                    <span class="text-muted"><i class="bi bi-tag me-1"></i>Vendedor:</span>
                                    @if($proyecto->vendedor)
                                        <span class="fw-semibold text-dark">{{ $proyecto->vendedor->nombre_completo }}</span>
                                    @else
                                        <span class="text-dark fw-semibold">Sin vendedor</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 ps-md-3">
                                <div class="mb-2 small d-flex align-items-center gap-2">
                                    <span class="text-muted"><i class="bi bi-exclamation-triangle me-1"></i>Nivel de urgencia:</span>
                                    @include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])
                                </div>
                                <p class="mb-0 small">
                                    <span class="text-muted"><i class="bi bi-person-gear me-1"></i>Responsable:</span>
                                    @if($proyecto->responsable)
                                        <span class="fw-semibold text-dark">{{ $proyecto->responsable->nombre_completo }}</span>
                                    @else
                                        <span style="color:#fd7e14;" class="fw-semibold">Sin responsable</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Fechas y Monto --}}
                <div class="col-12">
                    <div class="bg-white rounded shadow-sm p-4 card-section">
                        <h6 class="card-section-title"><i class="bi bi-calendar3 me-1"></i> Fechas y Monto</h6>
                        <div class="row g-2 text-center">
                            <div class="col">
                                <p class="text-muted small mb-0"><i class="bi bi-plus-circle me-1"></i>Creación</p>
                                <p class="fw-bold mb-0 small">{{ $proyecto->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col">
                                <p class="text-muted small mb-0"><i class="bi bi-calendar-event me-1"></i>Inicio</p>
                                <p class="fw-bold mb-0 small">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                            <div class="col">
                                <p class="text-muted small mb-0"><i class="bi bi-calendar-check me-1"></i>Estimada</p>
                                <p class="fw-bold mb-0 small">{{ $proyecto->fecha_estimada?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                            <div class="col">
                                <p class="text-muted small mb-0"><i class="bi bi-calendar-x me-1"></i>Finalización</p>
                                <p class="fw-bold mb-0 small">{{ $proyecto->fecha_fin?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                            <div class="col">
                                <p class="text-muted small mb-0"><i class="bi bi-cash-coin me-1"></i>Monto Total</p>
                                <p class="fw-bold fs-5 mb-0">${{ number_format($proyecto->monto_total, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="col-md-6">
                    <div class="bg-white rounded shadow-sm p-4 h-100 card-section">
                        <h6 class="card-section-title"><i class="bi bi-chat-left-text me-1"></i> Razón / Motivo</h6>
                        <p class="mb-0 small">{{ $proyecto->razon ?? '—' }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-white rounded shadow-sm p-4 h-100 card-section">
                        <h6 class="card-section-title"><i class="bi bi-check2-square me-1"></i> Resultado Esperado</h6>
                        <p class="mb-2 small">{{ $proyecto->resultado_esperado ?? '—' }}</p>
                        <hr class="my-2">
                        <p class="mb-0 small"><span class="text-muted">Acuerdo de pagos:</span> {{ $proyecto->acuerdo_pagos ?? '—' }}</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- ══ COLUMNA DERECHA: subtrabajos ══ --}}
        <div class="col-lg-5">
            <div class="bg-white rounded shadow-sm card-section h-100">
                <div class="d-flex align-items-center justify-content-between" style="background:linear-gradient(90deg,#1a1a1a,#2d2d2d);padding:.6rem 1rem;border-radius:.375rem .375rem 0 0;">
                    <span style="color:#fff;font-size:.8rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;">
                        Subtrabajos ({{ $proyecto->subtrabajos->count() }})
                    </span>
                    @can('create', [App\Models\Subtrabajo::class, $proyecto])
                    <button type="button" class="btn btn-primary btn-sm py-0 px-2" data-bs-toggle="modal" data-bs-target="#modalSubtrabajo">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                    @endcan
                </div>

                <div class="p-3">
                    @forelse($proyecto->subtrabajos as $sub)
                    <div class="border rounded p-3 mb-2 position-relative">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1 small"><i class="bi bi-building me-1 text-muted"></i><strong>{{ $sub->departamento->nombre_departamento }}</strong></p>
                                @if($sub->servicio)
                                <p class="mb-1 small"><i class="bi bi-briefcase me-1 text-muted"></i><strong>{{ $sub->servicio->nombre_servicio }}</strong></p>
                                @endif
                                <p class="mb-1 small text-muted">
                                    <i class="bi bi-person me-1"></i>
                                    {{ $sub->responsable?->nombre_completo ?? 'Sin asignar' }}
                                </p>
                                @if($sub->observaciones)
                                <p class="mb-1 small">{{ $sub->observaciones }}</p>
                                @endif
                                <div class="d-flex gap-3 mt-1">
                                    <span class="small text-muted">
                                        <i class="bi bi-calendar-event me-1"></i>Inicio:
                                        <span class="fw-semibold text-dark">{{ $sub->fecha_inicio?->format('d/m/Y') ?? '—' }}</span>
                                    </span>
                                    <span class="small text-muted">
                                        <i class="bi bi-calendar-check me-1"></i>Estimada:
                                        <span class="fw-semibold text-dark">{{ $sub->fecha_estimada?->format('d/m/Y') ?? '—' }}</span>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="small text-muted">
                                        Costo:
                                    </span>
                                    <span  class="small fw-semibold">${{ number_format($sub->costo_especialista, 2) }}</span>
                                    @if($sub->precio_incluido)
                                    <span class="badge bg-success" style="font-size:.65rem;">Incluido</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex flex-column align-items-end gap-1">
                                <span class="badge bg-{{ $sub->estado_badge }} mb-1">{{ $sub->estado_label }}</span>
                                <a href="{{ route('subtrabajos.show', [$proyecto->id, $sub->id]) }}"
                                   class="btn btn-sm btn-outline-info py-0 px-2" title="Ver subtrabajo">
                                    <i class="bi bi-eye" style="font-size:.75rem;"></i> Ver Detalles
                                </a>
                                @can('ver-historial-subtrabajo')
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary py-0 px-2"
                                        style="font-size:.72rem;"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalHistorialSub{{ $sub->id }}">
                                    <i class="bi bi-clock-history" style="font-size:.75rem;"></i> Historial
                                </button>
                                @endcan
                                @can('aceptar', $sub)
                                <form action="{{ route('subtrabajos.aceptar', [$proyecto->id, $sub->id]) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Aceptar subtrabajo">
                                        <i class="bi bi-person-check me-1"></i> Aceptar
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4 small">
                        <i class="bi bi-diagram-3 d-block mb-1" style="font-size:1.5rem;"></i>
                        No hay subtrabajos registrados.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ══ MODAL AGREGAR SUBTRABAJO ══ --}}
{{-- ══ MODAL ACTUALIZAR PROGRESO ══ --}}
{{-- ══ MODALES HISTORIAL SUBTRABAJO ══ --}}
@can('ver-historial-subtrabajo')
@php
    $iconosSub = [
        'estado'         => ['bi-activity',       '#0d6efd'],
        'responsable_id' => ['bi-person-gear',    '#6f42c1'],
        'fecha_inicio'   => ['bi-calendar-event', '#198754'],
        'fecha_estimada' => ['bi-calendar-check', '#fd7e14'],
        'fecha_fin'      => ['bi-calendar-x',     '#dc3545'],
    ];
@endphp
@foreach($proyecto->subtrabajos as $sub)
<div class="modal fade" id="modalHistorialSub{{ $sub->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a1a1a;">
                <h6 class="modal-title text-white fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Historial — Sub #{{ $sub->id }}
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @forelse($sub->historial as $entrada)
                @php [$icono, $color] = $iconosSub[$entrada->campo] ?? ['bi-pencil', '#6c757d']; @endphp
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
@endforeach
@endcan

{{-- ══ MODAL HISTORIAL ══ --}}
<div class="modal fade" id="modalHistorial" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a1a1a;">
                <h6 class="modal-title text-white fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Historial de Cambios
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @php
                    $iconos = [
                        'estado_trabajo' => ['bi-activity',       '#0d6efd'],
                        'responsable_id' => ['bi-person-gear',    '#6f42c1'],
                        'fecha_inicio'   => ['bi-calendar-event', '#198754'],
                        'fecha_estimada' => ['bi-calendar-check', '#fd7e14'],
                        'fecha_fin'      => ['bi-calendar-x',     '#dc3545'],
                    ];
                @endphp
                @forelse($historial as $entrada)
                @php [$icono, $color] = $iconos[$entrada->campo] ?? ['bi-pencil', '#6c757d']; @endphp
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

<form id="formFinalizarProyecto" action="{{ route('proyectos.finalizar', $proyecto->id) }}" method="POST" class="d-none">
    @csrf @method('PATCH')
</form>

@can('gestionar', $proyecto)
<div class="modal fade" id="modalGestionar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('proyectos.gestionar', $proyecto->id) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header" style="background:#1a1a1a;">
                    <h6 class="modal-title text-white fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Actualizar Progreso
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Estado <span class="text-danger">*</span></label>
                            <select name="estado_trabajo" class="form-select form-select-sm" required>
                                <option value="pendiente"  {{ $proyecto->estado_trabajo === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                                <option value="proceso"    {{ $proyecto->estado_trabajo === 'proceso'    ? 'selected' : '' }}>En Proceso</option>
                                <option value="cancelado"  {{ $proyecto->estado_trabajo === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Razón / Motivo</label>
                            <textarea name="razon" class="form-control form-control-sm" rows="3">{{ $proyecto->razon }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Resultado Esperado</label>
                            <textarea name="resultado_esperado" class="form-control form-control-sm" rows="3">{{ $proyecto->resultado_esperado }}</textarea>
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

@can('create', [App\Models\Subtrabajo::class, $proyecto])
<div class="modal fade" id="modalSubtrabajo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('subtrabajos.store', $proyecto->id) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#1a1a1a;">
                    <h6 class="modal-title text-white fw-semibold">
                        <i class="bi bi-plus-circle me-1"></i> Nuevo Subtrabajo
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento_id" id="sub-depto" class="form-select form-select-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach($departamentos as $d)
                                <option value="{{ $d->id }}">{{ $d->nombre_departamento }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Servicio</label>
                            <select name="servicio_id" id="sub-servicio" class="form-select form-select-sm">
                                <option value="">Seleccione un departamento primero...</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Responsable <span class="text-muted">(opcional)</span></label>
                            <select name="responsable_id" id="sub-responsable" class="form-select form-select-sm">
                                <option value="">Sin asignar — quedará en bandeja</option>
                            </select>
                            <div class="form-text">Seleccione primero el departamento.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">N° Trámite</label>
                            <input type="text" name="numero_tramite" class="form-control form-control-sm"
                                   maxlength="20" placeholder="Máx. 20 caracteres">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Costo Especialista</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="costo_especialista" class="form-control" value="0" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="precio_incluido" id="precio_incluido" value="1">
                                <label class="form-check-label small" for="precio_incluido">
                                    Precio incluido en el proyecto
                                </label>
                            </div>
                        </div>
                        <input type="hidden" name="estado" value="pendiente">
                        <div class="col-md-4">
                            <label class="form-label small">Estado</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="Pendiente" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Fecha Estimada</label>
                            <input type="date" name="fecha_estimada" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Observaciones</label>
                            <textarea name="observaciones" class="form-control form-control-sm" rows="2"
                                      placeholder="Descripción o notas del subtrabajo..."></textarea>
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

@can('asignarResponsable', $proyecto)
<div class="modal fade" id="modalAsignarResponsable" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('proyectos.asignarResponsable', $proyecto->id) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header" style="background:#1a1a1a;">
                    <h6 class="modal-title text-white fw-semibold">
                        <i class="bi bi-person-fill-add me-1"></i> Asignar Responsable
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Selecciona el usuario que será responsable de este proyecto.
                        Solo se muestran usuarios activos del departamento
                        <strong>{{ $proyecto->departamento->nombre_departamento }}</strong>.
                    </p>
                    <label class="form-label small">Responsable <span class="text-danger">*</span></label>
                    <input type="text" id="buscarResponsable" class="form-control form-control-sm mb-2"
                           placeholder="Buscar por nombre...">
                    <select name="responsable_id" id="selectResponsable"
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

@endsection

{{-- Form oculto para N° Trámite del proyecto --}}
@can('gestionar', $proyecto)
<form id="formTramiteProyecto" action="{{ route('proyectos.tramite', $proyecto->id) }}" method="POST" class="d-none">
    @csrf @method('PATCH')
    <input type="hidden" name="numero_tramite" id="inputTramiteProyecto">
</form>
@endcan

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
const subtrabajosActivos = {{ $subtrabajosActivos ?? 0 }};

function confirmarFinalizarProyecto(event, button) {
    event.preventDefault();
    const swalMixin = Swal.mixin({
        customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-danger' },
        buttonsStyling: false
    });

    if (subtrabajosActivos > 0) {
        swalMixin.fire({
            icon: 'warning',
            title: 'Subtrabajos sin finalizar',
            text: `Hay ${subtrabajosActivos} ${subtrabajosActivos === 1 ? 'subtrabajo' : 'subtrabajos'} que no están finalizados o cancelados. Resuélvelos antes de finalizar el proyecto.`,
            showCancelButton: false,
            confirmButtonText: 'Entendido',
        });
        return;
    }

    swalMixin.fire({
        icon: 'warning',
        title: 'Finalizar proyecto',
        text: 'Se marcará el proyecto como terminado con la fecha de hoy.',
        showCancelButton: true,
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) button.closest('form').submit();
    });
}

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
            document.getElementById('inputTramiteProyecto').value = result.value ?? '';
            document.getElementById('formTramiteProyecto').submit();
        }
    });
}
const subServicios = @json($servicios->map(fn($s) => ['id' => $s->id, 'text' => $s->nombre_servicio, 'depto' => $s->departamento_id]));
const subUsuarios  = @json($usuarios->map(fn($u) => ['id' => $u->id, 'text' => $u->nombre_completo, 'depto' => $u->departamento_id]));

const tsModalOpts = {
    allowEmptyOption: true,
    dropdownParent: 'body',
    openOnFocus: true,
    selectOnTab: true,
    maxOptions: null,
};

// Tom Select para servicio y responsable (se cargan dinámicamente)
const servicioTs = new TomSelect('#sub-servicio', {
    ...tsModalOpts,
    placeholder: 'Seleccione un departamento primero...',
    options: [],
    items: [],
});

const responsableTs = new TomSelect('#sub-responsable', {
    ...tsModalOpts,
    placeholder: 'Sin asignar — quedará en bandeja',
    options: [{ value: '', text: 'Sin asignar — quedará en bandeja' }],
    items: [''],
});

// El departamento es un select nativo (pocos ítems, no necesita búsqueda)
document.getElementById('sub-depto').addEventListener('change', function () {
    const deptoId = this.value;

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

    servicioTs.setValue('');
    responsableTs.setValue('');
    servicioTs.refreshOptions(false);
    responsableTs.refreshOptions(false);
});

// Limpiar al cerrar el modal
document.getElementById('modalSubtrabajo')?.addEventListener('hidden.bs.modal', function () {
    document.getElementById('sub-depto').value = '';
    servicioTs.clear(); servicioTs.clearOptions();
    responsableTs.clear(); responsableTs.clearOptions();
    responsableTs.addOption({ value: '', text: 'Sin asignar — quedará en bandeja' });
    responsableTs.setValue('');
});

// Búsqueda en vivo en el modal de asignar responsable del proyecto
document.getElementById('buscarResponsable')?.addEventListener('input', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#selectResponsable option').forEach(opt => {
        opt.hidden = !opt.textContent.toLowerCase().includes(term);
    });
});
</script>
@endpush
