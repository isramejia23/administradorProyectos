@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Cabecera --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('cobros.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="page-title mb-0">
            <i class="bi bi-cash-stack title-icon"></i>
            Cuenta #{{ $cobro->id }} — {{ $cobro->trabajo->cliente->nombre_completo }}
        </h4>
        <div class="ms-auto d-flex gap-2">
            @can('editar-cuenta-cobrar')
            @if($cobro->saldo > 0)
            <button type="button" class="btn btn-outline-secondary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalEditarCuenta">
                <i class="bi bi-pencil me-1"></i> Editar Cuenta
            </button>
            @endif
            @endcan
            @can('crear-pago')
            @if($cobro->saldo > 0)
            <button type="button" class="btn btn-primary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalRegistrarPago">
                <i class="bi bi-plus-circle me-1"></i> Registrar Pago
            </button>
            @else
            <span class="badge bg-success px-3 py-2" style="font-size:.8rem;">
                <i class="bi bi-check-circle me-1"></i> Cuenta Pagada
            </span>
            @endif
            @endcan
        </div>
    </div>

    {{-- Fila superior: info proyecto + resumen financiero --}}
    <div class="row g-3 mb-4">

        {{-- Info del proyecto --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="card-section-title">
                        <i class="bi bi-kanban me-1"></i> Información del Proyecto
                    </div>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted fw-normal">Cliente</dt>
                        <dd class="col-7 fw-semibold mb-1">{{ $cobro->trabajo->cliente->nombre_completo }}</dd>

                        <dt class="col-5 text-muted fw-normal">Identificación</dt>
                        <dd class="col-7 mb-1">{{ $cobro->trabajo->cliente->identificacion_clientes }}</dd>

                        <dt class="col-5 text-muted fw-normal">Servicio</dt>
                        <dd class="col-7 mb-1">{{ $cobro->trabajo->servicio?->nombre_servicio ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Departamento</dt>
                        <dd class="col-7 mb-1">{{ $cobro->trabajo->departamento?->nombre_departamento ?? '—' }}</dd>

                        <dt class="col-5 text-muted fw-normal">Responsable</dt>
                        <dd class="col-7 mb-1">
                            {{ $cobro->trabajo->responsable ? $cobro->trabajo->responsable->nombre . ' ' . $cobro->trabajo->responsable->apellido : '—' }}
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Estado Proyecto</dt>
                        <dd class="col-7 mb-1">
                            @php
                                $epBadge = match($cobro->trabajo->estado_trabajo) {
                                    'pendiente'  => 'info',
                                    'en_proceso' => 'primary',
                                    'terminado'  => 'success',
                                    'cancelado'  => 'danger',
                                    default      => 'secondary',
                                };
                                $epLabel = match($cobro->trabajo->estado_trabajo) {
                                    'pendiente'  => 'Pendiente',
                                    'en_proceso' => 'En Proceso',
                                    'terminado'  => 'Terminado',
                                    'cancelado'  => 'Cancelado',
                                    default      => ucfirst($cobro->trabajo->estado_trabajo ?? '—'),
                                };
                            @endphp
                            <span class="badge bg-{{ $epBadge }}" style="font-size:.72rem;">{{ $epLabel }}</span>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Vencimiento</dt>
                        <dd class="col-7 mb-0">
                            @if($cobro->fecha_vencimiento)
                                @php $vencida = $cobro->fecha_vencimiento->isPast() && $cobro->estado_pago !== 'pagado'; @endphp
                                <span class="{{ $vencida ? 'text-danger fw-semibold' : '' }}">
                                    {{ $cobro->fecha_vencimiento->format('d/m/Y') }}
                                    @if($vencida) <i class="bi bi-exclamation-triangle-fill ms-1"></i> @endif
                                </span>
                            @else
                                <span class="text-muted">Sin fecha</span>
                            @endif
                        </dd>

                        @if($cobro->notas)
                        <dt class="col-5 text-muted fw-normal mt-1">Notas</dt>
                        <dd class="col-7 mt-1 mb-0">{{ $cobro->notas }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        {{-- Resumen financiero --}}
        <div class="col-lg-7">
            <div class="row g-3 h-100">
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="text-muted small mb-1">Monto Total</div>
                            <div class="fw-bold fs-5">${{ number_format($cobro->monto_total, 2) }}</div>
                            @if($cobro->monto_extras > 0)
                            <div class="text-muted" style="font-size:.7rem;">
                                Base ${{ number_format($cobro->monto_base, 2) }}<br>
                                Extras ${{ number_format($cobro->monto_extras, 2) }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="text-muted small mb-1">Pagado</div>
                            <div class="fw-bold fs-5 text-success">${{ number_format($cobro->monto_pagado, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="text-muted small mb-1">Saldo</div>
                            <div class="fw-bold fs-5 {{ $cobro->saldo > 0 ? 'text-danger' : 'text-success' }}">
                                ${{ number_format($cobro->saldo, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body py-3">
                            <div class="text-muted small mb-1">Estado</div>
                            <span class="badge bg-{{ $cobro->estado_pago_badge }} fs-6 mt-1">
                                {{ $cobro->estado_pago_label }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Barra de progreso --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Progreso de pago</span>
                                <span class="fw-semibold">{{ $cobro->porcentaje_pagado }}%</span>
                            </div>
                            <div class="progress" style="height:10px;border-radius:6px;">
                                <div class="progress-bar bg-success"
                                     style="width:{{ $cobro->porcentaje_pagado }}%;border-radius:6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fila inferior: distribución + pagos --}}
    <div class="row g-3">

        {{-- Distribución de cobro --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="card-section-title">
                        <i class="bi bi-pie-chart me-1"></i> Distribución del Cobro
                    </div>
                    @forelse($distribucion as $comp)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold">
                                <i class="bi {{ $comp['icono'] }} me-1" style="color:{{ $comp['color'] }}"></i>
                                {{ $comp['nombre'] }}
                            </span>
                            <span class="small text-muted">${{ number_format($comp['monto'], 2) }}</span>
                        </div>
                        <div class="progress mb-1" style="height:7px;border-radius:4px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width:{{ $comp['porcentaje'] }}%;background:{{ $comp['color'] }};border-radius:4px;">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size:.7rem;color:#888;">
                            <span>Cubierto: ${{ number_format($comp['cubierto'], 2) }}</span>
                            <span>Pendiente: ${{ number_format($comp['pendiente'], 2) }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small mb-0">Sin componentes.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Historial de pagos --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="card-section-title">
                        <i class="bi bi-receipt me-1"></i> Historial de Pagos
                    </div>
                    @if($cobro->pagos->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-receipt d-block mb-2" style="font-size:2rem;"></i>
                        No hay pagos registrados.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 small">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th class="text-end">Monto</th>
                                    <th>Referencia</th>
                                    <th>Registrado por</th>
                                    <th class="text-center" style="width:80px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cobro->pagos as $pago)
                                <tr class="{{ $pago->is_anulado ? 'opacity-50' : '' }}">
                                    <td class="{{ $pago->is_anulado ? 'text-decoration-line-through text-muted' : '' }}">
                                        {{ $pago->fecha_pago->format('d/m/Y') }}
                                    </td>
                                    <td>
                                        @if($pago->is_anulado)
                                            <span class="badge bg-danger">Anulado</span>
                                        @else
                                            <span class="badge bg-{{ $pago->metodo_pago_badge }}">
                                                {{ $pago->metodo_pago_label }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold {{ $pago->is_anulado ? 'text-decoration-line-through text-muted' : 'text-success' }}">
                                        ${{ number_format($pago->monto, 2) }}
                                    </td>
                                    <td class="text-muted">{{ $pago->referencia ?? '—' }}</td>
                                    <td class="text-muted" style="font-size:.75rem;">
                                        {{ $pago->registrador ? $pago->registrador->nombre . ' ' . $pago->registrador->apellido : '—' }}
                                    </td>
                                    <td class="text-center">
                                        @if(! $pago->is_anulado)
                                        <div class="d-flex gap-1 justify-content-center">
                                            @can('editar-pago')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="Editar pago"
                                                    onclick="abrirEditarPago(
                                                        {{ $pago->id }},
                                                        '{{ $pago->monto }}',
                                                        '{{ $pago->fecha_pago->format('Y-m-d') }}',
                                                        '{{ $pago->metodo_pago }}',
                                                        '{{ addslashes($pago->referencia ?? '') }}',
                                                        '{{ addslashes($pago->notas ?? '') }}',
                                                        {{ $cobro->saldo + (float) $pago->monto }}
                                                    )">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            @endcan
                                            @can('borrar-pago')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Anular pago"
                                                    onclick="abrirAnularPago({{ $pago->id }}, '${{ number_format($pago->monto, 2) }}', '{{ $pago->fecha_pago->format('d/m/Y') }}')">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                            @endcan
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @if($pago->is_anulado && $pago->motivo_anulacion)
                                <tr style="background:#fff5f5;">
                                    <td colspan="6" class="py-1 px-3 text-danger" style="font-size:.75rem;">
                                        <i class="bi bi-slash-circle me-1"></i>
                                        <strong>Motivo de anulación:</strong> {{ $pago->motivo_anulacion }}
                                        — {{ $pago->anulado_at->format('d/m/Y H:i') }}
                                    </td>
                                </tr>
                                @elseif($pago->notas)
                                <tr class="table-light">
                                    <td colspan="6" class="py-1 px-3 text-muted" style="font-size:.75rem;">
                                        <i class="bi bi-chat-left-text me-1"></i>{{ $pago->notas }}
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /row --}}

</div>{{-- /container --}}

{{-- ══════════════════════════════════════════════
     MODAL: Registrar Pago
══════════════════════════════════════════════ --}}
@can('crear-pago')
<div class="modal fade" id="modalRegistrarPago" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cobros.pagos.store', $cobro->id) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(90deg,#1a1a1a,#2d2d2d);color:#fff;">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> Registrar Pago</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Monto <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto" step="0.01" min="0.01"
                                   max="{{ $cobro->saldo }}"
                                   class="form-control" required
                                   placeholder="{{ number_format($cobro->saldo, 2) }}">
                        </div>
                        @if($cobro->saldo > 0)
                        <div class="form-text">Saldo pendiente: ${{ number_format($cobro->saldo, 2) }}</div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_pago" class="form-control form-control-sm"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Método de Pago <span class="text-danger">*</span></label>
                        <select name="metodo_pago" class="form-select form-select-sm" required>
                            @foreach($metodos as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Referencia / N° Comprobante</label>
                        <input type="text" name="referencia" class="form-control form-control-sm"
                               placeholder="Opcional" maxlength="100">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Notas</label>
                        <textarea name="notas" class="form-control form-control-sm" rows="2"
                                  placeholder="Opcional" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Guardar Pago
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- ══════════════════════════════════════════════
     MODAL: Editar Pago
══════════════════════════════════════════════ --}}
@can('editar-pago')
<div class="modal fade" id="modalEditarPago" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="formEditarPago" action="">
            @csrf @method('PATCH')
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(90deg,#1a1a1a,#2d2d2d);color:#fff;">
                    <h5 class="modal-title"><i class="bi bi-pencil me-1"></i> Editar Pago</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Monto <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" id="editMonto" name="monto" step="0.01" min="0.01"
                                   class="form-control" required>
                        </div>
                        <div id="editMontoHint" class="form-text"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Fecha de Pago <span class="text-danger">*</span></label>
                        <input type="date" id="editFecha" name="fecha_pago" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Método de Pago <span class="text-danger">*</span></label>
                        <select id="editMetodo" name="metodo_pago" class="form-select form-select-sm" required>
                            @foreach($metodos as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Referencia / N° Comprobante</label>
                        <input type="text" id="editReferencia" name="referencia" class="form-control form-control-sm"
                               maxlength="100">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Notas</label>
                        <textarea id="editNotas" name="notas" class="form-control form-control-sm" rows="2"
                                  maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Actualizar Pago
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- ══════════════════════════════════════════════
     MODAL: Editar Cuenta
══════════════════════════════════════════════ --}}
@can('editar-cuenta-cobrar')
<div class="modal fade" id="modalEditarCuenta" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('cobros.update', $cobro->id) }}">
            @csrf @method('PATCH')
            <div class="modal-content">
                <div class="modal-header" style="background:linear-gradient(90deg,#1a1a1a,#2d2d2d);color:#fff;">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> Editar Cuenta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Fecha de Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="form-control form-control-sm"
                               value="{{ $cobro->fecha_vencimiento?->format('Y-m-d') }}">
                        <div class="form-text">Dejar vacío para sin fecha de vencimiento.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Notas</label>
                        <textarea name="notas" class="form-control form-control-sm" rows="3"
                                  maxlength="1000" placeholder="Observaciones sobre esta cuenta...">{{ $cobro->notas }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- ══════════════════════════════════════════════
     MODAL: Anular Pago
══════════════════════════════════════════════ --}}
@can('borrar-pago')
<div class="modal fade" id="modalAnularPago" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="formAnularPago" action="">
            @csrf @method('PATCH')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-slash-circle me-1"></i> Anular Pago</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">
                        Está a punto de anular el pago de <strong id="anularMonto"></strong>
                        del <strong id="anularFecha"></strong>. Esta acción quedará registrada en el historial.
                    </p>
                    <div class="mb-0">
                        <label class="form-label small">Motivo de anulación <span class="text-danger">*</span></label>
                        <textarea name="motivo_anulacion" class="form-control form-control-sm" rows="3"
                                  required maxlength="500"
                                  placeholder="Describa el motivo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bi bi-slash-circle me-1"></i> Confirmar Anulación
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

@push('scripts')
<script>
function abrirEditarPago(id, monto, fecha, metodo, referencia, notas, maxMonto) {
    document.getElementById('formEditarPago').action =
        '{{ url("cobros/" . $cobro->id . "/pagos") }}/' + id;
    const input = document.getElementById('editMonto');
    input.value = monto;
    input.max   = maxMonto;
    document.getElementById('editMontoHint').textContent =
        'Máximo permitido: $' + parseFloat(maxMonto).toFixed(2);
    document.getElementById('editFecha').value      = fecha;
    document.getElementById('editMetodo').value     = metodo;
    document.getElementById('editReferencia').value = referencia;
    document.getElementById('editNotas').value      = notas;
    new bootstrap.Modal(document.getElementById('modalEditarPago')).show();
}

function abrirAnularPago(id, monto, fecha) {
    document.getElementById('formAnularPago').action =
        '{{ url("cobros/" . $cobro->id . "/pagos") }}/' + id + '/anular';
    document.getElementById('anularMonto').textContent = monto;
    document.getElementById('anularFecha').textContent = fecha;
    document.querySelector('#modalAnularPago textarea[name="motivo_anulacion"]').value = '';
    new bootstrap.Modal(document.getElementById('modalAnularPago')).show();
}
</script>
@endpush

@endsection
