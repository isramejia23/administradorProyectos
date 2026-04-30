@extends('layouts.app')


@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="page-title">
            <i class="bi bi-pencil-square title-icon"></i>
            Editar Propiedad
        </h4>
        <a href="{{ route('propiedades.show', $propiedad) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger py-2 mb-3">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="row g-3">

        {{-- ── Columna izquierda ── --}}
        <div class="col-lg-8">

            {{-- ══ FOTOS ACTUALES (FUERA del form principal) ══ --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body p-2">
                    @if($propiedad->fotos->isNotEmpty())
                        @php $portada = $propiedad->fotos->firstWhere('es_portada', true) ?? $propiedad->fotos->first(); @endphp
                        <img id="foto-preview-main"
                             src="{{ Storage::url($portada->ruta) }}"
                             style="width:100%;height:440px;object-fit:cover;border-radius:8px;">
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            @foreach($propiedad->fotos as $foto)
                            <div style="position:relative;">
                                <img src="{{ Storage::url($foto->ruta) }}"
                                     onclick="document.getElementById('foto-preview-main').src='{{ Storage::url($foto->ruta) }}'"
                                     style="width:72px;height:56px;object-fit:cover;border-radius:5px;cursor:pointer;border:2px solid {{ $foto->es_portada ? '#0d6efd' : '#dee2e6' }};">
                                @if(!$foto->es_portada)
                                {{-- Formulario portada — independiente --}}
                                <form action="{{ route('propiedades.fotos.portada', [$propiedad, $foto]) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Usar como portada"
                                            style="position:absolute;bottom:2px;left:2px;background:rgba(0,0,0,.55);color:#fff;border:none;font-size:.55rem;padding:1px 4px;border-radius:3px;cursor:pointer;line-height:1.4;">
                                        portada
                                    </button>
                                </form>
                                @endif
                                {{-- Formulario eliminar foto — independiente --}}
                                <form action="{{ route('propiedades.fotos.destroy', [$propiedad, $foto]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete(event, this)"
                                            style="position:absolute;top:2px;right:2px;background:rgba(0,0,0,.6);color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:.7rem;line-height:1;cursor:pointer;">
                                        &times;
                                    </button>
                                </form>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height:440px;">
                            <span class="text-muted text-center"><i class="bi bi-image fs-1 d-block mb-1"></i>Sin fotos</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ══ FORM PRINCIPAL (solo PUT — sin forms anidados) ══ --}}
            <form action="{{ route('propiedades.update', $propiedad) }}" method="POST" enctype="multipart/form-data" id="form-editar">
                @csrf @method('PUT')

                {{-- Agregar fotos nuevas --}}
                @if($propiedad->fotos->count() < 5)
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-images me-1"></i>Agregar Fotos ({{ 5 - $propiedad->fotos->count() }} slots disponibles)</div>
                        <p class="small text-muted mb-2 mt-1">Haz clic en una miniatura para marcarla como portada.</p>
                        <input type="file" name="fotos[]" id="fotos-input" accept="image/*" multiple class="d-none">
                        <input type="hidden" name="portada_index" id="portada_index" value="0">
                        <div id="fotos-preview" class="d-flex flex-wrap gap-2 mb-2"></div>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-agregar-foto">
                            <i class="bi bi-image-fill me-1"></i> Seleccionar fotos
                        </button>
                        <small class="text-muted ms-2">JPG, PNG o WebP · máx. 5 MB</small>
                    </div>
                </div>
                @endif

                {{-- Descripción --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-text-paragraph me-1"></i>Descripción</div>
                        <textarea name="descripcion" class="form-control form-control-sm mt-2" rows="4">{{ old('descripcion', $propiedad->descripcion) }}</textarea>
                    </div>
                </div>

                {{-- Ubicación --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-geo-alt me-1"></i>Ubicación</div>
                        <div class="row g-3 mt-1">
                            <div class="col-sm-4">
                                <label class="form-label small"><i class="bi bi-building me-1"></i>Ciudad <span class="text-danger">*</span></label>
                                <input type="text" name="ciudad" class="form-control form-control-sm"
                                       value="{{ old('ciudad', $propiedad->ciudad) }}" required maxlength="100">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label small"><i class="bi bi-signpost me-1"></i>Sector / Barrio</label>
                                <input type="text" name="sector" class="form-control form-control-sm"
                                       value="{{ old('sector', $propiedad->sector) }}" maxlength="150">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label small"><i class="bi bi-pin-map me-1"></i>Dirección <span class="text-danger">*</span></label>
                                <input type="text" name="direccion" class="form-control form-control-sm"
                                       value="{{ old('direccion', $propiedad->direccion) }}" required maxlength="250">
                            </div>
                        </div>
                    </div>
                </div>

            {{-- (la columna derecha cierra el form también) --}}

        </div>{{-- fin col-lg-8 --}}

        {{-- ── Columna derecha ── --}}
        <div class="col-lg-4">

                {{-- Información general --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-info-circle me-1"></i>Información General</div>
                        <div class="row g-2 mt-1">
                            <div class="col-12">
                                <label class="form-label small">Título <span class="text-danger">*</span></label>
                                <input type="text" name="titulo" class="form-control form-control-sm"
                                       value="{{ old('titulo', $propiedad->titulo) }}" required maxlength="200">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Tipo de propiedad <span class="text-danger">*</span></label>
                                <select name="tipo_propiedad" id="tipo_propiedad" class="form-select form-select-sm" required>
                                    <option value="casa"            {{ old('tipo_propiedad', $propiedad->tipo_propiedad) === 'casa'            ? 'selected' : '' }}>Casa</option>
                                    <option value="departamento"    {{ old('tipo_propiedad', $propiedad->tipo_propiedad) === 'departamento'    ? 'selected' : '' }}>Departamento</option>
                                    <option value="terreno"         {{ old('tipo_propiedad', $propiedad->tipo_propiedad) === 'terreno'         ? 'selected' : '' }}>Terreno</option>
                                    <option value="local_comercial" {{ old('tipo_propiedad', $propiedad->tipo_propiedad) === 'local_comercial' ? 'selected' : '' }}>Local Comercial</option>
                                    <option value="oficina"         {{ old('tipo_propiedad', $propiedad->tipo_propiedad) === 'oficina'         ? 'selected' : '' }}>Oficina</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Estado <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select form-select-sm" required>
                                    <option value="disponible" {{ old('estado', $propiedad->estado) === 'disponible' ? 'selected' : '' }}>Disponible</option>
                                    <option value="en_proceso" {{ old('estado', $propiedad->estado) === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="vendido"    {{ old('estado', $propiedad->estado) === 'vendido'    ? 'selected' : '' }}>Vendido</option>
                                    <option value="cancelado"  {{ old('estado', $propiedad->estado) === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Precio --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-tag me-1"></i>Precio</div>
                        <div class="row g-2 mt-1">
                            <div class="col-12">
                                <label class="form-label small">Precio estimado (USD) <span class="text-danger">*</span></label>
                                <input type="number" name="precio_estimado" class="form-control form-control-sm"
                                       value="{{ old('precio_estimado', $propiedad->precio_estimado) }}" step="0.01" min="0" required>
                            </div>
                            <div class="col-12 pt-1">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="negociable" value="1"
                                           id="negociable" {{ old('negociable', $propiedad->negociable) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="negociable">Precio negociable</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Características --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-list-check me-1"></i>Características</div>
                        <div class="row g-2 mt-1">
                            <div class="col-6">
                                <label class="form-label small"><i class="bi bi-grid-3x3-gap me-1"></i>Terreno (m²)</label>
                                <input type="number" name="metros_terreno" class="form-control form-control-sm"
                                       value="{{ old('metros_terreno', $propiedad->metros_terreno) }}" step="0.01" min="0">
                            </div>
                            <div class="col-6" id="wrap_metros_construccion">
                                <label class="form-label small"><i class="bi bi-building me-1"></i>Construcción (m²)</label>
                                <input type="number" name="metros_construccion" class="form-control form-control-sm"
                                       value="{{ old('metros_construccion', $propiedad->metros_construccion) }}" step="0.01" min="0">
                            </div>
                            <div class="col-4" id="wrap_habitaciones">
                                <label class="form-label small"><i class="bi bi-door-open me-1"></i>Hab.</label>
                                <input type="number" name="numero_habitaciones" class="form-control form-control-sm"
                                       value="{{ old('numero_habitaciones', $propiedad->numero_habitaciones) }}" min="0" max="99">
                            </div>
                            <div class="col-4" id="wrap_banos">
                                <label class="form-label small"><i class="bi bi-droplet me-1"></i>Baños</label>
                                <input type="number" name="numero_banos" class="form-control form-control-sm"
                                       value="{{ old('numero_banos', $propiedad->numero_banos) }}" min="0" max="99">
                            </div>
                            <div class="col-4" id="wrap_parqueaderos">
                                <label class="form-label small"><i class="bi bi-car-front me-1"></i>Parq.</label>
                                <input type="number" name="parqueaderos" class="form-control form-control-sm"
                                       value="{{ old('parqueaderos', $propiedad->parqueaderos) }}" min="0" max="99">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Dueño --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-person-fill me-1"></i>Datos del Dueño</div>
                        <div class="row g-2 mt-1">
                            <div class="col-12">
                                <label class="form-label small"><i class="bi bi-person me-1"></i>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre_dueno" class="form-control form-control-sm"
                                       value="{{ old('nombre_dueno', $propiedad->nombre_dueno) }}" required maxlength="200">
                            </div>
                            <div class="col-12">
                                <label class="form-label small"><i class="bi bi-telephone me-1"></i>Teléfono <span class="text-danger">*</span></label>
                                <input type="text" name="telefono_dueno" class="form-control form-control-sm"
                                       value="{{ old('telefono_dueno', $propiedad->telefono_dueno) }}" required maxlength="20">
                            </div>
                            <div class="col-6">
                                <label class="form-label small"><i class="bi bi-people me-1"></i>N° Propietarios</label>
                                <input type="number" name="numero_propietarios" class="form-control form-control-sm"
                                       value="{{ old('numero_propietarios', $propiedad->numero_propietarios ?? 1) }}" min="1" max="99">
                            </div>
                            <div class="col-6">
                                <label class="form-label small"><i class="bi bi-person-badge me-1"></i>Captador</label>
                                <select name="captador_id" id="select-captador" class="form-select form-select-sm">
                                    <option value="">— Ninguno —</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}"
                                            {{ old('captador_id', $propiedad->captador_id) == $usuario->id ? 'selected' : '' }}>
                                            {{ $usuario->nombre }} {{ $usuario->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small"><i class="bi bi-people me-1"></i>Vincular a cliente</label>
                                <select name="cliente_id" class="form-select form-select-sm">
                                    <option value="">— Sin vincular —</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}"
                                            {{ old('cliente_id', $propiedad->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('propiedades.show', $propiedad) }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
                    <button type="submit" form="form-editar" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-check-lg me-1"></i> Guardar Cambios
                    </button>
                </div>

            </form>{{-- fin form principal --}}

        </div>{{-- fin col-lg-4 --}}
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
new TomSelect('#select-captador', { allowEmptyOption: true, placeholder: 'Buscar captador...' });

const camposPorTipo = {
    terreno:         { construccion: false, habitaciones: false, banos: false, parqueaderos: false },
    casa:            { construccion: true,  habitaciones: true,  banos: true,  parqueaderos: true  },
    departamento:    { construccion: true,  habitaciones: true,  banos: true,  parqueaderos: true  },
    local_comercial: { construccion: true,  habitaciones: false, banos: false, parqueaderos: true  },
    oficina:         { construccion: true,  habitaciones: true,  banos: true,  parqueaderos: true  },
};

function actualizarCampos() {
    const tipo  = document.getElementById('tipo_propiedad').value;
    const regla = camposPorTipo[tipo] ?? { construccion: true, habitaciones: true, banos: true, parqueaderos: true };
    document.getElementById('wrap_metros_construccion').style.display = regla.construccion  ? '' : 'none';
    document.getElementById('wrap_habitaciones').style.display        = regla.habitaciones  ? '' : 'none';
    document.getElementById('wrap_banos').style.display               = regla.banos         ? '' : 'none';
    document.getElementById('wrap_parqueaderos').style.display        = regla.parqueaderos  ? '' : 'none';
}
document.getElementById('tipo_propiedad').addEventListener('change', actualizarCampos);
actualizarCampos();

// Preview fotos nuevas
let archivosSeleccionados = [];
const maxSlots   = {{ 5 - $propiedad->fotos->count() }};
const btnAgregar = document.getElementById('btn-agregar-foto');

if (btnAgregar) {
    btnAgregar.addEventListener('click', () => document.getElementById('fotos-input').click());
    document.getElementById('fotos-input').addEventListener('change', function () {
        const disponibles = maxSlots - archivosSeleccionados.length;
        archivosSeleccionados = archivosSeleccionados.concat(Array.from(this.files).slice(0, disponibles));
        this.value = '';
        renderPreview();
    });
}

function renderPreview() {
    const container = document.getElementById('fotos-preview');
    if (!container) return;
    container.innerHTML = '';
    const portadaIdx = parseInt(document.getElementById('portada_index').value) || 0;

    archivosSeleccionados.forEach((file, i) => {
        const url  = URL.createObjectURL(file);
        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:relative;cursor:pointer;';

        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = `width:72px;height:56px;object-fit:cover;border-radius:5px;border:2px solid ${i === portadaIdx ? '#0d6efd' : '#dee2e6'};`;

        const badge = document.createElement('span');
        badge.textContent = 'portada';
        badge.style.cssText = `position:absolute;bottom:2px;left:2px;background:#0d6efd;color:#fff;font-size:.55rem;padding:1px 4px;border-radius:3px;line-height:1.4;display:${i === portadaIdx ? 'block' : 'none'};`;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '&times;';
        btn.style.cssText = 'position:absolute;top:2px;right:2px;background:rgba(0,0,0,.6);color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:.7rem;line-height:1;cursor:pointer;';
        btn.onclick = (e) => { e.stopPropagation(); archivosSeleccionados.splice(i, 1); renderPreview(); };

        wrap.appendChild(img);
        wrap.appendChild(badge);
        wrap.appendChild(btn);
        wrap.onclick = () => { document.getElementById('portada_index').value = i; renderPreview(); };
        container.appendChild(wrap);
    });

    if (btnAgregar) btnAgregar.style.display = archivosSeleccionados.length >= maxSlots ? 'none' : '';

    const dt = new DataTransfer();
    archivosSeleccionados.forEach(f => dt.items.add(f));
    const input = document.getElementById('fotos-input');
    if (input) input.files = dt.files;
}
</script>
@endpush
