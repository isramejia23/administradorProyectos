@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper.form-control, .ts-wrapper.form-select { padding: 0; }
    .ts-control { border: 1px solid #dee2e6 !important; border-radius: .375rem !important; font-size: .875rem; min-height: 31px; }
    .ts-dropdown { font-size: .875rem; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="page-title mb-0">
            <i class="bi bi-kanban-fill title-icon"></i> Editar Proyecto #{{ $proyecto->id }}
        </h4>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('proyectos.update', $proyecto->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">

            {{-- ══ COLUMNA IZQUIERDA ══ --}}
            <div class="col-md-8">

                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Información General</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Cliente <span class="text-danger">*</span></label>
                            <select name="cliente_id" id="sel-cliente" class="form-select form-select-sm" required>
                                <option value="">Buscar cliente...</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}" {{ old('cliente_id', $proyecto->cliente_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre_completo }} — {{ $c->identificacion_clientes }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento_id" id="sel-depto" class="form-select form-select-sm" required>
                                <option value="">Seleccione departamento...</option>
                                @foreach($departamentos as $id => $nombre)
                                    <option value="{{ $id }}" {{ old('departamento_id', $proyecto->departamento_id) == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Servicio <span class="text-danger">*</span></label>
                            <select name="servicio_id" id="sel-servicio" class="form-select form-select-sm" required>
                                @foreach($servicios as $s)
                                    <option value="{{ $s->id }}" data-depto="{{ $s->departamento_id }}"
                                        {{ old('servicio_id', $proyecto->servicio_id) == $s->id ? 'selected' : '' }}>
                                        {{ $s->nombre_servicio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Descripción del Trabajo</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Razón / Motivo <span class="text-danger">*</span></label>
                            <textarea name="razon" class="form-control form-control-sm" rows="4"
                                      placeholder="Descripción del trabajo solicitado..." required>{{ old('razon', $proyecto->razon) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Resultado Esperado <span class="text-danger">*</span></label>
                            <textarea name="resultado_esperado" class="form-control form-control-sm" rows="4"
                                      maxlength="1000" required>{{ old('resultado_esperado', $proyecto->resultado_esperado) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Acuerdo de Pagos <span class="text-danger">*</span></label>
                            <input type="text" name="acuerdo_pagos" class="form-control form-control-sm"
                                   maxlength="500" value="{{ old('acuerdo_pagos', $proyecto->acuerdo_pagos) }}"
                                   placeholder="Ej: 50% anticipo, 50% al finalizar" required>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══ COLUMNA DERECHA ══ --}}
            <div class="col-md-4">

                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Responsables</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Vendedor</label>
                            <select name="vendedor_id" id="sel-vendedor" class="form-select form-select-sm">
                                <option value="">Buscar usuario...</option>
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->id }}" {{ old('vendedor_id', $proyecto->vendedor_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->nombre }} {{ $u->apellido }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Responsable / Supervisor</label>
                            <select name="responsable_id" id="sel-responsable" class="form-select form-select-sm">
                                <option value="">Buscar usuario...</option>
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->id }}" {{ old('responsable_id', $proyecto->responsable_id) == $u->id ? 'selected' : '' }}>
                                        {{ $u->nombre }} {{ $u->apellido }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Fechas</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                                   value="{{ old('fecha_inicio', $proyecto->fecha_inicio?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Fecha Estimada <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_estimada" class="form-control form-control-sm"
                                   value="{{ old('fecha_estimada', $proyecto->fecha_estimada?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control form-control-sm"
                                   value="{{ old('fecha_fin', $proyecto->fecha_fin?->format('Y-m-d')) }}">
                        </div>
                    </div>
                </div>

            </div>

            {{-- ══ CLASIFICACIÓN (ancho completo) ══ --}}
            <div class="col-12">
                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Clasificación</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small">N° Trámite</label>
                            <input type="text" name="numero_tramite" class="form-control form-control-sm"
                                   maxlength="20" value="{{ old('numero_tramite', $proyecto->numero_tramite) }}"
                                   placeholder="Máx. 20 caracteres">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Monto Total <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto_total" step="0.01" min="0"
                                       class="form-control"
                                       value="{{ old('monto_total', $proyecto->monto_total) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Estado <span class="text-danger">*</span></label>
                            <select name="estado_trabajo" class="form-select form-select-sm" required>
                                @foreach($estados as $val => $label)
                                    <option value="{{ $val }}" {{ old('estado_trabajo', $proyecto->estado_trabajo) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Nivel de Urgencia <span class="text-danger">*</span></label>
                            <select name="nivel_urgencia" class="form-select form-select-sm" required>
                                @foreach($urgencias as $val => $label)
                                    <option value="{{ $val }}" {{ old('nivel_urgencia', $proyecto->nivel_urgencia) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end pb-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="trabajo_unico"
                                       id="trabajo_unico" value="1"
                                       {{ old('trabajo_unico', $proyecto->trabajo_unico) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-semibold" for="trabajo_unico">
                                    Trabajo Único
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i> Actualizar Proyecto
            </button>
            <a href="{{ route('proyectos.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i> Cancelar
            </a>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const tsOpts    = { placeholder: 'Buscar...', allowEmptyOption: true };
    const tsOptsReq = { placeholder: 'Buscar...', allowEmptyOption: false };

    new TomSelect('#sel-cliente',     { ...tsOpts });
    new TomSelect('#sel-vendedor',    { ...tsOpts });
    new TomSelect('#sel-responsable', { ...tsOpts });

    const allServicios = @json($servicios->map(fn($s) => ['id' => $s->id, 'text' => $s->nombre_servicio, 'depto' => $s->departamento_id]));

    const servicioTs = new TomSelect('#sel-servicio', { ...tsOptsReq });

    const deptoTs = new TomSelect('#sel-depto', { ...tsOptsReq,
        onChange(val) { filtrarServicios(val); }
    });

    function filtrarServicios(deptoId, mantener = false) {
        const current = servicioTs.getValue();
        servicioTs.clear();
        servicioTs.clearOptions();
        const opts = deptoId
            ? allServicios.filter(s => String(s.depto) === String(deptoId))
            : allServicios;
        opts.forEach(s => servicioTs.addOption({ value: s.id, text: s.text }));
        servicioTs.refreshOptions(false);
        if (mantener && current) servicioTs.setValue(current);
    }

    const initDepto = '{{ old('departamento_id', $proyecto->departamento_id) }}';
    if (initDepto) filtrarServicios(initDepto, true);
</script>
@endpush
