@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper.form-control, .ts-wrapper.form-select { padding: 0; }
    .ts-control { border: 1px solid #dee2e6 !important; border-radius: .375rem !important; font-size: .875rem; min-height: 31px; }
    .ts-dropdown { font-size: .875rem; }
    .campo-vendedor { background: #f8f9fa; border-left: 3px solid #198754; padding: .5rem .75rem; border-radius: 0 .375rem .375rem 0; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('proyectos.solicitudes') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="page-title mb-0">
            <i class="bi bi-check2-circle title-icon"></i> Aprobar Solicitud #{{ $proyecto->id }}
        </h4>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="row g-3">

        {{-- ══ COLUMNA IZQUIERDA: datos del vendedor (solo lectura) ══ --}}
        <div class="col-lg-4">

            <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                <h6 class="card-section-title"><i class="bi bi-person-badge me-1"></i> Datos del Vendedor</h6>
                <p class="mb-1 small text-muted">Enviado por</p>
                <p class="mb-3 fw-semibold">{{ $proyecto->vendedor?->nombre_completo ?? '—' }}</p>

                <hr class="my-2">

                <p class="mb-1 small text-muted">Cliente</p>
                <p class="mb-1 fw-semibold">{{ $proyecto->cliente->nombre_completo }}</p>
                <p class="mb-3 small text-muted">{{ $proyecto->cliente->identificacion_clientes }}</p>

                <p class="mb-1 small text-muted">Servicio solicitado</p>
                <p class="mb-3 fw-semibold">{{ $proyecto->servicio->nombre_servicio }}</p>

                <p class="mb-1 small text-muted">Departamento sugerido</p>
                <p class="mb-3 fw-semibold">{{ $proyecto->departamento->nombre_departamento }}</p>

                <hr class="my-2">

                <p class="mb-1 small text-muted">Monto estimado</p>
                <p class="mb-3 fw-bold fs-5">${{ number_format($proyecto->monto_total, 2) }}</p>

                <p class="mb-1 small text-muted">Urgencia</p>
                <p class="mb-3">@include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])</p>

                @if($proyecto->fecha_estimada)
                <p class="mb-1 small text-muted">Fecha estimada sugerida</p>
                <p class="mb-3 small fw-semibold">{{ $proyecto->fecha_estimada->format('d/m/Y') }}</p>
                @endif

                <hr class="my-2">

                <p class="mb-1 small text-muted">Razón / Motivo</p>
                <p class="mb-3 small">{{ $proyecto->razon ?? '—' }}</p>

                <p class="mb-1 small text-muted">Resultado Esperado</p>
                <p class="mb-3 small">{{ $proyecto->resultado_esperado ?? '—' }}</p>

                @if($proyecto->acuerdo_pagos)
                <p class="mb-1 small text-muted">Acuerdo de Pagos</p>
                <p class="mb-0 small">{{ $proyecto->acuerdo_pagos }}</p>
                @endif
            </div>

            {{-- Botón rechazar --}}
            <div class="bg-white rounded shadow-sm p-4 card-section">
                <h6 class="card-section-title text-danger"><i class="bi bi-x-circle me-1"></i> Rechazar</h6>
                <p class="small text-muted mb-3">Si la solicitud no puede aprobarse, indícalo con un motivo claro para el vendedor.</p>
                <button type="button" class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalRechazar">
                    <i class="bi bi-x-circle me-1"></i> Rechazar Solicitud
                </button>
            </div>

        </div>

        {{-- ══ COLUMNA DERECHA: formulario de aprobación ══ --}}
        <div class="col-lg-8">

            <form action="{{ route('proyectos.confirmarAprobacion', $proyecto->id) }}" method="POST">
                @csrf

                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title"><i class="bi bi-sliders me-1"></i> Completar Información del Proyecto</h6>
                    <p class="small text-muted mb-3">Completa los campos necesarios para activar el proyecto. Los datos proporcionados por el vendedor ya están precargados.</p>

                    <div class="row g-3">
                        {{-- Departamento --}}
                        <div class="col-md-6">
                            <label class="form-label small">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento_id" id="sel-depto" class="form-select form-select-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach($departamentos as $id => $nombre)
                                    <option value="{{ $id }}" {{ (old('departamento_id', $proyecto->departamento_id) == $id) ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Servicio — opciones se cargan por JS según departamento --}}
                        <div class="col-md-6">
                            <label class="form-label small">Servicio <span class="text-danger">*</span></label>
                            <select name="servicio_id" id="sel-servicio" class="form-select form-select-sm" required>
                                <option value="">Seleccione primero el departamento...</option>
                            </select>
                        </div>

                        {{-- Vendedor --}}
                        <div class="col-md-6">
                            <label class="form-label small">Vendedor</label>
                            <select name="vendedor_id" id="sel-vendedor" class="form-select form-select-sm">
                                <option value="">Sin vendedor</option>
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->id }}" {{ (old('vendedor_id', $proyecto->vendedor_id) == $u->id) ? 'selected' : '' }}>
                                        {{ $u->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Porcentaje comisión --}}
                        <div class="col-md-6">
                            <label class="form-label small">% Comisión</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="porcentaje_comision" step="0.01" min="0" max="100"
                                       class="form-control" value="{{ old('porcentaje_comision', $proyecto->porcentaje_comision) }}"
                                       placeholder="0.00">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        {{-- Responsable — opciones se cargan por JS según departamento --}}
                        <div class="col-md-6">
                            <label class="form-label small">Responsable / Supervisor</label>
                            <select name="responsable_id" id="sel-responsable" class="form-select form-select-sm">
                                <option value="">Sin asignar — quedará en bandeja</option>
                            </select>
                        </div>

                        {{-- N° trámite --}}
                        <div class="col-md-6">
                            <label class="form-label small">N° Trámite</label>
                            <input type="text" name="numero_tramite" class="form-control form-control-sm"
                                   maxlength="20" value="{{ old('numero_tramite') }}" placeholder="Máx. 20 caracteres">
                        </div>

                        {{-- Monto total --}}
                        <div class="col-md-6">
                            <label class="form-label small">Monto Total <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto_total" step="0.01" min="0"
                                       class="form-control" value="{{ old('monto_total', $proyecto->monto_total) }}" required>
                            </div>
                        </div>

                        {{-- Urgencia --}}
                        <div class="col-md-6">
                            <label class="form-label small">Nivel de Urgencia <span class="text-danger">*</span></label>
                            <select name="nivel_urgencia" class="form-select form-select-sm" required>
                                @foreach($urgencias as $val => $label)
                                    <option value="{{ $val }}" {{ (old('nivel_urgencia', $proyecto->nivel_urgencia) === $val) ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Fecha inicio --}}
                        <div class="col-md-6">
                            <label class="form-label small">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                                   value="{{ old('fecha_inicio', now()->toDateString()) }}" required>
                        </div>

                        {{-- Fecha estimada --}}
                        <div class="col-md-6">
                            <label class="form-label small">Fecha Estimada <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_estimada" class="form-control form-control-sm"
                                   value="{{ old('fecha_estimada', $proyecto->fecha_estimada?->toDateString()) }}" required>
                        </div>

                    </div>
                </div>

                {{-- Descripción (editable por el aprobador) --}}
                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title"><i class="bi bi-chat-left-text me-1"></i> Descripción</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Razón / Motivo <span class="text-danger">*</span></label>
                            <textarea name="razon" class="form-control form-control-sm" rows="4" required>{{ old('razon', $proyecto->razon) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Resultado Esperado <span class="text-danger">*</span></label>
                            <textarea name="resultado_esperado" class="form-control form-control-sm" rows="4"
                                      maxlength="1000" required>{{ old('resultado_esperado', $proyecto->resultado_esperado) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Acuerdo de Pagos <span class="text-danger">*</span></label>
                            <input type="text" name="acuerdo_pagos" class="form-control form-control-sm" maxlength="500"
                                   value="{{ old('acuerdo_pagos', $proyecto->acuerdo_pagos) }}"
                                   placeholder="Ej: 50% anticipo, 50% al finalizar" required>
                        </div>
                    </div>
                </div>

                {{-- Tipo de trabajo --}}
                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title"><i class="bi bi-diagram-3 me-1"></i> Tipo de Trabajo</h6>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="trabajo_unico" id="trabajo_unico" value="1"
                               {{ old('trabajo_unico') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="trabajo_unico">
                            <strong>Trabajo único</strong>
                            <span class="text-muted">— no requiere subtrabajos; las acciones se registran directamente sobre este proyecto</span>
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i> Aprobar y Activar Proyecto
                    </button>
                    <a href="{{ route('proyectos.solicitudes') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancelar
                    </a>
                </div>

            </form>
        </div>

    </div>
</div>

{{-- Modal rechazar --}}
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
                    <p class="small text-muted mb-3">
                        Explica el motivo del rechazo. El vendedor podrá ver esta información.
                    </p>
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

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const tsOpts = { placeholder: 'Buscar...', allowEmptyOption: true };

    // Vendedor: sin filtro por departamento (puede ser de cualquier área)
    new TomSelect('#sel-vendedor', { ...tsOpts });

    const allServicios = @json($servicios->map(fn($s) => ['id' => $s->id, 'text' => $s->nombre_servicio, 'depto' => $s->departamento_id]));
    const allUsuarios  = @json($usuarios->map(fn($u)  => ['id' => $u->id,  'text' => $u->nombre_completo, 'depto' => $u->departamento_id]));

    // Servicio y responsable arrancan vacíos; se llenan al elegir departamento
    const servicioTs    = new TomSelect('#sel-servicio',    { options: [], items: [], placeholder: 'Seleccione primero el departamento...' });
    const responsableTs = new TomSelect('#sel-responsable', {
        options: [{ value: '', text: 'Sin asignar — quedará en bandeja' }],
        items:   [''],
        placeholder: 'Sin asignar — quedará en bandeja',
        allowEmptyOption: true,
    });

    function filtrarPorDepto(deptoId) {
        servicioTs.clear();    servicioTs.clearOptions();
        responsableTs.clear(); responsableTs.clearOptions();
        responsableTs.addOption({ value: '', text: 'Sin asignar — quedará en bandeja' });

        if (deptoId) {
            allServicios
                .filter(s => String(s.depto) === String(deptoId))
                .forEach(s => servicioTs.addOption({ value: String(s.id), text: s.text }));

            allUsuarios
                .filter(u => String(u.depto) === String(deptoId))
                .forEach(u => responsableTs.addOption({ value: String(u.id), text: u.text }));
        }

        servicioTs.refreshOptions(false);
        responsableTs.refreshOptions(false);
    }

    const deptoTs = new TomSelect('#sel-depto', {
        allowEmptyOption: false,
        placeholder: 'Seleccione departamento...',
        onChange(val) { filtrarPorDepto(val); }
    });

    const currentDepto      = '{{ old('departamento_id', $proyecto->departamento_id) }}';
    const currentServicio   = '{{ old('servicio_id', $proyecto->servicio_id) }}';
    const currentResponsable = '{{ old('responsable_id') }}';

    if (currentDepto) {
        filtrarPorDepto(currentDepto);
        servicioTs.setValue(currentServicio);
        if (currentResponsable) responsableTs.setValue(currentResponsable);
    }
</script>
@endpush
