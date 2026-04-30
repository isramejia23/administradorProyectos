@extends('layouts.catalogo')

@section('title', 'Catálogo de Propiedades')

@push('styles')
<style>
    /* ─── HERO ─── */
    .pg-hero {
        background: linear-gradient(135deg, #111 0%, #1e1e1e 55%, #2a0a10 100%);
        padding: 5rem 0 2.5rem;
        position: relative; overflow: hidden;
    }
    .pg-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(ellipse 80% 60% at 70% 50%, rgba(200,16,46,.08) 0%, transparent 70%);
        pointer-events: none;
    }
    .hero-eyebrow {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(200,16,46,.18); color: #ff8a96;
        font-size: .7rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase;
        padding: 5px 14px; border-radius: 20px;
        border: 1px solid rgba(200,16,46,.3);
        margin-bottom: 1.25rem;
    }
    .pg-hero h1 {
        color: #fff; font-size: clamp(1.9rem, 4vw, 2.8rem);
        font-weight: 800; line-height: 1.15; margin-bottom: .75rem;
    }
    .pg-hero h1 .red { color: var(--pg-red); }
    .pg-hero .hero-sub { color: #aaa; font-size: .95rem; line-height: 1.6; margin-bottom: 0; }
    .hero-badges { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.5rem; }
    .hero-badge {
        background: rgba(255,255,255,.07); color: #ccc;
        font-size: .75rem; padding: 5px 12px; border-radius: 20px;
        border: 1px solid rgba(255,255,255,.1);
        display: flex; align-items: center; gap: 6px;
    }
    .hero-badge i { color: var(--pg-red); }

    /* ─── FILTER CARD ─── */
    .filter-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 16px 60px rgba(0,0,0,.28);
        padding: 1.6rem;
        border-top: 3px solid var(--pg-red);
    }
    .filter-card .fc-title {
        font-size: .78rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .08em; color: var(--pg-gray); margin-bottom: 1rem;
    }
    .filter-card .form-label { font-size: .75rem; font-weight: 600; color: #555; margin-bottom: 3px; }
    .filter-card .form-control,
    .filter-card .form-select {
        height: 40px; border-radius: 7px; border-color: #e5e5e5; font-size: .85rem;
    }
    .filter-card .form-control:focus,
    .filter-card .form-select:focus {
        border-color: var(--pg-red);
        box-shadow: 0 0 0 3px rgba(200,16,46,.1);
    }
    .filter-card .input-group-text {
        background: #fafafa; border-color: #e5e5e5;
        font-size: .8rem; color: #888;
    }
    .btn-search {
        height: 40px; width: 100%;
        background: var(--pg-red); border: none; color: #fff;
        font-weight: 700; font-size: .88rem; border-radius: 7px;
        transition: background .15s, transform .1s;
    }
    .btn-search:hover { background: var(--pg-red2); transform: translateY(-1px); }

    /* ─── RESULTS BAR ─── */
    .results-bar {
        background: #fff;
        border-bottom: 1px solid #eaeaea;
        padding: .6rem 0;
        font-size: .8rem;
    }
    .results-bar .results-count strong { color: var(--pg-dark); }
    .results-bar .results-count { color: #777; }
    .filter-chip {
        display: inline-flex; align-items: center; gap: 5px;
        background: rgba(200,16,46,.08); color: var(--pg-red);
        border: 1px solid rgba(200,16,46,.2);
        font-size: .72rem; font-weight: 600;
        padding: 3px 10px; border-radius: 20px;
    }
    .filter-chip a { color: var(--pg-red); text-decoration: none; font-size: .85rem; line-height: 1; }

    /* ─── PROPERTY CARD ─── */
    .prop-card {
        background: #fff; border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 14px rgba(0,0,0,.07);
        border: 1.5px solid #ececec;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        text-decoration: none; color: inherit; display: block; height: 100%;
    }
    .prop-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 36px rgba(0,0,0,.13);
        border-color: var(--pg-red);
        color: inherit;
    }
    .prop-img-wrap {
        position: relative; height: 200px; overflow: hidden;
    }
    .prop-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .45s ease;
    }
    .prop-card:hover .prop-img-wrap img { transform: scale(1.05); }
    .prop-img-wrap .no-img {
        width: 100%; height: 100%;
        background: linear-gradient(135deg, #e8e8e8, #d8d8d8);
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; color: #bbb;
    }
    .prop-badge-tipo {
        position: absolute; top: 10px; left: 10px;
        background: rgba(26,26,26,.85); color: #fff;
        font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        padding: 3px 9px; border-radius: 20px;
        backdrop-filter: blur(4px);
    }
    .prop-badge-neg {
        position: absolute; top: 10px; right: 10px;
        background: #198754; color: #fff;
        font-size: .6rem; font-weight: 700;
        padding: 3px 8px; border-radius: 20px;
    }
    .prop-body { padding: 1rem 1.1rem .9rem; }
    .prop-price { font-size: 1.2rem; font-weight: 800; color: var(--pg-dark); margin-bottom: 2px; }
    .prop-title {
        font-size: .9rem; font-weight: 600; color: var(--pg-dark);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px;
    }
    .prop-location { font-size: .76rem; color: #999; margin-bottom: .65rem; }
    .prop-location i { color: var(--pg-red); }
    .prop-specs {
        display: flex; gap: .6rem; flex-wrap: wrap;
        border-top: 1px solid #f2f2f2; padding-top: .6rem;
    }
    .prop-spec { font-size: .72rem; color: #777; display: flex; align-items: center; gap: 4px; }
    .prop-spec i { color: var(--pg-gray); font-size: .75rem; }

    /* ─── EMPTY STATE ─── */
    .empty-state {
        background: #fff; border-radius: 14px;
        padding: 4.5rem 2rem; text-align: center;
        border: 1.5px dashed #e0e0e0;
    }
    .empty-state i { font-size: 3.5rem; color: #ddd; display: block; margin-bottom: 1rem; }
</style>
@endpush

@section('content')

{{-- ─── HERO ────────────────────────────────────────────────────── --}}
<section class="pg-hero">
    <div class="container">
        <div class="row align-items-center g-5">

            {{-- Texto --}}
            <div class="col-lg-5">
                <div class="hero-eyebrow">
                    <i class="bi bi-star-fill"></i> Catálogo oficial
                </div>
                <h1>Encuentra tu <span class="red">propiedad<br>ideal</span> en Ecuador</h1>
                <p class="hero-sub">
                    Casas, departamentos, terrenos y locales comerciales.<br>
                    Asesoría personalizada desde el primer contacto.
                </p>
                <div class="hero-badges">
                    <span class="hero-badge"><i class="bi bi-shield-check"></i> Propiedades verificadas</span>
                    <span class="hero-badge"><i class="bi bi-headset"></i> Asesoría gratuita</span>
                    <span class="hero-badge"><i class="bi bi-tag"></i> Precio negociable</span>
                </div>
            </div>

            {{-- Filtro --}}
            <div class="col-lg-7">
                <div class="filter-card">
                    <p class="fc-title"><i class="bi bi-search me-1"></i>Buscar propiedades</p>
                    <form action="{{ route('catalogo.index') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label">Tipo de propiedad</label>
                                <select name="tipo" class="form-select">
                                    <option value="">Todos los tipos</option>
                                    <option value="casa"            {{ request('tipo')==='casa'            ? 'selected':'' }}>🏠 Casa</option>
                                    <option value="departamento"    {{ request('tipo')==='departamento'    ? 'selected':'' }}>🏢 Departamento</option>
                                    <option value="terreno"         {{ request('tipo')==='terreno'         ? 'selected':'' }}>🌿 Terreno</option>
                                    <option value="local_comercial" {{ request('tipo')==='local_comercial' ? 'selected':'' }}>🏪 Local Comercial</option>
                                    <option value="oficina"         {{ request('tipo')==='oficina'         ? 'selected':'' }}>💼 Oficina</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Ciudad</label>
                                <select name="ciudad" class="form-select">
                                    <option value="">Todas las ciudades</option>
                                    @foreach($ciudades as $ciudad)
                                        <option value="{{ $ciudad }}" {{ request('ciudad')===$ciudad ? 'selected':'' }}>{{ $ciudad }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Precio mínimo (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="precio_min" class="form-control"
                                           value="{{ request('precio_min') }}" min="0" placeholder="0">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label">Precio máximo (USD)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="precio_max" class="form-control"
                                           value="{{ request('precio_max') }}" min="0" placeholder="Sin límite">
                                </div>
                            </div>
                            <div class="col-8">
                                <button type="submit" class="btn-search">
                                    <i class="bi bi-search me-2"></i>Buscar propiedades
                                </button>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('catalogo.index') }}"
                                   class="btn btn-outline-secondary w-100"
                                   style="height:40px;font-size:.83rem;">
                                    <i class="bi bi-x-lg me-1"></i>Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ─── BARRA DE RESULTADOS ─────────────────────────────────────── --}}
<div class="results-bar">
    <div class="container d-flex align-items-center gap-3 flex-wrap">
        <span class="results-count">
            <strong>{{ $propiedades->total() }}</strong>
            {{ $propiedades->total() === 1 ? 'propiedad encontrada' : 'propiedades encontradas' }}
        </span>

        @if(request()->hasAny(['tipo','ciudad','precio_min','precio_max']))
            <span style="color:#ccc;">|</span>
            <span style="font-size:.75rem;color:#aaa;">Filtros:</span>
            @if(request('tipo'))
                <span class="filter-chip">
                    {{ ucfirst(str_replace('_',' ', request('tipo'))) }}
                    <a href="{{ route('catalogo.index', request()->except('tipo')) }}">×</a>
                </span>
            @endif
            @if(request('ciudad'))
                <span class="filter-chip">
                    {{ request('ciudad') }}
                    <a href="{{ route('catalogo.index', request()->except('ciudad')) }}">×</a>
                </span>
            @endif
            @if(request('precio_min') || request('precio_max'))
                <span class="filter-chip">
                    ${{ number_format(request('precio_min',0)) }} – ${{ request('precio_max') ? number_format(request('precio_max')) : '∞' }}
                    <a href="{{ route('catalogo.index', request()->except(['precio_min','precio_max'])) }}">×</a>
                </span>
            @endif
        @endif

        @if($propiedades->lastPage() > 1)
            <span class="ms-auto" style="font-size:.73rem;color:#bbb;">
                Página {{ $propiedades->currentPage() }} de {{ $propiedades->lastPage() }}
            </span>
        @endif
    </div>
</div>

{{-- ─── GRID DE PROPIEDADES ─────────────────────────────────────── --}}
<div class="container py-4">

    @if($propiedades->isNotEmpty())
        <div class="row g-3 mb-4">
            @foreach($propiedades as $prop)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <a href="{{ route('catalogo.show', $prop) }}" class="prop-card">
                    <div class="prop-img-wrap">
                        @if($prop->portada)
                            <img src="{{ Storage::url($prop->portada->ruta) }}"
                                 alt="{{ $prop->titulo }}" loading="lazy">
                        @else
                            <div class="no-img"><i class="bi bi-image"></i></div>
                        @endif
                        <span class="prop-badge-tipo">{{ $prop->tipo_label }}</span>
                        @if($prop->negociable)
                            <span class="prop-badge-neg"><i class="bi bi-check-circle me-1"></i>Negociable</span>
                        @endif
                    </div>
                    <div class="prop-body">
                        <div class="prop-price">{{ $prop->precio_formateado }}</div>
                        <div class="prop-title">{{ $prop->titulo }}</div>
                        <div class="prop-location">
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            {{ $prop->ciudad }}{{ $prop->sector ? ' · '.$prop->sector : '' }}
                        </div>
                        <div class="prop-specs">
                            @if($prop->metros_terreno)
                                <span class="prop-spec"><i class="bi bi-grid-3x3-gap"></i>{{ number_format($prop->metros_terreno,0) }} m²</span>
                            @endif
                            @if($prop->metros_construccion)
                                <span class="prop-spec"><i class="bi bi-building"></i>{{ number_format($prop->metros_construccion,0) }} m²</span>
                            @endif
                            @if($prop->numero_habitaciones !== null)
                                <span class="prop-spec"><i class="bi bi-door-open"></i>{{ $prop->numero_habitaciones }}</span>
                            @endif
                            @if($prop->numero_banos !== null)
                                <span class="prop-spec"><i class="bi bi-droplet"></i>{{ $prop->numero_banos }}</span>
                            @endif
                            @if($prop->parqueaderos !== null)
                                <span class="prop-spec"><i class="bi bi-car-front"></i>{{ $prop->parqueaderos }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-2">
            {{ $propiedades->links() }}
        </div>

    @else
        <div class="empty-state">
            <i class="bi bi-house-slash"></i>
            <h5 class="fw-semibold mb-2">Sin resultados</h5>
            <p class="text-muted small mb-3">No encontramos propiedades con esos filtros.</p>
            <a href="{{ route('catalogo.index') }}" class="btn"
               style="background:var(--pg-red);color:#fff;font-size:.85rem;border-radius:7px;">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Ver todas las propiedades
            </a>
        </div>
    @endif

</div>

@endsection
