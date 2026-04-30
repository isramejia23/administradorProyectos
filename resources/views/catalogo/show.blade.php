@extends('layouts.catalogo')

@section('title', $propiedad->titulo)

@push('styles')
<style>
    /* ─── BREADCRUMB ─── */
    .pg-breadcrumb {
        background: #fff;
        border-bottom: 1px solid #eaeaea;
        padding: .65rem 0;
    }
    .pg-breadcrumb .bc-inner {
        display: flex; align-items: center; gap: .5rem;
        font-size: .78rem; color: #999;
    }
    .pg-breadcrumb a { color: #999; text-decoration: none; transition: color .15s; }
    .pg-breadcrumb a:hover { color: var(--pg-red); }
    .pg-breadcrumb .sep { font-size: .65rem; }
    .pg-breadcrumb .current { color: #444; font-weight: 500; }

    /* ─── GALLERY ─── */
    .gallery-main {
        border-radius: 12px; overflow: hidden;
        background: #f0f0f0;
        aspect-ratio: 16/9;
        position: relative;
    }
    .gallery-main img {
        width: 100%; height: 100%; object-fit: cover;
        transition: opacity .2s ease;
    }
    .gallery-main .no-photo {
        width: 100%; height: 100%;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        color: #ccc; font-size: 3rem;
    }
    .thumb-strip { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: .5rem; }
    .thumb-strip img {
        width: 72px; height: 56px; object-fit: cover;
        border-radius: 7px; cursor: pointer;
        border: 2px solid transparent; transition: border-color .15s, opacity .15s;
        opacity: .75;
    }
    .thumb-strip img:hover { opacity: 1; }
    .thumb-strip img.active { border-color: var(--pg-red); opacity: 1; }
    .photo-count {
        position: absolute; bottom: 10px; right: 10px;
        background: rgba(0,0,0,.55); color: #fff;
        font-size: .72rem; padding: 4px 10px; border-radius: 20px;
        backdrop-filter: blur(4px);
    }

    /* ─── DETAIL PANEL ─── */
    .detail-panel { position: sticky; top: 78px; }

    .price-block { margin-bottom: 1.25rem; }
    .price-block .price {
        font-size: 2rem; font-weight: 800; color: var(--pg-dark); line-height: 1;
    }
    .price-block .neg-tag {
        display: inline-flex; align-items: center; gap: 4px;
        background: #e6f4ec; color: #198754;
        font-size: .72rem; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        margin-top: 4px;
    }

    .badge-tipo {
        background: var(--pg-dark); color: #fff;
        font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
        padding: 4px 12px; border-radius: 20px;
    }
    .badge-proceso {
        background: #fff3cd; color: #856404;
        font-size: .7rem; font-weight: 700;
        padding: 4px 12px; border-radius: 20px;
    }

    .prop-address {
        display: flex; align-items: flex-start; gap: 7px;
        color: #666; font-size: .85rem; margin: .75rem 0 1.25rem;
    }
    .prop-address i { color: var(--pg-red); margin-top: 2px; flex-shrink: 0; }

    /* Feature grid */
    .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; margin-bottom: 1.25rem; }
    .feat-item {
        background: #f8f8f8; border-radius: 9px;
        padding: .65rem .5rem; text-align: center;
        border: 1px solid #ececec;
    }
    .feat-item i { font-size: 1.1rem; color: var(--pg-red); display: block; margin-bottom: 3px; }
    .feat-item .feat-val { font-size: .88rem; font-weight: 700; color: var(--pg-dark); display: block; }
    .feat-item .feat-lbl { font-size: .65rem; color: #aaa; display: block; text-transform: uppercase; letter-spacing: .05em; }

    /* Contact card */
    .contact-card {
        background: linear-gradient(135deg, #1a1a1a, #2a0a10);
        border-radius: 12px;
        padding: 1.25rem;
        color: #fff;
    }
    .contact-card .cc-title { font-weight: 700; font-size: .95rem; margin-bottom: .25rem; }
    .contact-card .cc-sub   { font-size: .78rem; color: #aaa; margin-bottom: 1rem; }
    .btn-wa {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        background: #25D366; color: #fff; border: none;
        font-weight: 700; font-size: .9rem; padding: .65rem 1rem;
        border-radius: 8px; text-decoration: none; width: 100%;
        transition: background .15s, transform .1s;
    }
    .btn-wa:hover { background: #1db954; transform: translateY(-1px); color: #fff; }
    .btn-wa i { font-size: 1.1rem; }
    .btn-call {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        background: rgba(255,255,255,.08); color: #ddd; border: 1px solid rgba(255,255,255,.15);
        font-size: .82rem; padding: .55rem 1rem;
        border-radius: 8px; text-decoration: none; width: 100%;
        transition: background .15s;
        margin-top: .5rem;
    }
    .btn-call:hover { background: rgba(255,255,255,.14); color: #fff; }

    /* Description */
    .desc-section {
        background: #fff; border-radius: 12px;
        padding: 1.5rem; margin-top: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 1px solid #f0f0f0;
    }
    .desc-section h6 {
        font-size: .75rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: .09em; color: var(--pg-gray); margin-bottom: .75rem;
    }
    .desc-section .red-line { width: 28px; height: 2px; background: var(--pg-red); margin-bottom: .85rem; }
    .desc-section p { color: #444; font-size: .9rem; line-height: 1.75; margin: 0; white-space: pre-line; }
</style>
@endpush

@section('content')

{{-- ─── BREADCRUMB ─────────────────────────────────────────────── --}}
<div class="pg-breadcrumb">
    <div class="container">
        <div class="bc-inner">
            <a href="{{ route('catalogo.index') }}"><i class="bi bi-grid-3x3-gap-fill me-1"></i>Catálogo</a>
            <span class="sep"><i class="bi bi-chevron-right"></i></span>
            @if(request('tipo'))
            <a href="{{ route('catalogo.index', ['tipo' => request('tipo')]) }}">
                {{ ucfirst(str_replace('_',' ', request('tipo'))) }}
            </a>
            <span class="sep"><i class="bi bi-chevron-right"></i></span>
            @endif
            <span class="current">{{ Str::limit($propiedad->titulo, 45) }}</span>
        </div>
    </div>
</div>

{{-- ─── CONTENIDO ──────────────────────────────────────────────── --}}
<div class="container py-4">
    <div class="row g-4">

        {{-- ── Galería ── --}}
        <div class="col-lg-7">
            <div class="gallery-main">
                @if($propiedad->fotos->isNotEmpty())
                    @php $portada = $propiedad->fotos->firstWhere('es_portada', true) ?? $propiedad->fotos->first(); @endphp
                    <img id="main-photo"
                         src="{{ Storage::url($portada->ruta) }}"
                         alt="{{ $propiedad->titulo }}">
                    @if($propiedad->fotos->count() > 1)
                        <div class="photo-count">
                            <i class="bi bi-images me-1"></i>
                            <span id="photo-idx">1</span>/{{ $propiedad->fotos->count() }}
                        </div>
                    @endif
                @else
                    <div class="no-photo">
                        <i class="bi bi-image"></i>
                        <span style="font-size:.85rem;margin-top:.5rem;">Sin fotos disponibles</span>
                    </div>
                @endif
            </div>

            @if($propiedad->fotos->count() > 1)
            <div class="thumb-strip">
                @foreach($propiedad->fotos as $i => $foto)
                <img src="{{ Storage::url($foto->ruta) }}"
                     alt="Foto {{ $i+1 }}"
                     class="{{ $foto->es_portada ? 'active' : '' }}"
                     data-src="{{ Storage::url($foto->ruta) }}"
                     data-idx="{{ $i+1 }}"
                     onclick="setPhoto(this)">
                @endforeach
            </div>
            @endif
        </div>

        {{-- ── Panel de detalle ── --}}
        <div class="col-lg-5">
            <div class="detail-panel">

                {{-- Badges --}}
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge-tipo">{{ $propiedad->tipo_label }}</span>
                    @if($propiedad->estado === 'en_proceso')
                        <span class="badge-proceso"><i class="bi bi-clock me-1"></i>En negociación</span>
                    @endif
                </div>

                {{-- Título --}}
                <h1 style="font-size:1.45rem;font-weight:800;color:var(--pg-dark);margin-bottom:.1rem;line-height:1.2;">
                    {{ $propiedad->titulo }}
                </h1>

                {{-- Ubicación --}}
                <div class="prop-address">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>
                        {{ $propiedad->direccion ?? '' }}
                        @if($propiedad->sector || $propiedad->ciudad)
                            @if($propiedad->direccion) &mdash; @endif
                            {{ implode(', ', array_filter([$propiedad->sector, $propiedad->ciudad])) }}
                        @endif
                    </span>
                </div>

                {{-- Precio --}}
                <div class="price-block">
                    <div class="price">{{ $propiedad->precio_formateado }}</div>
                    @if($propiedad->negociable)
                        <div class="neg-tag"><i class="bi bi-check-circle-fill"></i>Precio negociable</div>
                    @endif
                </div>

                {{-- Características --}}
                @php
                    $feats = [];
                    if($propiedad->metros_terreno)        $feats[] = ['icon'=>'bi-grid-3x3-gap',   'val'=>number_format($propiedad->metros_terreno,0).' m²',       'lbl'=>'Terreno'];
                    if($propiedad->metros_construccion)   $feats[] = ['icon'=>'bi-building',        'val'=>number_format($propiedad->metros_construccion,0).' m²',  'lbl'=>'Construcción'];
                    if($propiedad->numero_habitaciones !== null) $feats[] = ['icon'=>'bi-door-open', 'val'=>$propiedad->numero_habitaciones,                          'lbl'=>'Habitaciones'];
                    if($propiedad->numero_banos !== null)       $feats[] = ['icon'=>'bi-droplet',    'val'=>$propiedad->numero_banos,                                'lbl'=>'Baños'];
                    if($propiedad->parqueaderos !== null)       $feats[] = ['icon'=>'bi-car-front',  'val'=>$propiedad->parqueaderos,                                'lbl'=>'Parqueaderos'];
                @endphp
                @if(count($feats) > 0)
                <div class="features-grid">
                    @foreach($feats as $f)
                    <div class="feat-item">
                        <i class="bi {{ $f['icon'] }}"></i>
                        <span class="feat-val">{{ $f['val'] }}</span>
                        <span class="feat-lbl">{{ $f['lbl'] }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Contacto --}}
                <div class="contact-card">
                    <div class="cc-title">¿Te interesa esta propiedad?</div>
                    <div class="cc-sub">Un asesor de Grupo Piguma te atenderá enseguida.</div>
                    <a href="https://wa.me/593000000000?text={{ urlencode('Hola, me interesa la propiedad «'.$propiedad->titulo.'» del catálogo de Grupo Piguma. ¿Podría darme más información?') }}"
                       target="_blank"
                       class="btn-wa">
                        <i class="bi bi-whatsapp"></i> Consultar por WhatsApp
                    </a>
                    <a href="tel:+593000000000" class="btn-call">
                        <i class="bi bi-telephone-fill"></i> Llamar ahora
                    </a>
                </div>

            </div>
        </div>

    </div>

    {{-- ─── Descripción ──────────────────────────────────────────── --}}
    @if($propiedad->descripcion)
    <div class="desc-section">
        <h6>Descripción de la propiedad</h6>
        <div class="red-line"></div>
        <p>{{ $propiedad->descripcion }}</p>
    </div>
    @endif

    {{-- ─── Volver ────────────────────────────────────────────────── --}}
    <div class="mt-4">
        <a href="{{ route('catalogo.index') }}"
           class="text-decoration-none"
           style="color:var(--pg-gray);font-size:.82rem;">
            <i class="bi bi-arrow-left me-1"></i>Volver al catálogo
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script>
function setPhoto(el) {
    document.getElementById('main-photo').src = el.dataset.src;
    document.getElementById('photo-idx').textContent = el.dataset.idx;
    document.querySelectorAll('.thumb-strip img').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}
</script>
@endpush
