@extends('layouts.app')

@push('styles')
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
        <span class="badge ms-1" style="background:#fff;color:#000;border:1px solid #dee2e6;font-size:.7rem;">
            <i class="bi bi-1-circle me-1"></i>Trabajo Único
        </span>

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
            @if($proyecto->estado_trabajo !== 'terminado' && $proyecto->estado_trabajo !== 'cancelado')
            @can('gestionar', $proyecto)
                @if($subtrabajoPrincipal)
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        onclick="editarTramite('{{ $subtrabajoPrincipal->numero_tramite ?? '' }}')">
                    <i class="bi bi-hash me-1"></i> N° Trámite
                </button>
                @endif
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalGestionar">
                    <i class="bi bi-pencil-square me-1"></i> Actualizar
                </button>
                @if($subtrabajoPrincipal)
                @php $accionesPendientes = $subtrabajoPrincipal->acciones->whereIn('estado', ['pendiente', 'proceso'])->count(); @endphp
                <button type="button" class="btn btn-success btn-sm"
                        form="formFinalizar"
                        onclick="confirmarFinalizar(event, this)">
                    <i class="bi bi-check2-all me-1"></i> Finalizar
                </button>
                @endif
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

        {{-- ══ COLUMNA IZQUIERDA: detalles ══ --}}
        <div class="col-lg-7">
            <div class="row g-3">

                {{-- Cliente --}}
                <div class="col-12">
                    <div class="bg-white rounded shadow-sm p-3 card-section">
                        <h6 class="card-section-title"><i class="bi bi-person-vcard me-1"></i> Cliente</h6>
                        <div class="row g-2">
                            <div class="col-md-6 border-end">
                                <p class="mb-1 fw-bold text-dark">{{ $proyecto->cliente->nombre_completo }}</p>
                                <p class="mb-1"><span class="badge bg-secondary fw-normal" style="font-size:.7rem;">{{ $proyecto->cliente->codigo_cliente }}</span></p>
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
                                    data-bs-toggle="modal" data-bs-target="#modalHistorialUnico">
                                <i class="bi bi-clock-history me-1"></i> Historial
                            </button>
                            @endcan
                        </h6>
                        <p class="mb-1 fw-bold text-dark">{{ $proyecto->servicio->nombre_servicio }}</p>
                        <p class="mb-1 small"><i class="bi bi-building me-1 text-muted"></i><strong>{{ $proyecto->departamento->nombre_departamento }}</strong></p>
                        @if($subtrabajoPrincipal?->numero_tramite)
                        <p class="mb-1 small"><i class="bi bi-hash me-1 text-muted"></i><span class="text-muted">N° Trámite:</span> <strong>{{ $subtrabajoPrincipal->numero_tramite }}</strong></p>
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
                            <div class="col-3">
                                <p class="text-muted small mb-0"><i class="bi bi-calendar-event me-1"></i>Inicio</p>
                                <p class="fw-bold mb-0">{{ $proyecto->fecha_inicio?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <div class="col-3">
                                <p class="text-muted small mb-0"><i class="bi bi-calendar-check me-1"></i>Estimada</p>
                                <p class="fw-bold mb-0">{{ $proyecto->fecha_estimada?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                            <div class="col-3">
                                <p class="text-muted small mb-0"><i class="bi bi-calendar-x me-1"></i>Finalización</p>
                                <p class="fw-bold mb-0">{{ $proyecto->fecha_fin?->format('d/m/Y H:i') ?? '—' }}</p>
                            </div>
                            <div class="col-3">
                                <p class="text-muted small mb-0"><i class="bi bi-cash-coin me-1"></i>Monto Total</p>
                                <p class="fw-bold fs-5 mb-0">${{ number_format($proyecto->monto_total, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Razón y Resultado --}}
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

        {{-- ══ COLUMNA DERECHA: acciones directas ══ --}}
        <div class="col-lg-5">
            <div class="bg-white rounded shadow-sm card-section h-100">
                <div class="d-flex align-items-center justify-content-between"
                     style="background:linear-gradient(90deg,#1a1a1a,#2d2d2d);padding:.6rem 1rem;border-radius:.375rem .375rem 0 0;">
                    <span style="color:#fff;font-size:.8rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase;">
                        <i class="bi bi-list-check me-1"></i> Acciones ({{ $subtrabajoPrincipal?->acciones->count() ?? 0 }})
                    </span>
                    @if($subtrabajoPrincipal && $proyecto->estado_trabajo !== 'terminado' && $proyecto->estado_trabajo !== 'cancelado')
                        @can('create', [App\Models\Accion::class, $subtrabajoPrincipal])
                        <button type="button" class="btn btn-primary btn-sm py-0 px-2"
                                data-bs-toggle="modal" data-bs-target="#modalAccion">
                            <i class="bi bi-plus-lg"></i> Agregar
                        </button>
                        @endcan
                    @endif
                </div>

                <div class="p-3">
                    @if($subtrabajoPrincipal && $subtrabajoPrincipal->acciones->isNotEmpty())
                        @foreach($subtrabajoPrincipal->acciones as $accion)
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
                                    @if($proyecto->estado_trabajo !== 'terminado' && $proyecto->estado_trabajo !== 'cancelado' && $accion->estado !== 'terminado' && $accion->estado !== 'cancelado')
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
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4 small">
                            <i class="bi bi-list-check d-block mb-1" style="font-size:1.5rem;"></i>
                            No hay acciones registradas.
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Formularios ocultos --}}
@if($subtrabajoPrincipal)
<form id="formTramite" action="{{ route('subtrabajos.tramite', [$proyecto->id, $subtrabajoPrincipal->id]) }}" method="POST" class="d-none">
    @csrf @method('PATCH')
    <input type="hidden" name="numero_tramite" id="inputTramite">
</form>
<form id="formFinalizar" action="{{ route('subtrabajos.finalizar', [$proyecto->id, $subtrabajoPrincipal->id]) }}" method="POST" class="d-none">
    @csrf @method('PATCH')
</form>
@endif

{{-- ══ MODAL AGREGAR ACCIÓN ══ --}}
@if($subtrabajoPrincipal)
@can('create', [App\Models\Accion::class, $subtrabajoPrincipal])
<div class="modal fade" id="modalAccion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('acciones.store', [$proyecto->id, $subtrabajoPrincipal->id]) }}" method="POST">
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
@endif

{{-- ══ MODAL ACTUALIZAR PROGRESO ══ --}}
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

{{-- ══ MODAL ASIGNAR RESPONSABLE ══ --}}
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

{{-- ══ MODAL HISTORIAL TRABAJO ÚNICO ══ --}}
@can('ver-historial-trabajo')
@php
    $iconosHist = [
        'estado_trabajo' => ['bi-activity',       '#0d6efd'],
        'responsable_id' => ['bi-person-gear',    '#6f42c1'],
        'fecha_inicio'   => ['bi-calendar-event', '#198754'],
        'fecha_estimada' => ['bi-calendar-check', '#fd7e14'],
        'fecha_fin'      => ['bi-calendar-x',     '#dc3545'],
    ];
    $historialProyecto = $proyecto->historial()->with('usuario')->get();
@endphp
<div class="modal fade" id="modalHistorialUnico" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:#1a1a1a;">
                <h6 class="modal-title text-white fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Historial de Cambios
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @forelse($historialProyecto as $entrada)
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
<script>
@if($subtrabajoPrincipal)
const baseUrl = '{{ url("proyectos/{$proyecto->id}/subtrabajos/{$subtrabajoPrincipal->id}/acciones") }}';
const accionesPendientes = {{ $accionesPendientes ?? 0 }};

function confirmarFinalizar(event, button) {
    event.preventDefault();
    const swalMixin = Swal.mixin({
        customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-danger' },
        buttonsStyling: false
    });

    if (accionesPendientes > 0) {
        swalMixin.fire({
            icon: 'warning',
            title: 'Acciones sin completar',
            text: `Hay ${accionesPendientes} ${accionesPendientes === 1 ? 'acción' : 'acciones'} en pendiente o en proceso. Resuélvelas antes de finalizar el proyecto.`,
            showCancelButton: false,
            confirmButtonText: 'Entendido',
        });
        return;
    }

    swalMixin.fire({
        icon: 'warning',
        title: 'Finalizar proyecto',
        text: 'Se marcará el proyecto como terminado. Esta acción no se puede revertir.',
        showCancelButton: true,
        confirmButtonText: 'Sí, finalizar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formFinalizar').submit();
        }
    });
}

function abrirEditarAccion(accionId, estado, fechaFin, observacion) {
    document.getElementById('formEditarAccion').action = baseUrl + '/' + accionId;
    document.getElementById('edit-estado').value       = estado;
    document.getElementById('edit-fecha-fin').value    = fechaFin;
    document.getElementById('edit-observacion').value  = observacion;
    new bootstrap.Modal(document.getElementById('modalEditarAccion')).show();
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
            document.getElementById('inputTramite').value = result.value ?? '';
            document.getElementById('formTramite').submit();
        }
    });
}
@endif

document.getElementById('buscarResponsable')?.addEventListener('input', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#selectResponsable option').forEach(opt => {
        opt.hidden = !opt.textContent.toLowerCase().includes(term);
    });
});
</script>
@endpush

@endsection
