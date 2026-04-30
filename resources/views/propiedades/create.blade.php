@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="page-title">
            <i class="bi bi-house-add title-icon"></i>
            Nueva Propiedad
        </h4>
        <a href="{{ route('propiedades.index') }}" class="btn btn-outline-secondary btn-sm">
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

    <form action="{{ route('propiedades.store') }}" method="POST" enctype="multipart/form-data" id="form-crear">
        @csrf

        <div class="row g-3">

            {{-- ── Columna izquierda ── --}}
            <div class="col-lg-8">

                {{-- Fotos --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-images me-1"></i>Fotos (máx. 5)</div>
                        <p class="small text-muted mb-2 mt-1">Haz clic en una miniatura para marcarla como portada.</p>

                        <input type="file" name="fotos[]" id="fotos-input" accept="image/*" multiple class="d-none">
                        <input type="hidden" name="portada_index" id="portada_index" value="0">

                        <div id="fotos-preview" class="d-flex flex-wrap gap-2 mb-2">
                            <div id="placeholder-foto" style="width:100%;height:220px;background:#f8f9fa;border:2px dashed #dee2e6;border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#aaa;cursor:pointer;"
                                 onclick="document.getElementById('fotos-input').click()">
                                <i class="bi bi-cloud-upload" style="font-size:2.5rem;"></i>
                                <span class="mt-2 small">Haz clic para subir fotos</span>
                                <span class="small text-muted">JPG, PNG o WebP · máx. 5 MB por foto</span>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-secondary btn-sm d-none" id="btn-agregar-foto">
                            <i class="bi bi-image-fill me-1"></i> Agregar más fotos
                        </button>
                    </div>
                </div>

                {{-- Descripción --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="card-section-title"><i class="bi bi-text-paragraph me-1"></i>Descripción</div>
                        <textarea name="descripcion" class="form-control form-control-sm mt-2" rows="4"
                                  placeholder="Describe la propiedad: características destacadas, estado, entorno...">{{ old('descripcion') }}</textarea>
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
                                       value="{{ old('ciudad') }}" required maxlength="100" placeholder="Ej: Ambato">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label small"><i class="bi bi-signpost me-1"></i>Sector / Barrio</label>
                                <input type="text" name="sector" class="form-control form-control-sm"
                                       value="{{ old('sector') }}" maxlength="150" placeholder="Ej: La Merced">
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label small"><i class="bi bi-pin-map me-1"></i>Dirección <span class="text-danger">*</span></label>
                                <input type="text" name="direccion" class="form-control form-control-sm"
                                       value="{{ old('direccion') }}" required maxlength="250" placeholder="Calle y número">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

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
                                       value="{{ old('titulo') }}" required maxlength="200"
                                       placeholder="Ej: Casa de 3 habitaciones en La Merced">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Tipo de propiedad <span class="text-danger">*</span></label>
                                <select name="tipo_propiedad" id="tipo_propiedad" class="form-select form-select-sm" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="casa"            {{ old('tipo_propiedad') === 'casa'            ? 'selected' : '' }}>Casa</option>
                                    <option value="departamento"    {{ old('tipo_propiedad') === 'departamento'    ? 'selected' : '' }}>Departamento</option>
                                    <option value="terreno"         {{ old('tipo_propiedad') === 'terreno'         ? 'selected' : '' }}>Terreno</option>
                                    <option value="local_comercial" {{ old('tipo_propiedad') === 'local_comercial' ? 'selected' : '' }}>Local Comercial</option>
                                    <option value="oficina"         {{ old('tipo_propiedad') === 'oficina'         ? 'selected' : '' }}>Oficina</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Estado <span class="text-danger">*</span></label>
                                <select name="estado" class="form-select form-select-sm" required>
                                    <option value="disponible" {{ old('estado', 'disponible') === 'disponible' ? 'selected' : '' }}>Disponible</option>
                                    <option value="en_proceso" {{ old('estado') === 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="vendido"    {{ old('estado') === 'vendido'    ? 'selected' : '' }}>Vendido</option>
                                    <option value="cancelado"  {{ old('estado') === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
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
                                       value="{{ old('precio_estimado') }}" step="0.01" min="0" required placeholder="0.00">
                            </div>
                            <div class="col-12 pt-1">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="negociable" value="1"
                                           id="negociable" {{ old('negociable') ? 'checked' : '' }}>
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
                                       value="{{ old('metros_terreno') }}" step="0.01" min="0">
                            </div>
                            <div class="col-6" id="wrap_metros_construccion">
                                <label class="form-label small"><i class="bi bi-building me-1"></i>Construcción (m²)</label>
                                <input type="number" name="metros_construccion" class="form-control form-control-sm"
                                       value="{{ old('metros_construccion') }}" step="0.01" min="0">
                            </div>
                            <div class="col-4" id="wrap_habitaciones">
                                <label class="form-label small"><i class="bi bi-door-open me-1"></i>Hab.</label>
                                <input type="number" name="numero_habitaciones" class="form-control form-control-sm"
                                       value="{{ old('numero_habitaciones') }}" min="0" max="99">
                            </div>
                            <div class="col-4" id="wrap_banos">
                                <label class="form-label small"><i class="bi bi-droplet me-1"></i>Baños</label>
                                <input type="number" name="numero_banos" class="form-control form-control-sm"
                                       value="{{ old('numero_banos') }}" min="0" max="99">
                            </div>
                            <div class="col-4" id="wrap_parqueaderos">
                                <label class="form-label small"><i class="bi bi-car-front me-1"></i>Parq.</label>
                                <input type="number" name="parqueaderos" class="form-control form-control-sm"
                                       value="{{ old('parqueaderos') }}" min="0" max="99">
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
                                       value="{{ old('nombre_dueno') }}" required maxlength="200">
                            </div>
                            <div class="col-6">
                                <label class="form-label small"><i class="bi bi-telephone me-1"></i>Teléfono <span class="text-danger">*</span></label>
                                <input type="text" name="telefono_dueno" class="form-control form-control-sm"
                                       value="{{ old('telefono_dueno') }}" required maxlength="20">
                            </div>
                            <div class="col-6">
                                <label class="form-label small"><i class="bi bi-people me-1"></i>N° Propietarios</label>
                                <input type="number" name="numero_propietarios" class="form-control form-control-sm"
                                       value="{{ old('numero_propietarios', 1) }}" min="1" max="99">
                            </div>
                            <div class="col-12">
                                <label class="form-label small"><i class="bi bi-person-badge me-1"></i>Captador</label>
                                <select name="captador_id" id="select-captador" class="form-select form-select-sm">
                                    <option value="">— Ninguno —</option>
                                    @foreach($usuarios as $usuario)
                                        <option value="{{ $usuario->id }}" {{ old('captador_id') == $usuario->id ? 'selected' : '' }}>
                                            {{ $usuario->nombre }} {{ $usuario->apellido }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label small"><i class="bi bi-people me-1"></i>Vincular a cliente</label>
                                <select name="cliente_id" id="select-cliente" class="form-select form-select-sm">
                                    <option value="">— Sin vincular —</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('propiedades.index') }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-check-lg me-1"></i> Guardar Propiedad
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
// Selects con búsqueda
new TomSelect('#select-captador', { allowEmptyOption: true, placeholder: 'Buscar captador...' });
new TomSelect('#select-cliente',  { allowEmptyOption: true, placeholder: 'Buscar cliente...' });

// Campos condicionales por tipo
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

// Preview fotos
let archivos = [];

document.getElementById('fotos-input').addEventListener('change', function () {
    const disponibles = 5 - archivos.length;
    archivos = archivos.concat(Array.from(this.files).slice(0, disponibles));
    this.value = '';
    renderPreview();
});

function renderPreview() {
    const container   = document.getElementById('fotos-preview');
    const placeholder = document.getElementById('placeholder-foto');
    const btnAgregar  = document.getElementById('btn-agregar-foto');

    if (archivos.length === 0) {
        placeholder.style.display = 'flex';
        btnAgregar.classList.add('d-none');
        container.innerHTML = '';
        container.appendChild(placeholder);
        return;
    }

    placeholder.style.display = 'none';
    container.innerHTML = '';

    const portadaIdx = parseInt(document.getElementById('portada_index').value) || 0;

    archivos.forEach((file, i) => {
        const url  = URL.createObjectURL(file);
        const wrap = document.createElement('div');
        wrap.style.cssText = 'position:relative;cursor:pointer;';

        const img = document.createElement('img');
        img.src = url;
        img.style.cssText = `width:100px;height:80px;object-fit:cover;border-radius:6px;border:3px solid ${i === portadaIdx ? '#0d6efd' : '#dee2e6'};transition:border-color .15s;`;

        const badge = document.createElement('span');
        badge.textContent = 'Portada';
        badge.style.cssText = `position:absolute;bottom:4px;left:4px;background:#0d6efd;color:#fff;font-size:.6rem;padding:1px 6px;border-radius:3px;display:${i === portadaIdx ? 'block' : 'none'};`;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = '&times;';
        btn.style.cssText = 'position:absolute;top:3px;right:3px;background:rgba(0,0,0,.65);color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:.8rem;line-height:1;cursor:pointer;';
        btn.onclick = (e) => {
            e.stopPropagation();
            archivos.splice(i, 1);
            if (parseInt(document.getElementById('portada_index').value) >= archivos.length) {
                document.getElementById('portada_index').value = 0;
            }
            renderPreview();
        };

        wrap.appendChild(img);
        wrap.appendChild(badge);
        wrap.appendChild(btn);
        wrap.onclick = () => { document.getElementById('portada_index').value = i; renderPreview(); };
        container.appendChild(wrap);
    });

    btnAgregar.classList.toggle('d-none', archivos.length >= 5);

    const dt = new DataTransfer();
    archivos.forEach(f => dt.items.add(f));
    document.getElementById('fotos-input').files = dt.files;
}

document.getElementById('btn-agregar-foto').addEventListener('click', () => {
    document.getElementById('fotos-input').click();
});
</script>
@endpush
