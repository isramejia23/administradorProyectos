@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="page-title">
            @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Logo">
            @else
                <i class="bi bi-person-fill title-icon"></i>
            @endif
            Usuarios
        </h4>
        @can('crear-usuario')
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">Nuevo Usuario</a>
        @endcan
    </div>

    {{-- Filtros --}}
    <form action="{{ route('usuarios.index') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1 small">Búsqueda libre</label>
                <input type="text" name="buscar" class="form-control"
                       placeholder="Nombre, apellido, email, cédula..."
                       value="{{ request('buscar') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Departamento</label>
                <select name="departamento_id" class="form-select">
                    <option value="">Todos los departamentos</option>
                    @foreach($departamentos as $id => $nombre)
                        <option value="{{ $id }}" {{ request('departamento_id') == $id ? 'selected' : '' }}>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small">Rol</label>
                <select name="rol" class="form-select">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $rol)
                        <option value="{{ $rol }}" {{ request('rol') == $rol ? 'selected' : '' }}>
                            {{ $rol }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary w-100" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Departamento</th>
                <th>Estado</th>
                <th>Rol(es)</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $usuario)
                <tr>
                    <td>{{ $usuario->nombre }}</td>
                    <td>{{ $usuario->apellido }}</td>
                    <td>{{ $usuario->email }}</td>
                    <td>{{ $usuario->departamento?->nombre_departamento ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $usuario->estado === 'Activo' ? 'success' : 'secondary' }}">
                            {{ $usuario->estado }}
                        </span>
                    </td>
                    <td>
                        @foreach($usuario->roles as $role)
                            <span class="badge bg-primary">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="text-center">
                        @can('editar-usuario')
                        <a href="{{ route('usuarios.edit', $usuario->id) }}"
                           class="btn btn-sm btn-outline-secondary"
                           title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endcan
                        @can('borrar-usuario')
                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Eliminar"
                                    onclick="confirmDelete(event, this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No se encontraron usuarios.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $usuarios->links() }}
</div>
@endsection
