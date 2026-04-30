@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="page-title">
            <i class="bi bi-house-door-fill title-icon"></i>
            {{ $propiedad->titulo }}
        </h4>
        <div class="d-flex gap-2">
            @can('editar-propiedad')
            <a href="{{ route('propiedades.edit', $propiedad) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil me-1"></i> Editar
            </a>
            @endcan
            <a href="{{ route('propiedades.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- Badges de estado/tipo --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <span class="badge bg-dark">{{ $propiedad->tipo_label }}</span>
        <span class="badge bg-{{ $propiedad->estado_badge }}">{{ $propiedad->estado_label }}</span>
        @if($propiedad->negociable)
            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Precio Negociable</span>
        @endif
    </div>

    <div class="row g-3">

        {{-- ── Columna izquierda: Fotos + Descripción ── --}}
        <div class="col-lg-8">

            {{-- Fotos --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body p-2">
                    @if($propiedad->fotos->isNotEmpty())
                        @php $portada = $propiedad->fotos->firstWhere('es_portada', true) ?? $propiedad->fotos->first(); @endphp
                        <img id="foto-principal"
                             src="{{ Storage::url($portada->ruta) }}"
                             alt="{{ $propiedad->titulo }}"
                             style="width:100%;height:440px;object-fit:cover;border-radius:8px;">
                        @if($propiedad->fotos->count() > 1)
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            @foreach($propiedad->fotos as $foto)
                            <img src="{{ Storage::url($foto->ruta) }}"
                                 alt="Foto"
                                 onclick="document.getElementById('foto-principal').src='{{ Storage::url($foto->ruta) }}'"
                                 style="width:72px;height:56px;object-fit:cover;border-radius:5px;cursor:pointer;border:2px solid {{ $foto->es_portada ? '#0d6efd' : '#dee2e6' }};">
                            @endforeach
                        </div>
                        @endif
                    @else
                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height:440px;">
                            <span class="text-muted text-center"><i class="bi bi-image fs-1 d-block mb-1"></i>Sin fotos</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Descripción --}}
            @if($propiedad->descripcion)
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="card-section-title"><i class="bi bi-text-paragraph me-1"></i>Descripción</div>
                    <p class="mb-0 mt-2" style="white-space:pre-line;color:#444;line-height:1.75;">{{ $propiedad->descripcion }}</p>
                </div>
            </div>
            @endif

            {{-- Ubicación --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="card-section-title"><i class="bi bi-geo-alt me-1"></i>Ubicación</div>
                    <div class="row g-3 mt-1">
                        <div class="col-sm-4">
                            <small class="text-muted d-block"><i class="bi bi-building me-1"></i>Ciudad</small>
                            <strong>{{ $propiedad->ciudad }}</strong>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block"><i class="bi bi-signpost me-1"></i>Sector / Barrio</small>
                            <strong>{{ $propiedad->sector ?? '—' }}</strong>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block"><i class="bi bi-pin-map me-1"></i>Dirección</small>
                            <strong>{{ $propiedad->direccion }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Columna derecha: Precio + Características + Dueño ── --}}
        <div class="col-lg-4">

            {{-- Precio --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="card-section-title"><i class="bi bi-tag me-1"></i>Precio</div>
                    <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                        <span style="font-size:1.4rem;font-weight:700;color:#111;">{{ $propiedad->precio_formateado }}</span>
                        @if($propiedad->negociable)
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Negociable</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Características --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="card-section-title"><i class="bi bi-list-check me-1"></i>Características</div>
                    <div class="row g-2 mt-1">
                        @if($propiedad->metros_terreno)
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-light rounded px-2 py-1">
                                <i class="bi bi-grid-3x3-gap text-secondary"></i>
                                <div>
                                    <div style="font-size:.8rem;font-weight:700;line-height:1.2;">{{ number_format($propiedad->metros_terreno, 0) }} m²</div>
                                    <div style="font-size:.65rem;color:#999;">Terreno</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($propiedad->metros_construccion)
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-light rounded px-2 py-1">
                                <i class="bi bi-building text-secondary"></i>
                                <div>
                                    <div style="font-size:.8rem;font-weight:700;line-height:1.2;">{{ number_format($propiedad->metros_construccion, 0) }} m²</div>
                                    <div style="font-size:.65rem;color:#999;">Construcción</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($propiedad->numero_habitaciones !== null)
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-light rounded px-2 py-1">
                                <i class="bi bi-door-open text-secondary"></i>
                                <div>
                                    <div style="font-size:.8rem;font-weight:700;line-height:1.2;">{{ $propiedad->numero_habitaciones }}</div>
                                    <div style="font-size:.65rem;color:#999;">Habitaciones</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($propiedad->numero_banos !== null)
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-light rounded px-2 py-1">
                                <i class="bi bi-droplet text-secondary"></i>
                                <div>
                                    <div style="font-size:.8rem;font-weight:700;line-height:1.2;">{{ $propiedad->numero_banos }}</div>
                                    <div style="font-size:.65rem;color:#999;">Baños</div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($propiedad->parqueaderos !== null)
                        <div class="col-6">
                            <div class="d-flex align-items-center gap-2 bg-light rounded px-2 py-1">
                                <i class="bi bi-car-front text-secondary"></i>
                                <div>
                                    <div style="font-size:.8rem;font-weight:700;line-height:1.2;">{{ $propiedad->parqueaderos }}</div>
                                    <div style="font-size:.65rem;color:#999;">Parqueaderos</div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Dueño --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="card-section-title"><i class="bi bi-person-fill me-1"></i>Datos del Dueño</div>
                    <div class="mt-2">
                        <div class="mb-2">
                            <small class="text-muted d-block"><i class="bi bi-person me-1"></i>Nombre</small>
                            <strong>{{ $propiedad->nombre_dueno }}</strong>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block"><i class="bi bi-telephone me-1"></i>Teléfono</small>
                            <strong>{{ $propiedad->telefono_dueno }}</strong>
                        </div>
                        <div>
                            <small class="text-muted d-block"><i class="bi bi-people me-1"></i>N° Propietarios</small>
                            <strong>{{ $propiedad->numero_propietarios ?? 1 }}</strong>
                        </div>
                        @if($propiedad->captador)
                        <div>
                            <small class="text-muted d-block"><i class="bi bi-person-badge me-1"></i>Captador</small>
                            <strong>{{ $propiedad->captador->nombre }} {{ $propiedad->captador->apellido }}</strong>
                        </div>
                        @endif
                        @if($propiedad->cliente)
                        <div>
                            <small class="text-muted d-block"><i class="bi bi-people me-1"></i>Cliente vinculado</small>
                            <strong>{{ $propiedad->cliente->nombre_completo }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Meta --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="card-section-title"><i class="bi bi-clock-history me-1"></i>Registro</div>
                    <div class="mt-2">
                        <small class="text-muted d-block">Creado</small>
                        {{ $propiedad->created_at->format('d/m/Y H:i') }}
                        <small class="text-muted d-block mt-1">Última actualización</small>
                        {{ $propiedad->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
