@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title mb-0">
            @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Logo">
            @else
                <i class="bi bi-person-fill title-icon"></i>
            @endif
            Ficha de Cliente
        </h4>
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="row g-4">

        {{-- ── Col izquierda: datos + cartera ────────────────── --}}
        <div class="col-lg-4">

            {{-- Datos del cliente --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-person-vcard fs-5"></i>
                    <span class="fw-semibold">Datos del Cliente</span>
                    <span class="badge ms-auto bg-{{ $cliente->estado === 'Activo' ? 'success' : 'secondary' }}">
                        {{ $cliente->estado }}
                    </span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Código</dt>
                        <dd class="col-7">
                            <span class="badge bg-secondary fw-normal">{{ $cliente->codigo_cliente }}</span>
                        </dd>

                        <dt class="col-5 text-muted">Identificación</dt>
                        <dd class="col-7">{{ $cliente->identificacion_clientes }}</dd>

                        @if($cliente->nombres_clientes || $cliente->apellidos_clientes)
                        <dt class="col-5 text-muted">Nombres</dt>
                        <dd class="col-7">{{ trim($cliente->nombres_clientes . ' ' . $cliente->apellidos_clientes) }}</dd>
                        @endif

                        @if($cliente->razon_social)
                        <dt class="col-5 text-muted">Razón Social</dt>
                        <dd class="col-7">{{ $cliente->razon_social }}</dd>
                        @endif

                        <dt class="col-5 text-muted">Email</dt>
                        <dd class="col-7">
                            @if($cliente->email_cliente)
                                <a href="mailto:{{ $cliente->email_cliente }}">{{ $cliente->email_cliente }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-5 text-muted">Celular</dt>
                        <dd class="col-7">
                            @if($cliente->celular_clientes)
                                <a href="tel:{{ $cliente->celular_clientes }}">{{ $cliente->celular_clientes }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        <dt class="col-5 text-muted">Registrado</dt>
                        <dd class="col-7">{{ $cliente->created_at->format('d/m/Y') }}</dd>

                        @if($cliente->claves_observaciones)
                        <dt class="col-5 text-muted">Claves / Obs.</dt>
                        <dd class="col-7" style="white-space:pre-wrap">{{ $cliente->claves_observaciones }}</dd>
                        @endif
                    </dl>
                </div>
                @can('editar-cliente')
                <div class="card-footer bg-transparent text-end">
                    <button class="btn btn-sm btn-outline-secondary"
                            onclick="modalEditarCliente()">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </button>
                </div>
                @endcan
            </div>

            {{-- Resumen de cartera --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-wallet2 fs-5"></i>
                    <span class="fw-semibold">Estado de Cartera</span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end mb-1">
                        <span class="small text-muted">Total Facturado</span>
                        <span class="fw-bold">${{ number_format($totalFacturado, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mb-1">
                        <span class="small text-muted">Total Pagado</span>
                        <span class="text-success fw-bold">${{ number_format($totalPagado, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <span class="small text-muted">Saldo Pendiente</span>
                        <span class="fw-bold {{ $saldoPendiente > 0 ? 'text-danger' : 'text-success' }}">
                            ${{ number_format($saldoPendiente, 2) }}
                        </span>
                    </div>

                    @if($totalFacturado > 0)
                    @php $pct = min(100, round(($totalPagado / $totalFacturado) * 100)); @endphp
                    <div class="progress" style="height:8px" title="{{ $pct }}% pagado">
                        <div class="progress-bar bg-{{ $pct >= 100 ? 'success' : ($pct > 0 ? 'warning' : 'danger') }}"
                             style="width:{{ $pct }}%"></div>
                    </div>
                    <div class="text-end small text-muted mt-1">{{ $pct }}% pagado</div>
                    @endif
                </div>
            </div>

            {{-- Contadores por estado --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart fs-5"></i>
                    <span class="fw-semibold">Proyectos por Estado</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0 small">
                        <tbody>
                            <tr>
                                <td><span class="badge bg-secondary">Pendiente</span></td>
                                <td class="text-end fw-semibold">{{ $contadores['pendiente'] }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">En proceso</span></td>
                                <td class="text-end fw-semibold">{{ $contadores['proceso'] }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Terminado</span></td>
                                <td class="text-end fw-semibold">{{ $contadores['terminado'] }}</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">Cancelado</span></td>
                                <td class="text-end fw-semibold">{{ $contadores['cancelado'] }}</td>
                            </tr>
                            <tr class="table-light">
                                <td class="fw-semibold">Total</td>
                                <td class="text-end fw-bold">{{ $trabajos->count() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- ── Col derecha: tabla de proyectos ───────────────── --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center gap-2">
                    <i class="bi bi-folder2-open fs-5"></i>
                    <span class="fw-semibold">Proyectos</span>
                    <span class="badge bg-secondary ms-1">{{ $trabajos->count() }}</span>
                    @can('crear-trabajo')
                    <a href="{{ route('proyectos.create') }}?cliente_id={{ $cliente->id }}"
                       class="btn btn-primary btn-sm ms-auto">
                        <i class="bi bi-folder-plus me-1"></i> Nuevo Proyecto
                    </a>
                    @endcan
                </div>

                @if($trabajos->isEmpty())
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                    Este cliente no tiene proyectos registrados.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                                <th>Monto</th>
                                <th>Cobro</th>
                                <th>Urgencia</th>
                                <th>Inicio</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trabajos as $trabajo)
                            <tr>
                                <td class="text-muted">{{ $trabajo->id }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $trabajo->servicio->nombre_servicio ?? '—' }}</span>
                                    @if($trabajo->numero_tramite)
                                        <br><span class="text-muted">Trámite: {{ $trabajo->numero_tramite }}</span>
                                    @endif
                                    @if($trabajo->razon)
                                        <br><span class="text-muted fst-italic">{{ Str::limit($trabajo->razon, 60) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $trabajo->estado_badge }}">
                                        {{ $trabajo->estado_label }}
                                    </span>
                                </td>
                                <td class="text-nowrap">${{ number_format($trabajo->monto_total, 2) }}</td>
                                <td>
                                    @if($trabajo->cuentaCobrar)
                                        <span class="badge bg-{{ $trabajo->cuentaCobrar->estado_pago_badge }}">
                                            {{ $trabajo->cuentaCobrar->estado_pago_label }}
                                        </span>
                                        <br>
                                        <span class="text-muted">
                                            ${{ number_format($trabajo->cuentaCobrar->saldo, 2) }} pendiente
                                        </span>
                                    @else
                                        <span class="text-muted">Sin cuenta</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $trabajo->urgencia_badge }}">
                                        {{ ucfirst($trabajo->nivel_urgencia) }}
                                    </span>
                                </td>
                                <td class="text-nowrap text-muted">
                                    {{ $trabajo->fecha_inicio ? \Carbon\Carbon::parse($trabajo->fecha_inicio)->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ route('proyectos.show', $trabajo->id) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="Ver proyecto">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

{{-- Modal editar cliente (reutiliza el mismo formulario de index) --}}
@can('editar-cliente')
<form id="form-editar-show" action="{{ route('clientes.update', $cliente->id) }}" method="POST" class="d-none">
    @csrf @method('PUT')
    <input name="nombres_clientes"        id="es-nombres">
    <input name="apellidos_clientes"      id="es-apellidos">
    <input name="razon_social"            id="es-razon">
    <input name="identificacion_clientes" id="es-ident">
    <input name="email_cliente"           id="es-email">
    <input name="celular_clientes"        id="es-celular">
    <input name="estado"                  id="es-estado">
    <textarea name="claves_observaciones" id="es-claves"></textarea>
</form>
@endcan

@push('scripts')
<script>
@can('editar-cliente')
function modalEditarCliente() {
    const d = {
        nombres:  '{{ addslashes($cliente->nombres_clientes) }}',
        apellidos:'{{ addslashes($cliente->apellidos_clientes) }}',
        razon:    '{{ addslashes($cliente->razon_social ?? '') }}',
        ident:    '{{ addslashes($cliente->identificacion_clientes) }}',
        email:    '{{ addslashes($cliente->email_cliente ?? '') }}',
        celular:  '{{ addslashes($cliente->celular_clientes ?? '') }}',
        estado:   '{{ $cliente->estado }}',
        claves:   '{{ str_replace(["\r\n","\n","\r"], "\\n", addslashes($cliente->claves_observaciones ?? '')) }}',
    };

    Swal.fire({
        title: 'Editar Cliente',
        html: `
        <div class="text-start">
            <div class="row g-2">
                <div class="col-12">
                    <label class="form-label small mb-1">Identificación <span class="text-danger">*</span></label>
                    <input id="es-swal-ident" class="form-control form-control-sm" value="${d.ident}">
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Nombres</label>
                    <input id="es-swal-nombres" class="form-control form-control-sm" value="${d.nombres}">
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Apellidos</label>
                    <input id="es-swal-apellidos" class="form-control form-control-sm" value="${d.apellidos}">
                </div>
                <div class="col-8">
                    <label class="form-label small mb-1">Razón Social</label>
                    <input id="es-swal-razon" class="form-control form-control-sm" value="${d.razon}">
                </div>
                <div class="col-4">
                    <label class="form-label small mb-1">Estado</label>
                    <select id="es-swal-estado" class="form-select form-select-sm">
                        <option value="Activo"   ${d.estado === 'Activo'   ? 'selected' : ''}>Activo</option>
                        <option value="Inactivo" ${d.estado === 'Inactivo' ? 'selected' : ''}>Inactivo</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Email</label>
                    <input id="es-swal-email" type="email" class="form-control form-control-sm" value="${d.email}">
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Celular</label>
                    <input id="es-swal-celular" class="form-control form-control-sm"
                           inputmode="numeric" pattern="[0-9]*"
                           oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                           value="${d.celular}">
                </div>
                <div class="col-12">
                    <label class="form-label small mb-1">Claves / Observaciones</label>
                    <textarea id="es-swal-claves" class="form-control form-control-sm" rows="3">${d.claves.replace(/\\n/g, '\n')}</textarea>
                </div>
            </div>
        </div>`,
        width: 520,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const ident = document.getElementById('es-swal-ident').value.trim();
            if (!ident) { Swal.showValidationMessage('La identificación es obligatoria.'); return false; }
            return true;
        }
    }).then(result => {
        if (!result.isConfirmed) return;
        const f = document.getElementById('form-editar-show');
        document.getElementById('es-nombres').value   = document.getElementById('es-swal-nombres').value.trim();
        document.getElementById('es-apellidos').value = document.getElementById('es-swal-apellidos').value.trim();
        document.getElementById('es-razon').value     = document.getElementById('es-swal-razon').value.trim();
        document.getElementById('es-ident').value     = document.getElementById('es-swal-ident').value.trim();
        document.getElementById('es-email').value     = document.getElementById('es-swal-email').value.trim();
        document.getElementById('es-celular').value   = document.getElementById('es-swal-celular').value.trim();
        document.getElementById('es-estado').value    = document.getElementById('es-swal-estado').value;
        document.getElementById('es-claves').value    = document.getElementById('es-swal-claves').value;
        f.submit();
    });
}
@endcan
</script>
@endpush
@endsection
