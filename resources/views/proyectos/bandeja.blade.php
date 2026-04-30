@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-inbox-fill title-icon"></i> Bandeja de Entrada
        </h4>
        @can('crear-trabajo')
        <a href="{{ route('proyectos.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Proyecto
        </a>
        @endcan
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-0" id="bandejaTab">
        <li class="nav-item">
            <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-proyectos">
                <i class="bi bi-kanban me-1"></i> Proyectos sin responsable
                @if($proyectos->total() > 0)
                <span class="badge bg-danger ms-1">{{ $proyectos->total() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-subtrabajos">
                <i class="bi bi-diagram-3 me-1"></i> Subproyectos sin responsable
                @if($subtrabajos->total() > 0)
                <span class="badge bg-danger ms-1">{{ $subtrabajos->total() }}</span>
                @endif
            </button>
        </li>
        @if($solicitudes !== null)
        <li class="nav-item">
            <button class="nav-link fw-semibold" data-bs-toggle="tab" data-bs-target="#tab-solicitudes">
                <i class="bi bi-hourglass-split me-1"></i> Por aprobar
                @if($solicitudes->total() > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $solicitudes->total() }}</span>
                @endif
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content">

        {{-- ══ TAB PROYECTOS SIN RESPONSABLE ══ --}}
        <div class="tab-pane fade show active" id="tab-proyectos">
            <div class="bg-white rounded-bottom rounded-end shadow-sm">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Monto</th>
                            <th>Urgencia</th>
                            <th>Estado</th>
                            <th>F. Estimada</th>
                            <th class="text-center" style="width:90px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($proyectos as $proyecto)
                        <tr>
                            <td class="text-muted small">{{ $proyecto->id }}</td>
                            <td class="small" style="max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $proyecto->cliente->nombre_completo }}">{{ $proyecto->cliente->nombre_completo }}</td>
                            <td class="small">
                                {{ $proyecto->servicio->nombre_servicio }}
                                @if($proyecto->trabajo_unico)
                                <i class="bi bi-check-circle-fill ms-1 text-success" title="Trabajo Único"></i>
                                @else
                                <i class="bi bi-x-circle ms-1 text-secondary" title="Múltiples subtrabajos"></i>
                                @endif
                            </td>
                            <td class="small">${{ number_format($proyecto->monto_total, 2) }}</td>
                            <td>@include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])</td>
                            <td>
                                <span class="badge bg-{{ $proyecto->estado_badge }}">
                                    {{ $proyecto->estado_label }}
                                </span>
                            </td>
                            <td class="small">{{ $proyecto->fecha_estimada?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('proyectos.show', $proyecto->id) }}"
                                   class="btn btn-sm btn-outline-info" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('editar-trabajo')
                                <a href="{{ route('proyectos.edit', $proyecto->id) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Tomar / Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-check2-circle me-1"></i> No hay proyectos pendientes de asignación.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $proyectos->appends(['sub' => request('sub'), 'sol' => request('sol')])->links() }}</div>
        </div>

        {{-- ══ TAB SUBPROYECTOS SIN RESPONSABLE ══ --}}
        <div class="tab-pane fade" id="tab-subtrabajos">
            <div class="bg-white rounded-bottom rounded-end shadow-sm">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Proyecto / Cliente</th>
                            <th>Servicio</th>
                            <th>Departamento</th>
                            <th>Costo</th>
                            <th>Estado</th>
                            <th>F. Estimada</th>
                            <th class="text-center" style="width:90px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subtrabajos as $sub)
                        <tr>
                            <td class="text-muted small">{{ $sub->id }}</td>
                            <td class="small">
                                <span class="text-muted">Proy. #{{ $sub->trabajo_id }}</span><br>
                                <span>{{ $sub->trabajo->cliente->nombre_completo }}</span>
                            </td>
                            <td class="small">{{ $sub->servicio?->nombre_servicio ?? '—' }}</td>
                            <td class="small">{{ $sub->departamento->nombre_departamento }}</td>
                            <td class="small">${{ number_format($sub->costo_especialista, 2) }}
                                @if($sub->precio_incluido)
                                <span class="badge bg-success ms-1" style="font-size:.6rem;">Incluido</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $sub->estado_badge }}">
                                    {{ $sub->estado_label }}
                                </span>
                            </td>
                            <td class="small">{{ $sub->fecha_estimada?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('proyectos.show', $sub->trabajo_id) }}"
                                   class="btn btn-sm btn-outline-info" title="Ver proyecto">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @can('editar-subtrabajo')
                                <form action="{{ route('subtrabajos.aceptar', [$sub->trabajo_id, $sub->id]) }}"
                                      method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Aceptar subtrabajo">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-check2-circle me-1"></i> No hay subproyectos pendientes de asignación.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $subtrabajos->appends(['proy' => request('proy'), 'sol' => request('sol')])->links() }}</div>
        </div>

        {{-- ══ TAB PROYECTOS POR APROBAR ══ --}}
        @if($solicitudes !== null)
        <div class="tab-pane fade" id="tab-solicitudes">
            <div class="bg-white rounded-bottom rounded-end shadow-sm">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Departamento</th>
                            <th>Servicio</th>
                            <th>Monto Est.</th>
                            <th>Urgencia</th>
                            <th>Enviada</th>
                            <th class="text-center" style="width:100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes as $proyecto)
                        <tr class="table-warning">
                            <td class="text-muted small">{{ $proyecto->id }}</td>
                            <td class="small fw-semibold">{{ $proyecto->vendedor?->nombre_completo ?? '—' }}</td>
                            <td class="small">{{ $proyecto->cliente->nombre_completo }}</td>
                            <td class="small">{{ $proyecto->departamento->nombre_departamento }}</td>
                            <td class="small">{{ $proyecto->servicio->nombre_servicio }}</td>
                            <td class="small">${{ number_format($proyecto->monto_total, 2) }}</td>
                            <td>@include('partials.urgencia-bar', ['nivel' => $proyecto->nivel_urgencia])</td>
                            <td class="small text-muted">{{ $proyecto->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('proyectos.show', $proyecto->id) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Ver detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('proyectos.aprobar', $proyecto->id) }}"
                                   class="btn btn-sm btn-success" title="Revisar y aprobar">
                                    <i class="bi bi-check2-circle"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-hourglass me-1"></i> No hay solicitudes pendientes de aprobación.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $solicitudes->appends(['proy' => request('proy'), 'sub' => request('sub')])->links() }}</div>
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
@if(request()->has('sub'))
document.querySelector('[data-bs-target="#tab-subtrabajos"]').click();
@elseif(request()->has('sol'))
document.querySelector('[data-bs-target="#tab-solicitudes"]')?.click();
@endif
</script>
@endpush

@endsection
