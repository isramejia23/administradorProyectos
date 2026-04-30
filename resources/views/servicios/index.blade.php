@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Logo">
            @else
                <i class="bi bi-briefcase-fill title-icon"></i>
            @endif
            Servicios
        </h4>
        @can('crear-servicio')
        <button class="btn btn-primary btn-sm px-3" onclick="modalCrear()">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Servicio
        </button>
        @endcan
    </div>

    {{-- Filtros --}}
    <form action="{{ route('servicios.index') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Búsqueda</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Nombre del servicio..."
                       value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Departamento</label>
                <select name="departamento_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($departamentos as $id => $nombre)
                        <option value="{{ $id }}" {{ request('departamento_id') == $id ? 'selected' : '' }}>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Estatus</label>
                <select name="estatus" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Activo"   {{ request('estatus') === 'Activo'   ? 'selected' : '' }}>Activo</option>
                    <option value="Inactivo" {{ request('estatus') === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="{{ route('servicios.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="bg-white rounded shadow-sm">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Departamento</th>
                    <th>Precio sugerido</th>
                    <th>Estatus</th>
                    @canany(['editar-servicio', 'borrar-servicio'])
                    <th class="text-center" style="width:90px;">Acciones</th>
                    @endcanany
                </tr>
            </thead>
            <tbody>
                @forelse($servicios as $servicio)
                <tr>
                    <td>{{ $servicio->nombre_servicio }}</td>
                    <td>{{ $servicio->departamento?->nombre_departamento ?? '—' }}</td>
                    <td>${{ number_format($servicio->precio_sugerido, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $servicio->estatus === 'Activo' ? 'success' : 'secondary' }}">
                            {{ $servicio->estatus }}
                        </span>
                    </td>
                    @canany(['editar-servicio', 'borrar-servicio'])
                    <td class="text-center">
                        @can('editar-servicio')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary"
                                title="Editar"
                                onclick="modalEditar(
                                    {{ $servicio->id }},
                                    '{{ addslashes($servicio->nombre_servicio) }}',
                                    {{ $servicio->departamento_id }},
                                    {{ $servicio->precio_sugerido }},
                                    '{{ $servicio->estatus }}'
                                )">
                            <i class="bi bi-pencil"></i>
                        </button>
                        @endcan
                        @can('borrar-servicio')
                        <form action="{{ route('servicios.destroy', $servicio->id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                    onclick="confirmDelete(event, this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                    @endcanany
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay servicios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $servicios->links() }}</div>
</div>

@can('crear-servicio')
<form id="form-crear" action="{{ route('servicios.store') }}" method="POST" class="d-none">
    @csrf
    <input name="nombre_servicio"  id="c-nombre">
    <input name="departamento_id"  id="c-depto">
    <input name="precio_sugerido"  id="c-precio">
    <input name="estatus"          id="c-estatus">
</form>
@endcan

@can('editar-servicio')
<form id="form-editar" method="POST" class="d-none">
    @csrf @method('PUT')
    <input name="nombre_servicio"  id="e-nombre">
    <input name="departamento_id"  id="e-depto">
    <input name="precio_sugerido"  id="e-precio">
    <input name="estatus"          id="e-estatus">
</form>
@endcan

@push('scripts')
<script>
const departamentos = @json($departamentos);

function buildDeptoOptions(selectedId) {
    return Object.entries(departamentos)
        .map(([id, nombre]) =>
            `<option value="${id}" ${id == selectedId ? 'selected' : ''}>${nombre}</option>`
        ).join('');
}

function formHtml(data = {}) {
    return `
        <div class="text-start">
            <div class="mb-2">
                <label class="form-label small mb-1">Nombre del servicio</label>
                <input id="swal-nombre" class="form-control form-control-sm" value="${data.nombre || ''}">
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Departamento</label>
                <select id="swal-depto" class="form-select form-select-sm">
                    <option value="">-- Seleccione --</option>
                    ${buildDeptoOptions(data.depto || '')}
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Precio sugerido</label>
                <input id="swal-precio" type="number" step="0.01" min="0" class="form-control form-control-sm" value="${data.precio || ''}">
            </div>
            <div class="mb-1">
                <label class="form-label small mb-1">Estatus</label>
                <select id="swal-estatus" class="form-select form-select-sm">
                    <option value="Activo"   ${data.estatus === 'Activo'   ? 'selected' : ''}>Activo</option>
                    <option value="Inactivo" ${data.estatus === 'Inactivo' ? 'selected' : ''}>Inactivo</option>
                </select>
            </div>
        </div>`;
}

function getValues() {
    return {
        nombre:  document.getElementById('swal-nombre')?.value.trim(),
        depto:   document.getElementById('swal-depto')?.value,
        precio:  document.getElementById('swal-precio')?.value,
        estatus: document.getElementById('swal-estatus')?.value,
    };
}

function validate(v) {
    if (!v.nombre)  return 'El nombre es obligatorio.';
    if (!v.depto)   return 'Seleccione un departamento.';
    if (v.precio === '' || isNaN(v.precio)) return 'Ingrese un precio válido.';
    return null;
}

@can('crear-servicio')
function modalCrear() {
    Swal.fire({
        title: 'Nuevo Servicio',
        html: formHtml(),
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: 'transparent',
        reverseButtons: true,
        preConfirm: () => {
            const v = getValues();
            const err = validate(v);
            if (err) { Swal.showValidationMessage(err); return false; }
            return v;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const v = result.value;
            document.getElementById('c-nombre').value  = v.nombre;
            document.getElementById('c-depto').value   = v.depto;
            document.getElementById('c-precio').value  = v.precio;
            document.getElementById('c-estatus').value = v.estatus;
            document.getElementById('form-crear').submit();
        }
    });
}

@endcan

@can('editar-servicio')
function modalEditar(id, nombre, depto, precio, estatus) {
    Swal.fire({
        title: 'Editar Servicio',
        html: formHtml({ nombre, depto, precio, estatus }),
        showCancelButton: true,
        confirmButtonText: 'Actualizar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: 'transparent',
        reverseButtons: true,
        preConfirm: () => {
            const v = getValues();
            const err = validate(v);
            if (err) { Swal.showValidationMessage(err); return false; }
            return v;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const v = result.value;
            const form = document.getElementById('form-editar');
            form.action = `/servicios/${id}`;
            document.getElementById('e-nombre').value  = v.nombre;
            document.getElementById('e-depto').value   = v.depto;
            document.getElementById('e-precio').value  = v.precio;
            document.getElementById('e-estatus').value = v.estatus;
            form.submit();
        }
    });
}

@endcan

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({ icon:'error', title:'Error', text:'{{ $errors->first() }}' });
});
@endif
</script>
@endpush
@endsection
