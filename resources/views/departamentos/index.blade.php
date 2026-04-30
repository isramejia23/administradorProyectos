@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Logo">
            @else
                <i class="bi bi-building-fill title-icon"></i>
            @endif
            Departamentos
        </h4>
        @can('crear-departamento')
        <button type="button" class="btn btn-primary btn-sm px-3" onclick="modalCrear()">
            <i class="bi bi-plus-lg me-1"></i>Nuevo Departamento
        </button>
        @endcan
    </div>

    @php
        $colorList = ['#0d6efd','#198754','#6f42c1','#0dcaf0','#fd7e14','#20c997','#dc3545','#6c757d','#d63384','#e67e22'];
        $iconMap   = [
            'construct'  => 'bi-bricks',
            'obra'       => 'bi-bricks',
            'consult'    => 'bi-briefcase-fill',
            'asesor'     => 'bi-briefcase-fill',
            'diseño'     => 'bi-palette-fill',
            'tecnolog'   => 'bi-cpu-fill',
            'sistem'     => 'bi-cpu-fill',
            'finanz'     => 'bi-cash-stack',
            'contab'     => 'bi-calculator-fill',
            'admin'      => 'bi-building',
            'gerenc'     => 'bi-person-badge-fill',
            'rrhh'       => 'bi-people-fill',
            'recurso'    => 'bi-people-fill',
            'legal'      => 'bi-shield-check',
            'juridic'    => 'bi-shield-check',
            'logis'      => 'bi-truck',
            'transport'  => 'bi-truck',
            'market'     => 'bi-megaphone-fill',
            'ventas'     => 'bi-graph-up-arrow',
            'comerc'     => 'bi-graph-up-arrow',
            'compras'    => 'bi-cart-fill',
            'almac'      => 'bi-boxes',
            'producc'    => 'bi-gear-wide-connected',
            'manufactur' => 'bi-gear-wide-connected',
            'calidad'    => 'bi-patch-check-fill',
            'seguridad'  => 'bi-shield-fill-check',
            'salud'      => 'bi-heart-pulse-fill',
            'capacit'    => 'bi-mortarboard-fill',
        ];
    @endphp

    <div class="bg-white rounded shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Departamento</th>
                    <th class="text-center" style="width:110px;">Usuarios</th>
                    <th class="text-center" style="width:110px;">Proyectos</th>
                    <th class="text-center" style="width:120px;">Subtrabajos</th>
                    <th class="text-center" style="width:110px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departamentos as $depto)
                @php
                    $n     = mb_strtolower($depto->nombre_departamento);
                    $icon  = 'bi-building';
                    foreach ($iconMap as $kw => $ic) {
                        if (str_contains($n, $kw)) { $icon = $ic; break; }
                    }
                    $color = $colorList[$loop->index % count($colorList)];
                @endphp
                <tr>
                    <td class="text-muted small">{{ $depto->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:34px;height:34px;background:{{ $color }}1a;">
                                <i class="bi {{ $icon }}" style="font-size:1rem;color:{{ $color }};"></i>
                            </div>
                            <span class="fw-semibold" style="font-size:.9rem;">{{ $depto->nombre_departamento }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">{{ $depto->usuarios_count }}</span>
                    </td>
                    <td class="text-center">
                        @if($depto->proyectos_count > 0)
                        <a href="{{ route('proyectos.index', ['departamento_id' => $depto->id]) }}"
                           class="badge text-decoration-none" style="background:#0d6efd;">
                            {{ $depto->proyectos_count }}
                        </a>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($depto->subtrabajos_count > 0)
                        <span class="badge" style="background:#6f42c1;">{{ $depto->subtrabajos_count }}</span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('departamentos.show', $depto->id) }}"
                           class="btn btn-sm btn-outline-info border-0" title="Ver detalle">
                            <i class="bi bi-eye" style="font-size:1rem;"></i>
                        </a>
                        @can('editar-departamento')
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary border-0"
                                title="Editar"
                                onclick="modalEditar({{ $depto->id }}, '{{ addslashes($depto->nombre_departamento) }}')">
                            <i class="bi bi-pencil" style="font-size:1rem;"></i>
                        </button>
                        @endcan
                        @can('borrar-departamento')
                        <form action="{{ route('departamentos.destroy', $depto->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger border-0"
                                    title="Eliminar"
                                    onclick="confirmDelete(event, this)">
                                <i class="bi bi-trash" style="font-size:1rem;"></i>
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-building d-block mb-2" style="font-size:2rem;"></i>
                        No hay departamentos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($departamentos->hasPages())
    <div class="mt-3">{{ $departamentos->links() }}</div>
    @endif

</div>

<form id="form-crear" action="{{ route('departamentos.store') }}" method="POST" class="d-none">
    @csrf
    <input type="text" name="nombre_departamento" id="input-crear">
</form>

<form id="form-editar" method="POST" class="d-none">
    @csrf
    @method('PUT')
    <input type="text" name="nombre_departamento" id="input-editar">
</form>

@push('scripts')
<script>
    function modalCrear() {
        Swal.fire({
            title: 'Nuevo Departamento',
            input: 'text',
            inputLabel: 'Nombre del departamento',
            inputPlaceholder: 'Ej: Contabilidad',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: 'transparent',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value || !value.trim()) return 'El nombre es obligatorio.';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('input-crear').value = result.value.trim();
                document.getElementById('form-crear').submit();
            }
        });
    }

    function modalEditar(id, nombreActual) {
        Swal.fire({
            title: 'Editar Departamento',
            input: 'text',
            inputLabel: 'Nombre del departamento',
            inputValue: nombreActual,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: 'transparent',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value || !value.trim()) return 'El nombre es obligatorio.';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('form-editar');
                form.action = `/departamentos/${id}`;
                document.getElementById('input-editar').value = result.value.trim();
                form.submit();
            }
        });
    }

    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({ icon: 'error', title: 'Error', text: '{{ $errors->first() }}' });
    });
    @endif
</script>
@endpush
@endsection
