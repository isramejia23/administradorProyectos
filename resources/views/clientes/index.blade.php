@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">
            @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Logo">
            @else
                <i class="bi bi-people-fill title-icon"></i>
            @endif
            Clientes
        </h4>
        @can('crear-cliente')
        <button class="btn btn-primary btn-sm px-3" onclick="modalCrear()">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Cliente
        </button>
        @endcan
    </div>

    {{-- Filtros --}}
    <form action="{{ route('clientes.index') }}" method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label mb-1 small">Búsqueda libre</label>
                <input type="text" name="buscar" class="form-control form-control-sm"
                       placeholder="Nombre, apellido, identificación, razón social, email..."
                       value="{{ request('buscar') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="Activo"   {{ request('estado') === 'Activo'   ? 'selected' : '' }}>Activo</option>
                    <option value="Inactivo" {{ request('estado') === 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small">Ordenar por fecha</label>
                <select name="orden" class="form-select form-select-sm">
                    <option value="desc" {{ request('orden', 'desc') === 'desc' ? 'selected' : '' }}>Más recientes primero</option>
                    <option value="asc"  {{ request('orden') === 'asc'  ? 'selected' : '' }}>Más antiguos primero</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>

    <div class="bg-white rounded shadow-sm">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Identificación</th>
                    <th>Nombre / Razón Social</th>
                    <th>Email</th>
                    <th>Celular</th>
                    <th>Estado</th>
                    <th>Registrado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->identificacion_clientes }}</td>
                    <td>{{ $cliente->nombre_completo }}</td>
                    <td>{{ $cliente->email_cliente ?? '—' }}</td>
                    <td>{{ $cliente->celular_clientes ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $cliente->estado === 'Activo' ? 'success' : 'secondary' }}">
                            {{ $cliente->estado }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $cliente->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <a href="{{ route('clientes.show', $cliente->id) }}"
                               class="btn btn-sm btn-outline-info" title="Ver ficha">
                                <i class="bi bi-eye" style="font-size:1rem;"></i>
                            </a>
                            @can('crear-trabajo')
                            <a href="{{ route('proyectos.create') }}?cliente_id={{ $cliente->id }}"
                               class="btn btn-sm btn-outline-primary " title="Nuevo Proyecto">
                                <i class="bi bi-folder-plus" style="font-size:1rem;"></i>
                            </a>
                            @endcan
                            @can('editar-cliente')
                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary "
                                    title="Editar"
                                    onclick="modalEditar({{ $cliente->id }}, {
                                        nombres:  '{{ addslashes($cliente->nombres_clientes) }}',
                                        apellidos:'{{ addslashes($cliente->apellidos_clientes) }}',
                                        razon:    '{{ addslashes($cliente->razon_social ?? '') }}',
                                        ident:    '{{ addslashes($cliente->identificacion_clientes) }}',
                                        email:    '{{ addslashes($cliente->email_cliente ?? '') }}',
                                        celular:  '{{ addslashes($cliente->celular_clientes ?? '') }}',
                                        estado:   '{{ $cliente->estado }}',
                                        claves:   '{{ str_replace(["\r\n","\n","\r"], "\\n", addslashes($cliente->claves_observaciones ?? '')) }}'
                                    })">
                                <i class="bi bi-pencil" style="font-size:1rem;"></i>
                            </button>
                            @endcan
                            @can('borrar-cliente')
                            <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger " title="Eliminar"
                                        onclick="confirmDelete(event, this)">
                                    <i class="bi bi-trash" style="font-size:1rem;"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No hay clientes registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $clientes->links() }}</div>
</div>

{{-- Formularios ocultos --}}
@can('crear-cliente')
<form id="form-crear" action="{{ route('clientes.store') }}" method="POST" class="d-none">
    @csrf
    <input name="nombres_clientes"        id="c-nombres">
    <input name="apellidos_clientes"      id="c-apellidos">
    <input name="razon_social"            id="c-razon">
    <input name="identificacion_clientes" id="c-ident">
    <input name="email_cliente"           id="c-email">
    <input name="celular_clientes"        id="c-celular">
    <input name="estado"                  id="c-estado">
    <textarea name="claves_observaciones" id="c-claves"></textarea>
</form>
@endcan

@can('editar-cliente')
<form id="form-editar" method="POST" class="d-none">
    @csrf @method('PUT')
    <input name="nombres_clientes"        id="e-nombres">
    <input name="apellidos_clientes"      id="e-apellidos">
    <input name="razon_social"            id="e-razon">
    <input name="identificacion_clientes" id="e-ident">
    <input name="email_cliente"           id="e-email">
    <input name="celular_clientes"        id="e-celular">
    <input name="estado"                  id="e-estado">
    <textarea name="claves_observaciones" id="e-claves"></textarea>
</form>
@endcan

@push('scripts')
<script>
const CONSULTAR_URL = '{{ route("clientes.consultar", ":id") }}';

function formHtml(d = {}) {
    return `
    <div class="text-start">
        <div class="row g-2">

            {{-- Identificación + botón consultar --}}
            <div class="col-12">
                <label class="form-label small mb-1">Identificación <span class="text-danger">*</span></label>
                <div class="input-group input-group-sm">
                    <input id="swal-ident" class="form-control" value="${d.ident || ''}"
                           placeholder="Cédula (10 dígitos) o RUC (13 dígitos)">
                    <button type="button" class="btn btn-primary" onclick="consultarIdentificacion()"
                            id="btn-consultar" title="Consultar cédula / RUC">
                        <i class="bi bi-search"></i> Consultar
                    </button>
                </div>
                <div id="consulta-feedback" class="small mt-1"></div>
            </div>

            <div class="col-6">
                <label class="form-label small mb-1">Nombres</label>
                <input id="swal-nombres" class="form-control form-control-sm" value="${d.nombres || ''}">
            </div>
            <div class="col-6">
                <label class="form-label small mb-1">Apellidos</label>
                <input id="swal-apellidos" class="form-control form-control-sm" value="${d.apellidos || ''}">
            </div>
            <div class="col-8">
                <label class="form-label small mb-1">Razón Social</label>
                <input id="swal-razon" class="form-control form-control-sm" value="${d.razon || ''}">
            </div>
            <div class="col-4">
                <label class="form-label small mb-1">Estado</label>
                <select id="swal-estado" class="form-select form-select-sm">
                    <option value="Activo"   ${(d.estado || 'Activo') === 'Activo'   ? 'selected' : ''}>Activo</option>
                    <option value="Inactivo" ${d.estado === 'Inactivo' ? 'selected' : ''}>Inactivo</option>
                </select>
            </div>
            <div class="col-6">
                <label class="form-label small mb-1">Email</label>
                <input id="swal-email" type="email" class="form-control form-control-sm" value="${d.email || ''}">
            </div>
            <div class="col-6">
                <label class="form-label small mb-1">Celular</label>
                <input id="swal-celular" class="form-control form-control-sm" value="${d.celular || ''}"
                       inputmode="numeric" pattern="[0-9]*"
                       oninput="this.value=this.value.replace(/[^0-9]/g,'')">
            </div>
            <div class="col-12">
                <label class="form-label small mb-1">Claves / Observaciones</label>
                <textarea id="swal-claves" class="form-control form-control-sm" rows="3">${(d.claves || '').replace(/\\n/g, '\n')}</textarea>
            </div>
        </div>
    </div>`;
}

async function consultarIdentificacion() {
    const ident = document.getElementById('swal-ident')?.value.trim().replace(/\D/g, '');
    const feedback = document.getElementById('consulta-feedback');
    const btn = document.getElementById('btn-consultar');

    if (!ident || ![10, 13].includes(ident.length)) {
        feedback.innerHTML = '<span class="text-danger">Ingrese una cédula (10 dígitos) o RUC (13 dígitos).</span>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    feedback.innerHTML = '';

    try {
        const url = CONSULTAR_URL.replace(':id', ident);
        const resp = await fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        const data = await resp.json();

        if (!resp.ok) {
            feedback.innerHTML = `<span class="text-danger">${data.error || 'Error en la consulta.'}</span>`;
            return;
        }

        // Rellenar campos
        document.getElementById('swal-nombres').value   = data.nombres_clientes   || '';
        document.getElementById('swal-apellidos').value = data.apellidos_clientes || '';
        document.getElementById('swal-razon').value     = data.razon_social       || '';

        feedback.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Datos cargados correctamente.</span>';

    } catch (e) {
        feedback.innerHTML = '<span class="text-danger">No se pudo conectar con el servicio.</span>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-search"></i> Consultar';
    }
}

function getValues() {
    return {
        nombres:   document.getElementById('swal-nombres')?.value.trim(),
        apellidos: document.getElementById('swal-apellidos')?.value.trim(),
        ident:     document.getElementById('swal-ident')?.value.trim(),
        celular:   document.getElementById('swal-celular')?.value.trim(),
        razon:     document.getElementById('swal-razon')?.value.trim(),
        email:     document.getElementById('swal-email')?.value.trim(),
        estado:    document.getElementById('swal-estado')?.value,
        claves:    document.getElementById('swal-claves')?.value,
    };
}

function validate(v) {
    if (!v.ident) return 'La identificación es obligatoria.';
    if (!v.nombres && !v.razon) return 'Ingrese el nombre o la razón social.';
    return null;
}

function submitCrear(v) {
    document.getElementById('c-nombres').value   = v.nombres;
    document.getElementById('c-apellidos').value = v.apellidos;
    document.getElementById('c-razon').value     = v.razon;
    document.getElementById('c-ident').value     = v.ident;
    document.getElementById('c-email').value     = v.email;
    document.getElementById('c-celular').value   = v.celular;
    document.getElementById('c-estado').value    = v.estado;
    document.getElementById('c-claves').value    = v.claves;
    document.getElementById('form-crear').submit();
}

function submitEditar(id, v) {
    const form = document.getElementById('form-editar');
    form.action = `/clientes/${id}`;
    document.getElementById('e-nombres').value   = v.nombres;
    document.getElementById('e-apellidos').value = v.apellidos;
    document.getElementById('e-razon').value     = v.razon;
    document.getElementById('e-ident').value     = v.ident;
    document.getElementById('e-email').value     = v.email;
    document.getElementById('e-celular').value   = v.celular;
    document.getElementById('e-estado').value    = v.estado;
    document.getElementById('e-claves').value    = v.claves;
    form.submit();
}

const swalBase = {
    showCancelButton: true,
    confirmButtonColor: '#0d6efd',
    cancelButtonColor: 'transparent',
    cancelButtonText: 'Cancelar',
    reverseButtons: true,
    width: 580,
    preConfirm: () => {
        const v = getValues();
        const err = validate(v);
        if (err) { Swal.showValidationMessage(err); return false; }
        return v;
    }
};

@can('crear-cliente')
function modalCrear() {
    Swal.fire({ ...swalBase, title: 'Nuevo Cliente', html: formHtml(), confirmButtonText: 'Guardar' })
        .then(r => { if (r.isConfirmed) submitCrear(r.value); });
}
@endcan

@can('editar-cliente')
function modalEditar(id, d) {
    Swal.fire({ ...swalBase, title: 'Editar Cliente', html: formHtml(d), confirmButtonText: 'Actualizar' })
        .then(r => { if (r.isConfirmed) submitEditar(id, r.value); });
}
@endcan

@if($errors->any())
document.addEventListener('DOMContentLoaded', () => {
    Swal.fire({ icon: 'error', title: 'Error', text: '{{ $errors->first() }}' });
});
@endif


</script>
@endpush
@endsection
