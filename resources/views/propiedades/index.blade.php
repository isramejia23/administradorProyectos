@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            <i class="bi bi-house-door-fill title-icon"></i>
            Propiedades
        </h4>
        @can('crear-propiedad')
        <a href="{{ route('propiedades.create') }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> Nueva Propiedad
        </a>
        @endcan
    </div>

    {{-- Filtros --}}
    <form action="{{ route('propiedades.index') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Búsqueda libre</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Título, ciudad, sector, dueño..."
                       value="{{ request('buscar') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="casa"            {{ request('tipo') === 'casa'            ? 'selected' : '' }}>Casa</option>
                    <option value="departamento"    {{ request('tipo') === 'departamento'    ? 'selected' : '' }}>Departamento</option>
                    <option value="terreno"         {{ request('tipo') === 'terreno'         ? 'selected' : '' }}>Terreno</option>
                    <option value="local_comercial" {{ request('tipo') === 'local_comercial' ? 'selected' : '' }}>Local Comercial</option>
                    <option value="oficina"         {{ request('tipo') === 'oficina'         ? 'selected' : '' }}>Oficina</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="disponible" {{ request('estado') === 'disponible' ? 'selected' : '' }}>Disponible</option>
                    <option value="en_proceso" {{ request('estado') === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                    <option value="vendido"    {{ request('estado') === 'vendido'    ? 'selected' : '' }}>Vendido</option>
                    <option value="cancelado"  {{ request('estado') === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Ciudad</label>
                <input type="text" name="ciudad" class="form-control form-control-sm"
                       placeholder="Ciudad..." value="{{ request('ciudad') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label mb-1 small">Precio mín.</label>
                <input type="number" name="precio_min" class="form-control form-control-sm"
                       placeholder="0" min="0" value="{{ request('precio_min') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label mb-1 small">Precio máx.</label>
                <input type="number" name="precio_max" class="form-control form-control-sm"
                       placeholder="∞" min="0" value="{{ request('precio_max') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="{{ route('propiedades.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="bg-white rounded shadow-sm">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th style="width:60px;"></th>
                    <th>Título</th>
                    <th>Tipo</th>
                    <th>Ciudad / Sector</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Dueño</th>
                    <th class="text-center" style="width:120px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($propiedades as $propiedad)
                <tr>
                    <td>
                        @if($propiedad->portada)
                            <img src="{{ Storage::url($propiedad->portada->ruta) }}"
                                 alt="Portada"
                                 style="width:50px;height:40px;object-fit:cover;border-radius:4px;">
                        @else
                            <div style="width:50px;height:40px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                                <i class="bi bi-image text-muted" style="font-size:.9rem;"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        @can('ver-propiedad')
                        <a href="{{ route('propiedades.show', $propiedad) }}" class="fw-semibold text-decoration-none text-dark">
                            {{ $propiedad->titulo }}
                        </a>
                        @else
                        <span class="fw-semibold">{{ $propiedad->titulo }}</span>
                        @endcan
                    </td>
                    <td><span class="badge bg-dark">{{ $propiedad->tipo_label }}</span></td>
                    <td>
                        {{ $propiedad->ciudad }}
                        @if($propiedad->sector)
                            <br><small class="text-muted">{{ $propiedad->sector }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $propiedad->precio_formateado }}
                        @if($propiedad->negociable)
                            <br><small class="text-success"><i class="bi bi-check-circle-fill"></i> Negociable</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $propiedad->estado_badge }}">{{ $propiedad->estado_label }}</span>
                    </td>
                    <td>
                        {{ $propiedad->nombre_dueno }}
                        <br><small class="text-muted">{{ $propiedad->telefono_dueno }}</small>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            @can('ver-propiedad')
                            <a href="{{ route('propiedades.show', $propiedad) }}"
                               class="btn btn-sm btn-outline-info" title="Ver detalles">
                                <i class="bi bi-eye"></i>
                            </a>
                            @endcan
                            @can('editar-propiedad')
                            <a href="{{ route('propiedades.edit', $propiedad) }}"
                               class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('borrar-propiedad')
                            <form action="{{ route('propiedades.destroy', $propiedad) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                        onclick="confirmDelete(event, this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No hay propiedades registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $propiedades->links() }}</div>
</div>
@endsection
