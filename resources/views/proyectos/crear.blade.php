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
        <a href="{{ route('proyectos.ventas') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="page-title mb-0">
            @if($esVendedor)
                <i class="bi bi-send title-icon"></i> Nueva Solicitud de Proyecto
            @else
                <i class="bi bi-kanban-fill title-icon"></i> Nuevo Proyecto
            @endif
        </h4>
        @if($esVendedor)
        <span class="badge bg-warning text-dark ms-2">Pendiente de aprobación</span>
        @endif
    </div>

    @if($esVendedor)
    <div class="alert alert-info d-flex gap-2 mb-4" role="alert">
        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
        <div class="small">
            Completa los datos del proyecto. Una vez enviada, la solicitud será revisada por un administrador o jefe de departamento quien completará la información restante y la aprobará para iniciar el flujo de trabajo.
        </div>
    </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('proyectos.store') }}" method="POST">
        @csrf

        @if($esVendedor)
        {{-- ══════════════════════════════════════════════════════════
             FORMULARIO SIMPLIFICADO PARA VENDEDOR
        ══════════════════════════════════════════════════════════ --}}
        <div class="row g-3">

            <div class="col-md-8">

                {{-- Información General --}}
                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Información General</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Cliente <span class="text-danger">*</span></label>
                            <select name="cliente_id" id="sel-cliente" class="form-select form-select-sm" required>
                                <option value="">Buscar cliente...</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}" {{ (old('cliente_id', $clienteIdPreseleccionado ?? '')) == $c->id ? 'selected' : '' }}>
                                        {{ $c->codigo_cliente }} — {{ $c->nombre_completo }} — {{ $c->identificacion_clientes }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento_id" id="sel-depto" class="form-select form-select-sm" required>
                                <option value="">Seleccione departamento...</option>
                                @foreach($departamentos as $id => $nombre)
                                    <option value="{{ $id }}" {{ (old('departamento_id') ?: auth()->user()->departamento_id) == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Servicio <span class="text-danger">*</span></label>
                            <select name="servicio_id" id="sel-servicio" class="form-select form-select-sm" required>
                                <option value="">Seleccione primero el departamento...</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Descripción del Trabajo</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Razón / Motivo <span class="text-danger">*</span></label>
                            <textarea name="razon" class="form-control form-control-sm" rows="4"
                                      placeholder="Descripción del trabajo solicitado..." required>{{ old('razon') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Resultado Esperado <span class="text-danger">*</span></label>
                            <textarea name="resultado_esperado" class="form-control form-control-sm" rows="4"
                                      maxlength="1000" placeholder="¿Qué espera el cliente obtener?" required>{{ old('resultado_esperado') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Acuerdo de Pagos <span class="text-muted">(opcional)</span></label>
                            <input type="text" name="acuerdo_pagos" class="form-control form-control-sm"
                                   maxlength="500" value="{{ old('acuerdo_pagos') }}"
                                   placeholder="Ej: 50% anticipo, 50% al finalizar">
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-4">

                {{-- Vendedor (solo lectura: el usuario actual) --}}
                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title"><i class="bi bi-person-badge me-1"></i> Vendedor</h6>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-circle text-muted" style="font-size:1.4rem;"></i>
                        <div>
                            <p class="mb-0 fw-semibold">{{ auth()->user()->nombre_completo }}</p>
                            <p class="mb-0 small text-muted">{{ auth()->user()->email }}</p>
                        </div>
                        <span class="badge bg-primary ms-auto">Tú</span>
                    </div>
                </div>

                {{-- Clasificación --}}
                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Clasificación</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Monto Total <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto_total" step="0.01" min="0"
                                       class="form-control" value="{{ old('monto_total', 0) }}" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Nivel de Urgencia <span class="text-danger">*</span></label>
                            <select name="nivel_urgencia" class="form-select form-select-sm" required>
                                @foreach($urgencias as $val => $label)
                                    <option value="{{ $val }}" {{ old('nivel_urgencia', 'medio') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Fecha Estimada <span class="text-muted">(opcional)</span></label>
                            <input type="date" name="fecha_estimada" class="form-control form-control-sm" value="{{ old('fecha_estimada') }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="trabajo_unico" id="trabajo_unico" value="1"
                                       {{ old('trabajo_unico') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="trabajo_unico">
                                    <strong>Trabajo único</strong>
                                    <span class="text-muted">— sin subtrabajos, las acciones van directo al proyecto</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info estado --}}
                <div class="bg-white rounded shadow-sm p-4 card-section">
                    <h6 class="card-section-title">Estado de la Solicitud</h6>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark">Solicitud Pendiente</span>
                    </div>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Al guardar, la solicitud quedará en espera de aprobación. No se generarán subtrabajos hasta que sea aprobada.
                    </p>
                </div>

            </div>

        </div>

        @else
        {{-- ══════════════════════════════════════════════════════════
             FORMULARIO COMPLETO PARA ADMIN / JEFE / PROFESIONAL
        ══════════════════════════════════════════════════════════ --}}
        <div class="row g-3">

            <div class="col-md-8">

                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Información General</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Cliente <span class="text-danger">*</span></label>
                            <select name="cliente_id" id="sel-cliente" class="form-select form-select-sm" required>
                                <option value="">Buscar cliente...</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}" {{ (old('cliente_id', $clienteIdPreseleccionado ?? '')) == $c->id ? 'selected' : '' }}>
                                        {{ $c->codigo_cliente }} — {{ $c->nombre_completo }} — {{ $c->identificacion_clientes }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Departamento <span class="text-danger">*</span></label>
                            <select name="departamento_id" id="sel-depto" class="form-select form-select-sm" required>
                                <option value="">Seleccione departamento...</option>
                                @foreach($departamentos as $id => $nombre)
                                    <option value="{{ $id }}" {{ (old('departamento_id') ?: auth()->user()->departamento_id) == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Servicio <span class="text-danger">*</span></label>
                            <select name="servicio_id" id="sel-servicio" class="form-select form-select-sm" required>
                                <option value="">Seleccione primero el departamento...</option>
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
                                      placeholder="Descripción del trabajo solicitado..." required>{{ old('razon') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Resultado Esperado <span class="text-danger">*</span></label>
                            <textarea name="resultado_esperado" class="form-control form-control-sm" rows="4"
                                      maxlength="1000" placeholder="¿Qué espera el cliente obtener?" required>{{ old('resultado_esperado') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Acuerdo de Pagos <span class="text-danger">*</span></label>
                            <input type="text" name="acuerdo_pagos" class="form-control form-control-sm"
                                   maxlength="500" value="{{ old('acuerdo_pagos') }}"
                                   placeholder="Ej: 50% anticipo, 50% al finalizar" required>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-md-4">

                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Responsables</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Vendedor</label>
                            <select name="vendedor_id" id="sel-vendedor" class="form-select form-select-sm">
                                <option value="">Buscar usuario...</option>
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->id }}" {{ old('vendedor_id') == $u->id ? 'selected' : '' }}>
                                        {{ $u->nombre }} {{ $u->apellido }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Responsable / Supervisor</label>
                            <select name="responsable_id" id="sel-responsable" class="form-select form-select-sm">
                                <option value="">Seleccione primero el departamento...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Fechas</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="{{ old('fecha_inicio') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Fecha Estimada <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_estimada" class="form-control form-control-sm" value="{{ old('fecha_estimada') }}" required>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-12">
                <div class="bg-white rounded shadow-sm p-4 mb-3 card-section">
                    <h6 class="card-section-title">Clasificación</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Monto Total <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">$</span>
                                <input type="number" name="monto_total" step="0.01" min="0"
                                       class="form-control" value="{{ old('monto_total', 0) }}" required>
                            </div>
                        </div>
                        <input type="hidden" name="estado_trabajo" value="pendiente">
                        <div class="col-md-4">
                            <label class="form-label small">N° Trámite</label>
                            <input type="text" name="numero_tramite" class="form-control form-control-sm"
                                   maxlength="20" value="{{ old('numero_tramite') }}"
                                   placeholder="Máx. 20 caracteres">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Estado</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="Pendiente" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Nivel de Urgencia <span class="text-danger">*</span></label>
                            <select name="nivel_urgencia" class="form-select form-select-sm" required>
                                @foreach($urgencias as $val => $label)
                                    <option value="{{ $val }}" {{ old('nivel_urgencia', 'medio') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="trabajo_unico" id="trabajo_unico" value="1"
                                       {{ old('trabajo_unico') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="trabajo_unico">
                                    <strong>Trabajo único</strong>
                                    <span class="text-muted">— no requiere subtrabajos; las acciones se registran directamente sobre este proyecto</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @endif

        <div class="d-flex gap-2 mb-4">
            <button type="submit" class="btn btn-primary">
                @if($esVendedor)
                    <i class="bi bi-send me-1"></i> Enviar Solicitud
                @else
                    <i class="bi bi-check-lg me-1"></i> Guardar Proyecto
                @endif
            </button>
            <a href="{{ $esVendedor ? route('proyectos.ventas') : route('proyectos.index') }}" class="btn btn-outline-secondary">
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

    new TomSelect('#sel-cliente', { ...tsOpts });

    @unless($esVendedor)
    new TomSelect('#sel-vendedor', { ...tsOpts });
    @endunless

    const allServicios = @json($servicios->map(fn($s) => ['id' => $s->id, 'text' => $s->nombre_servicio, 'depto' => $s->departamento_id]));
    @unless($esVendedor)
    const allUsuarios  = @json($usuarios->map(fn($u) => ['id' => $u->id, 'text' => $u->nombre_completo, 'depto' => $u->departamento_id]));
    @endunless

    // Servicio arranca vacío; se llena al elegir departamento
    const servicioTs = new TomSelect('#sel-servicio', {
        options: [],
        items: [],
        placeholder: 'Seleccione primero el departamento...',
        allowEmptyOption: false,
    });

    @unless($esVendedor)
    const responsableTs = new TomSelect('#sel-responsable', {
        options: [{ value: '', text: 'Sin asignar — quedará en bandeja' }],
        items: [''],
        allowEmptyOption: true,
    });
    @endunless

    function filtrarPorDepto(deptoId) {
        servicioTs.clear();
        servicioTs.clearOptions();

        if (deptoId) {
            allServicios
                .filter(s => String(s.depto) === String(deptoId))
                .forEach(s => servicioTs.addOption({ value: String(s.id), text: s.text }));
        }
        servicioTs.refreshOptions(false);

        @unless($esVendedor)
        responsableTs.clear();
        responsableTs.clearOptions();
        responsableTs.addOption({ value: '', text: 'Sin asignar — quedará en bandeja' });
        if (deptoId) {
            allUsuarios
                .filter(u => String(u.depto) === String(deptoId))
                .forEach(u => responsableTs.addOption({ value: String(u.id), text: u.text }));
        }
        responsableTs.refreshOptions(false);
        @endunless
    }

    const deptoTs = new TomSelect('#sel-depto', { ...tsOptsReq,
        onChange(val) { filtrarPorDepto(val); }
    });

    const oldDepto  = '{{ old('departamento_id') }}';
    const userDepto = '{{ auth()->user()->departamento_id }}';
    const initDepto = oldDepto || userDepto;
    if (initDepto) {
        deptoTs.setValue(initDepto);
        filtrarPorDepto(initDepto);
        if (oldDepto) {
            servicioTs.setValue('{{ old('servicio_id') }}');
            @unless($esVendedor)
            responsableTs.setValue('{{ old('responsable_id') }}');
            @endunless
        }
    } else {
        filtrarPorDepto(null);
    }
</script>
@endpush
