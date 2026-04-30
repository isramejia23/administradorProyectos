<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Catálogo de Propiedades') — Grupo Piguma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --pg-red:   #C8102E;
            --pg-red2:  #a80d24;
            --pg-gray:  #58595B;
            --pg-dark:  #1a1a1a;
            --pg-bg:    #f4f4f6;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { background: var(--pg-bg); font-family: 'Inter', sans-serif; margin: 0; color: var(--pg-dark); }

        /* ─── NAVBAR ─── */
        .pg-nav {
            background: var(--pg-dark);
            padding: .8rem 0;
            position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 2px 16px rgba(0,0,0,.35);
        }
        .pg-nav .brand {
            display: flex; align-items: center; gap: .7rem;
            text-decoration: none;
        }
        .pg-nav .brand img { height: 36px; width: auto; object-fit: contain; }
        .pg-nav .brand-name { color: #fff; font-weight: 700; font-size: .95rem; line-height: 1.1; }
        .pg-nav .brand-sub  { color: #888; font-size: .6rem; letter-spacing: .09em; text-transform: uppercase; }
        .pg-nav-links { display: flex; align-items: center; gap: 1.5rem; }
        .pg-nav-links a {
            color: #bbb; font-size: .82rem; text-decoration: none;
            transition: color .15s; display: flex; align-items: center; gap: 5px;
        }
        .pg-nav-links a:hover, .pg-nav-links a.active { color: #fff; }
        .pg-nav-links a.active { font-weight: 600; }
        .pg-nav-links .nav-divider { width: 1px; height: 18px; background: #333; }
        .btn-acceso {
            background: var(--pg-red); border: none; color: #fff !important;
            font-size: .78rem; font-weight: 600;
            padding: .38rem 1rem; border-radius: 5px;
            text-decoration: none !important;
            transition: background .15s, transform .1s;
            display: flex; align-items: center; gap: 5px;
        }
        .btn-acceso:hover { background: var(--pg-red2) !important; transform: translateY(-1px); }

        /* ─── FOOTER ─── */
        .pg-footer {
            background: #111;
            color: #777;
            padding: 3rem 0 0;
            margin-top: 5rem;
        }
        .pg-footer .footer-logo img { height: 44px; }
        .pg-footer .footer-desc { font-size: .82rem; color: #666; line-height: 1.65; margin-top: .6rem; }
        .pg-footer h6 {
            color: #fff; font-size: .75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .09em; margin-bottom: .5rem;
        }
        .pg-footer .red-accent { width: 28px; height: 2px; background: var(--pg-red); margin-bottom: .85rem; }
        .pg-footer .footer-links a {
            color: #666; text-decoration: none; font-size: .8rem;
            display: flex; align-items: center; gap: 7px;
            padding: .25rem 0; transition: color .15s;
        }
        .pg-footer .footer-links a:hover { color: var(--pg-red); }
        .pg-footer .footer-links a i { font-size: .9rem; }
        .pg-footer .social-icons a {
            color: #555; font-size: 1.15rem; text-decoration: none;
            transition: color .15s;
        }
        .pg-footer .social-icons a:hover { color: var(--pg-red); }
        .pg-footer .footer-bottom {
            border-top: 1px solid #222;
            padding: 1rem 0;
            margin-top: 2.5rem;
        }
        .pg-footer .footer-bottom span { font-size: .72rem; color: #444; }
    </style>
</head>
<body>

{{-- ─── NAVBAR ──────────────────────────────────────────────────── --}}
<nav class="pg-nav">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <a class="brand" href="{{ route('catalogo.index') }}">
                @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                    <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Grupo Piguma">
                @endif
                <div>
                    <div class="brand-name">Grupo Piguma</div>
                    <div class="brand-sub">Centro de Negocios</div>
                </div>
            </a>

            <div class="pg-nav-links">
                <a href="{{ route('catalogo.index') }}" class="{{ request()->routeIs('catalogo.index') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap-fill"></i> Propiedades
                </a>
                <a href="tel:+593000000000">
                    <i class="bi bi-telephone"></i> Llamar
                </a>
                <a href="https://wa.me/593000000000?text={{ urlencode('Hola, me interesa una propiedad del catálogo de Grupo Piguma.') }}" target="_blank">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>
    </div>
</nav>

{{-- ─── CONTENIDO ───────────────────────────────────────────────── --}}
@yield('content')

{{-- ─── FOOTER ──────────────────────────────────────────────────── --}}
<footer class="pg-footer">
    <div class="container">
        <div class="row g-5">
            {{-- Marca --}}
            <div class="col-md-4">
                <div class="footer-logo">
                    @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                        <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Grupo Piguma">
                    @endif
                </div>
                <p class="footer-desc">
                    Tu aliado de confianza en bienes raíces.<br>
                    Casas, departamentos, terrenos y espacios comerciales seleccionados para ti.
                </p>
                <div class="social-icons d-flex gap-3 mt-3">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-tiktok"></i></a>
                    <a href="#"><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>

            {{-- Propiedades --}}
            <div class="col-sm-6 col-md-4">
                <h6>Tipo de propiedad</h6>
                <div class="red-accent"></div>
                <div class="footer-links">
                    <a href="{{ route('catalogo.index', ['tipo'=>'casa']) }}"><i class="bi bi-house-door"></i>Casas</a>
                    <a href="{{ route('catalogo.index', ['tipo'=>'departamento']) }}"><i class="bi bi-building"></i>Departamentos</a>
                    <a href="{{ route('catalogo.index', ['tipo'=>'terreno']) }}"><i class="bi bi-map"></i>Terrenos</a>
                    <a href="{{ route('catalogo.index', ['tipo'=>'local_comercial']) }}"><i class="bi bi-shop"></i>Locales Comerciales</a>
                    <a href="{{ route('catalogo.index', ['tipo'=>'oficina']) }}"><i class="bi bi-briefcase"></i>Oficinas</a>
                </div>
            </div>

            {{-- Contacto --}}
            <div class="col-sm-6 col-md-4">
                <h6>Contáctanos</h6>
                <div class="red-accent"></div>
                <div class="footer-links">
                    <a href="tel:+593000000000"><i class="bi bi-telephone"></i>+593 XX XXX XXXX</a>
                    <a href="mailto:contacto@grupopiguma.com"><i class="bi bi-envelope"></i>contacto@grupopiguma.com</a>
                    <a href="#"><i class="bi bi-geo-alt"></i>Ecuador</a>
                    <a href="https://wa.me/593000000000" target="_blank"><i class="bi bi-whatsapp"></i>WhatsApp</a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>&copy; {{ date('Y') }} Grupo Piguma &mdash; Todos los derechos reservados.</span>
                <span>Catálogo de Propiedades</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
