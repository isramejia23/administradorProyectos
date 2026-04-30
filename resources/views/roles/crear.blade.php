{{-- resources/views/roles/crear.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Crear Nuevo Rol</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>¡Error!</strong> Por favor revisa los campos obligatorios.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('roles.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nombre del Rol</label>
            <input type="text" name="name" class="form-control" placeholder="Ejemplo: Administrador">
        </div>

        <div class="mb-3">
            <label for="permission" class="form-label">Permisos</label>
            <div class="row">
                @foreach($permission as $value)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="checkbox" name="permission[]" value="{{ $value->id }}" class="form-check-input">
                            <label class="form-check-label">{{ $value->name }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Guardar
        </button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </form>
</div>
@endsection
