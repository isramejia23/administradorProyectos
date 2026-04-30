@extends('layouts.app')

@section('content')
@php
    $iconMap = [
        'construct' => 'bi-bricks',       'obra'       => 'bi-bricks',
        'consult'   => 'bi-briefcase-fill','asesor'     => 'bi-briefcase-fill',
        'diseño'    => 'bi-palette-fill',  'tecnolog'   => 'bi-cpu-fill',
        'sistem'    => 'bi-cpu-fill',      'finanz'     => 'bi-cash-stack',
        'contab'    => 'bi-calculator-fill','admin'     => 'bi-building',
        'gerenc'    => 'bi-person-badge-fill','rrhh'    => 'bi-people-fill',
        'recurso'   => 'bi-people-fill',   'legal'      => 'bi-shield-check',
        'juridic'   => 'bi-shield-check',  'logis'      => 'bi-truck',
        'transport' => 'bi-truck',         'market'     => 'bi-megaphone-fill',
        'ventas'    => 'bi-graph-up-arrow','comerc'     => 'bi-graph-up-arrow',
        'compras'   => 'bi-cart-fill',     'almac'      => 'bi-boxes',
        'producc'   => 'bi-gear-wide-connected','manufactur' => 'bi-gear-wide-connected',
        'calidad'   => 'bi-patch-check-fill','seguridad' => 'bi-shield-fill-check',
        'salud'     => 'bi-heart-pulse-fill','capacit'  => 'bi-mortarboard-fill',
    ];
    $n    = mb_strtolower($departamento->nombre_departamento);
    $icon = 'bi-building';
    foreach ($iconMap as $kw => $ic) {
        if (str_contains($n, $kw)) { $icon = $ic; break; }
    }
@endphp

<div class="container-fluid">

    {{-- Cabecera --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('departamentos.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="page-title mb-0">
            <i class="bi {{ $icon }} title-icon"></i>
            {{ $departamento->nombre_departamento }}
        </h4>
        @can('editar-departamento')
        <button type="button" class="btn btn-outline-secondary btn-sm ms-auto"
                onclick="modalEditar({{ $departamento->id }}, '{{ addslashes($departamento->nombre_departamento) }}')">
            <i class="bi bi-pencil me-1"></i> Editar
        </button>
        @endcan
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3 col-xl">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="fw-bold" style="font-size:1.6rem;color:#0d6efd;">{{ $usuarios->count() }}</div>
                <div class="text-muted small">Miembros</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="fw-bold" style="font-size:1.6rem;color:#6c757d;">{{ $stats['total'] }}</div>
                <div class="text-muted small">Proyectos totales</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="fw-bold" style="font-size:1.6rem;color:#0dcaf0;">{{ $stats['pendiente'] }}</div>
                <div class="text-muted small">Pendientes</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="fw-bold" style="font-size:1.6rem;color:#0d6efd;">{{ $stats['proceso'] }}</div>
                <div class="text-muted small">En proceso</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="fw-bold" style="font-size:1.6rem;color:#198754;">{{ $stats['terminado'] }}</div>
                <div class="text-muted small">Terminados</div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-xl">
            <div class="bg-white rounded shadow-sm p-3 text-center">
                <div class="fw-bold" style="font-size:1.6rem;color:#dc3545;">{{ $stats['cancelado'] }}</div>
                <div class="text-muted small">Cancelados</div>
            </div>
        </div>
    </div>

    {{-- Tabla de miembros --}}
    <div class="bg-white rounded shadow-sm">
        <div class="px-4 py-3 border-bottom d-flex align-items-center gap-2">
            <i class="bi bi-people-fill text-primary"></i>
            <span class="fw-semibold">Miembros del departamento</span>
            <span class="badge bg-secondary ms-1">{{ $usuarios->count() }}</span>
        </div>

        @if($usuarios->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-person-x d-block mb-2" style="font-size:2rem;"></i>
            No hay usuarios asignados a este departamento.
        </div>
        @else
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Miembro</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th class="text-center">Proyectos</th>
                    <th class="text-center" style="min-width:220px;">Distribución</th>
                    <th class="text-center" style="width:70px;">Ver</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usuarios as $usuario)
                @php
                    $total     = $usuario->proyectos_total;
                    $pct_proc  = $total > 0 ? round($usuario->proyectos_proceso   / $total * 100) : 0;
                    $pct_pend  = $total > 0 ? round($usuario->proyectos_pendiente / $total * 100) : 0;
                    $pct_term  = $total > 0 ? round($usuario->proyectos_terminado / $total * 100) : 0;
                    $rolNombre = $usuario->roles->first()?->name ?? '—';
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:36px;height:36px;">
                                <span class="fw-bold text-primary" style="font-size:.8rem;">
                                    {{ strtoupper(substr($usuario->nombre, 0, 1) . substr($usuario->apellido, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-semibold" style="font-size:.875rem;">{{ $usuario->nombre_completo }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $usuario->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border" style="font-size:.72rem;">
                            {{ $rolNombre }}
                        </span>
                    </td>
                    <td>
                        @if($usuario->estado === 'Activo')
                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:.72rem;">
                            <i class="bi bi-circle-fill me-1" style="font-size:.45rem;"></i>Activo
                        </span>
                        @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:.72rem;">
                            <i class="bi bi-circle-fill me-1" style="font-size:.45rem;"></i>Inactivo
                        </span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($total > 0)
                        <a href="{{ route('proyectos.index', ['responsable_id' => $usuario->id]) }}"
                           class="fw-bold text-decoration-none" style="font-size:.95rem;">
                            {{ $total }}
                        </a>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td>
                        @if($total > 0)
                        <div class="d-flex flex-column gap-1">
                            {{-- Barra de distribución --}}
                            <div class="progress" style="height:8px;border-radius:4px;">
                                <div class="progress-bar bg-primary"   style="width:{{ $pct_proc }}%;" title="En proceso: {{ $usuario->proyectos_proceso }}"></div>
                                <div class="progress-bar bg-info"      style="width:{{ $pct_pend }}%;" title="Pendiente: {{ $usuario->proyectos_pendiente }}"></div>
                                <div class="progress-bar bg-success"   style="width:{{ $pct_term }}%;" title="Terminado: {{ $usuario->proyectos_terminado }}"></div>
                            </div>
                            {{-- Leyenda --}}
                            <div class="d-flex gap-2" style="font-size:.67rem;color:#888;">
                                @if($usuario->proyectos_proceso > 0)
                                <span><span class="d-inline-block rounded-circle bg-primary me-1" style="width:6px;height:6px;"></span>{{ $usuario->proyectos_proceso }} proc.</span>
                                @endif
                                @if($usuario->proyectos_pendiente > 0)
                                <span><span class="d-inline-block rounded-circle bg-info me-1" style="width:6px;height:6px;"></span>{{ $usuario->proyectos_pendiente }} pend.</span>
                                @endif
                                @if($usuario->proyectos_terminado > 0)
                                <span><span class="d-inline-block rounded-circle bg-success me-1" style="width:6px;height:6px;"></span>{{ $usuario->proyectos_terminado }} term.</span>
                                @endif
                            </div>
                        </div>
                        @else
                        <span class="text-muted small fst-italic">Sin proyectos</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @can('ver-usuario')
                        <a href="{{ route('usuarios.show', $usuario->id) }}"
                           class="btn btn-sm btn-outline-info border-0" title="Ver perfil">
                            <i class="bi bi-person"></i>
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

{{-- Formulario oculto para editar --}}
<form id="form-editar" method="POST" class="d-none">
    @csrf @method('PUT')
    <input type="text" name="nombre_departamento" id="input-editar">
</form>

@push('scripts')
<script>
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
</script>
@endpush

@endsection
