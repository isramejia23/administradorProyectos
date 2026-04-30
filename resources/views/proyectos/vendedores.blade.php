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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-people-fill title-icon"></i> Proyectos por Vendedor
        </h4>
    </div>

    {{-- Filtros --}}
    <form action="{{ route('proyectos.vendedores') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label mb-1 small">Cliente / Servicio</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Nombre cliente o servicio..." value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Vendedor</label>
                <select name="vendedor_id" id="sel-vendedor" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="null" {{ request('vendedor_id') === 'null' ? 'selected' : '' }}>Sin vendedor</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ request('vendedor_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nombre_completo }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Estado</label>
                <select name="estado_trabajo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado_trabajo') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="proceso"   {{ request('estado_trabajo') === 'proceso'   ? 'selected' : '' }}>En Proceso</option>
                    <option value="terminado" {{ request('estado_trabajo') === 'terminado' ? 'selected' : '' }}>Terminado</option>
                    <option value="cancelado" {{ request('estado_trabajo') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('proyectos.vendedores') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>

    {{-- Alerta: proyectos sin vendedor (solo si no hay filtro activo) --}}
    @if(! request()->filled('vendedor_id') && ! request()->filled('buscar'))
    @php $sinVendedor = \App\Models\Trabajo::whereNull('vendedor_id')->count(); @endphp
    @if($sinVendedor > 0)
    <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.88rem;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <span>
            <strong>{{ $sinVendedor }}</strong> {{ $sinVendedor === 1 ? 'proyecto no tiene' : 'proyectos no tienen' }} vendedor asignado.
            <a href="{{ route('proyectos.vendedores', ['vendedor_id' => 'null']) }}" class="alert-link ms-1">Ver proyectos sin vendedor</a>
        </span>
    </div>
    @endif
    @endif

    <div class="bg-white rounded shadow-sm">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Vendedor</th>
                    <th>Monto Total</th>
                    <th>% Comisión</th>
                    <th>Comisión</th>
                    <th>Progreso</th>
                    <th>Estado</th>
                    <th>F. Inicio</th>
                    <th>F. Estimada</th>
                    <th class="text-center" style="width:90px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proyectos as $proyecto)
                @php
                    $total      = $proyecto->subtrabajos->count();
                    $terminados = $proyecto->subtrabajos->where('estado', 'terminado')->count();
                    $pct        = $total > 0 ? round($terminados / $total * 100) : 0;
                @endphp
                <tr>
                    <td class="text-muted small">{{ $proyecto->id }}</td>
                    <td class="small">{{ $proyecto->cliente->nombre_completo }}</td>
                    <td class="small">{{ $proyecto->servicio->nombre_servicio }}</td>
                    <td class="small">
                        @if($proyecto->vendedor)
                            {{ $proyecto->vendedor->nombre_completo }}
                        @else
                            <span class="badge bg-naranja">Sin vendedor</span>
                        @endif
                    </td>
                    <td class="small">${{ number_format($proyecto->monto_total, 2) }}</td>
                    <td class="small">
                        @if($proyecto->porcentaje_comision !== null)
                            <span class="badge bg-secondary">{{ $proyecto->porcentaje_comision }}%</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small fw-semibold">
                        @if($proyecto->porcentaje_comision !== null)
                            ${{ number_format($proyecto->monto_comision, 2) }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="min-width:120px;">
                        @if($proyecto->trabajo_unico)
                            <span class="text-muted small">Único</span>
                        @else
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                </div>
                                <span class="small text-muted" style="white-space:nowrap;">{{ $terminados }}/{{ $total }}</span>
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $proyecto->estado_badge }}">
                            {{ $proyecto->estado_label }}
                        </span>
                    </td>
                    <td class="small">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                    <td class="small">{{ $proyecto->fecha_estimada?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                        <a href="{{ route('proyectos.show', $proyecto->id) }}"
                           class="btn btn-sm btn-outline-info" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                        @can('asignar-vendedor')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                title="Asignar vendedor"
                                onclick="modalVendedor({{ $proyecto->id }}, '{{ $proyecto->cliente->nombre_completo }}', {{ $proyecto->vendedor_id ?? 'null' }})">
                            <i class="bi bi-person-plus"></i>
                        </button>
                        @endcan
                        @can('editar-comision')
                        <button type="button"
                                class="btn btn-sm btn-outline-success"
                                title="Asignar comisión"
                                onclick="modalComision({{ $proyecto->id }}, '{{ $proyecto->cliente->nombre_completo }}', {{ $proyecto->porcentaje_comision ?? 'null' }})">
                            <i class="bi bi-cash-coin"></i>
                        </button>
                        @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center text-muted py-4">
                        <i class="bi bi-people me-1"></i> No se encontraron proyectos con los filtros aplicados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $proyectos->links() }}</div>

</div>
@endsection

@can('asignar-vendedor')
<form id="form-vendedor" method="POST" class="d-none">
    @csrf @method('PATCH')
    <input type="hidden" name="vendedor_id" id="input-vendedor">
</form>
@endcan

@can('editar-comision')
<form id="form-comision" method="POST" class="d-none">
    @csrf @method('PATCH')
    <input type="number" name="porcentaje_comision" id="input-comision" step="0.01" min="0" max="100">
</form>
@endcan

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    new TomSelect('#sel-vendedor', {
        placeholder: 'Buscar vendedor...',
        allowEmptyOption: true,
    });

    @can('asignar-vendedor')
    const todosUsuarios = @json($usuarios->map(fn($u) => ['id' => $u->id, 'text' => $u->nombre_completo]));

    function modalVendedor(id, cliente, actualVendedorId) {
        Swal.fire({
            title: 'Asignar Vendedor',
            html: `
                <div class="text-start">
                    <p class="small text-muted mb-2">${cliente}</p>
                    <label class="form-label small mb-1">Vendedor</label>
                    <select id="swal-vendedor" class="form-select form-select-sm">
                        <option value="">Sin vendedor</option>
                        ${todosUsuarios.map(u =>
                            `<option value="${u.id}" ${u.id == actualVendedorId ? 'selected' : ''}>${u.text}</option>`
                        ).join('')}
                    </select>
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: 'transparent',
            reverseButtons: true,
            didOpen: () => {
                new TomSelect('#swal-vendedor', {
                    placeholder: 'Buscar por nombre...',
                    allowEmptyOption: true,
                });
            },
            preConfirm: () => document.getElementById('swal-vendedor').value
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-vendedor');
                form.action = `/proyectos/${id}/asignar-vendedor`;
                document.getElementById('input-vendedor').value = result.value;
                form.submit();
            }
        });
    }
    @endcan

    @can('editar-comision')
    function modalComision(id, cliente, actual) {
        Swal.fire({
            title: 'Asignar Comisión',
            html: `
                <div class="text-start">
                    <p class="small text-muted mb-2">${cliente}</p>
                    <label class="form-label small mb-1">Porcentaje de comisión (%)</label>
                    <div class="input-group input-group-sm">
                        <input id="swal-comision" type="number" step="0.01" min="0" max="100"
                               class="form-control" placeholder="Ej: 5.00"
                               value="${actual !== null ? actual : ''}">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="small text-muted mt-1">Deja vacío para quitar la comisión asignada.</div>
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: 'transparent',
            reverseButtons: true,
            preConfirm: () => {
                const val = document.getElementById('swal-comision').value;
                if (val !== '' && (isNaN(val) || val < 0 || val > 100)) {
                    Swal.showValidationMessage('Ingresa un valor entre 0 y 100.');
                    return false;
                }
                return val;
            }
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-comision');
                form.action = `/proyectos/${id}/comision`;
                document.getElementById('input-comision').value = result.value;
                form.submit();
            }
        });
    }
    @endcan
</script>
@endpush
