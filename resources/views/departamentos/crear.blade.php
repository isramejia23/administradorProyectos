@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Nuevo Departamento</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('departamentos.store') }}" method="POST" class="col-md-5">
        @csrf
        <div class="mb-3">
            <label for="nombre_departamento" class="form-label">Nombre del Departamento</label>
            <input type="text" name="nombre_departamento" id="nombre_departamento"
                   class="form-control" value="{{ old('nombre_departamento') }}" required>
        </div>
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('departamentos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
