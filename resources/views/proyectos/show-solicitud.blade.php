@extends('layouts.app')

@section('content')
@php
    $esAprobador = auth()->user()->can('ver-solicitudes-trabajo');
    $backRoute   = $esAprobador
        ? route('proyectos.solicitudes')
        : (auth()->user()->can('ver-mis-trabajos') ? route('proyectos.mis') : route('proyectos.ventas'));

    // Buscar en el historial quién procesó la solicitud
    $entradaProcesado = $historialSolicitud
        ->where('campo', 'estado_trabajo')
        ->whereIn('valor_nuevo', ['pendiente', 'rechazado'])
        ->first();
@endphp

<div class="container-fluid">

    {{-- Cabecera --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ $backRoute }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="page-title mb-0">
            <i class="bi bi-send title-icon"></i>
            Solicitud #{{ $proyecto->id }} — {{ $proyecto->servicio->nombre_servicio }}
        </h4>
        <span class="badge bg-{{ $proyecto->estado_badge }} ms-1">{{ $proyecto->estado_label }}</span>

        @if($proyecto->estado_trabajo === 'solicitud')
        <div class="ms-auto">
            @can('aprobar', $proyecto)
            <a href="{{ route('proyectos.aprobar', $proyecto->id) }}" class="btn btn-success btn-sm">
                <i class="bi bi-check2-circle me-1"></i> Revisar y Aprobar
            </a>
            @endcan
        </div>
        @endif
    </div>

    {{-- Banner de estado --}}
    @if($proyecto->estado_trabajo === 'rechazado')
    <div class="alert alert-danger d-flex gap-2 mb-4" role="alert">
        <i class="bi bi-x-circle-fill flex-shrink-0 mt-1" style="font-size:1.2rem;"></i>
        <div>
            <div class="fw-semibold mb-1">Solicitud rechazada</div>
            <div class="small">{{ $proyecto->motivo_rechazo }}</div>
            @if($entradaProcesado)
            <div class="small text-muted mt-1">
                <i class="bi bi-person me-1"></i>{{ $entradaProcesado->usuario?->nombre_completo ?? 'Sistema' }}
                &nbsp;·&nbsp;
                <i class="bi bi-clock me-1"></i>{{ $entradaProcesado->created_at->format('d/m/Y H:i') }}
            </div>
            @endif
        </div>
    </div>
    @elseif($proyecto->estado_trabajo === 'solicitud')
    <div class="alert alert-warning d-flex gap-2 mb-4" role="alert">
        <i class="bi bi-hourglass-split flex-shrink-0 mt-1" style="font-size:1.2rem;"></i>
        <div class="small">
            <strong>Pendiente de aprobación.</strong>
            Esta solicitud está siendo revisada. Recibirás retroalimentación una vez sea procesada.
        </div>
    </div>
    @endif

    <div class="row g-3">

        {{-- ══ Columna principal ══ --}}
        <div class="col-lg-8">

            {{-- Cliente --}}
            <div class="bg-white rounded shadow-sm p-3 mb-3 card-section">
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

            {{-- Servicio --}}
            <div class="bg-white rounded shadow-sm p-3 mb-3 card-section">
                <h6 class="card-section-title"><i class="bi bi-briefcase me-1"></i> Servicio</h6>
                <p class="mb-1 fw-bold text-dark">{{ $proyecto->servicio->nombre_servicio }}</p>
                <p class="mb-0 small"><i class="bi bi-building me-1 text-muted"></i><strong>{{ $proyecto->departamento->nombre_departamento }}</strong></p>
            </div>

            {{-- Descripción --}}
            <div class="row g-3 mb-3">
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
                        @if($proyecto->acuerdo_pagos)
                        <hr class="my-2">
                        <p class="mb-0 small"><span class="text-muted">Acuerdo de pagos:</span> {{ $proyecto->acuerdo_pagos }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Historial de la solicitud --}}
            @if($historialSolicitud->isNotEmpty())
            <div class="bg-white rounded shadow-sm p-4 card-section">
                <h6 class="card-section-title"><i class="bi bi-clock-history me-1"></i> Historial</h6>
                @foreach($historialSolicitud as $entrada)
                @php
                    $color = match($entrada->valor_nuevo) {
                        'pendiente' => '#198754',
                        'rechazado' => '#dc3545',
                        default     => '#6c757d',
                    };
                    $icono = match($entrada->valor_nuevo) {
                        'pendiente' => 'bi-check2-circle',
                        'rechazado' => 'bi-x-circle',
                        default     => 'bi-activity',
                    };
                @endphp
                <div class="d-flex gap-3 mb-2">
                    <div class="flex-shrink-0 pt-1">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:28px;height:28px;background:{{ $color }}1a;">
                            <i class="bi {{ $icono }}" style="font-size:.75rem;color:{{ $color }};"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fw-semibold small">Estado</span>
                            @if($entrada->valor_anterior)
                            <span class="badge" style="background:#f0f0f0;color:#555;font-size:.68rem;">{{ $entrada->valor_anterior }}</span>
                            <i class="bi bi-arrow-right" style="font-size:.65rem;color:#aaa;"></i>
                            @endif
                            <span class="badge" style="background:{{ $color }}20;color:{{ $color }};font-size:.68rem;">{{ $entrada->valor_nuevo }}</span>
                        </div>
                        <div class="d-flex gap-2 mt-1" style="font-size:.7rem;color:#aaa;">
                            <span><i class="bi bi-person me-1"></i>{{ $entrada->usuario?->nombre_completo ?? 'Sistema' }}</span>
                            <span>·</span>
                            <span><i class="bi bi-clock me-1"></i>{{ $entrada->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </div>

        {{-- ══ Columna lateral ══ --}}
        <div class="col-lg-4">

            {{-- Datos de la solicitud --}}
            <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                <h6 class="card-section-title"><i class="bi bi-info-circle me-1"></i> Datos de la Solicitud</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <p class="text-muted small mb-0">Estado</p>
                        <span class="badge bg-{{ $proyecto->estado_badge }}">{{ $proyecto->estado_label }}</span>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-0">Urgencia</p>
                        @include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-0 mt-2">Monto Estimado</p>
                        <p class="fw-bold mb-0">${{ number_format($proyecto->monto_total, 2) }}</p>
                    </div>
                    <div class="col-6">
                        <p class="text-muted small mb-0 mt-2">Fecha Estimada</p>
                        <p class="fw-semibold mb-0 small">{{ $proyecto->fecha_estimada?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    @if($proyecto->vendedor)
                    <div class="col-12">
                        <p class="text-muted small mb-0 mt-2">Vendedor</p>
                        <p class="fw-semibold mb-0 small">{{ $proyecto->vendedor->nombre_completo }}</p>
                    </div>
                    @endif
                    @if($proyecto->responsable)
                    <div class="col-12">
                        <p class="text-muted small mb-0 mt-2">Responsable</p>
                        <p class="fw-semibold mb-0 small">{{ $proyecto->responsable->nombre_completo }}</p>
                    </div>
                    @endif
                    @if(!$proyecto->vendedor && !$proyecto->responsable)
                    <div class="col-12">
                        <p class="text-muted small mb-0 mt-2">Creado por</p>
                        <p class="fw-semibold mb-0 small">—</p>
                    </div>
                    @endif
                    <div class="col-12">
                        <p class="text-muted small mb-0 mt-2">Enviada el</p>
                        <p class="fw-semibold mb-0 small">{{ $proyecto->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($entradaProcesado)
                    <div class="col-12">
                        <hr class="my-2">
                        <p class="text-muted small mb-0">
                            {{ $proyecto->estado_trabajo === 'rechazado' ? 'Rechazada por' : 'Aprobada por' }}
                        </p>
                        <p class="fw-semibold mb-0 small">{{ $entradaProcesado->usuario?->nombre_completo ?? '—' }}</p>
                        <p class="text-muted mb-0" style="font-size:.7rem;">{{ $entradaProcesado->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Acciones del aprobador (solo si está en solicitud) --}}
            @if($proyecto->estado_trabajo === 'solicitud')
            @can('aprobar', $proyecto)
            <div class="bg-white rounded shadow-sm p-4 card-section">
                <h6 class="card-section-title"><i class="bi bi-shield-check me-1"></i> Acciones</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('proyectos.aprobar', $proyecto->id) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-check2-circle me-1"></i> Revisar y Aprobar
                    </a>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                        <i class="bi bi-x-circle me-1"></i> Rechazar
                    </button>
                </div>
            </div>
            @endcan
            @endif

        </div>

    </div>
</div>

{{-- Modal rechazar — solo para solicitudes pendientes --}}
@if($proyecto->estado_trabajo === 'solicitud')
@can('aprobar', $proyecto)
<div class="modal fade" id="modalRechazar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('proyectos.rechazar', $proyecto->id) }}" method="POST">
                @csrf @method('PATCH')
                <div class="modal-header" style="background:#dc3545;">
                    <h6 class="modal-title text-white fw-semibold">
                        <i class="bi bi-x-circle me-1"></i> Rechazar Solicitud
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Explica el motivo. El solicitante podrá ver esta información.</p>
                    <label class="form-label small">Motivo del Rechazo <span class="text-danger">*</span></label>
                    <textarea name="motivo_rechazo" class="form-control form-control-sm" rows="4"
                              maxlength="1000" placeholder="Describe por qué no se puede aprobar esta solicitud..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-x-circle me-1"></i> Confirmar Rechazo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endif

@endsection
