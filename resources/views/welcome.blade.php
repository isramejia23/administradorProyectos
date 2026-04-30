<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupo Piguma — Sistema de Control Interno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --red:  #C8102E;
            --red2: #a80d24;
            --dark: #111111;
        }

        html, body {
            height: 100%; margin: 0; padding: 0;
            font-family: 'Inter', sans-serif;
            background: var(--dark);
            color: #fff;
        }

        /* ─── FONDO ─── */
        .pg-bg {
            min-height: 100vh;
            background: var(--dark);
            position: relative;
            overflow: hidden;
        }

        /* Gradiente de acento rojo */
        .pg-bg::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 70% 60% at 15% 90%, rgba(200,16,46,.18) 0%, transparent 55%),
                radial-gradient(ellipse 50% 40% at 85% 10%, rgba(200,16,46,.10) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Líneas decorativas de fondo */
        .pg-bg::after {
            content: '';
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        /* ─── NAVBAR ─── */
        .top-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            padding: 1rem 0;
            background: linear-gradient(to bottom, rgba(0,0,0,.6), transparent);
        }
        .top-bar .brand {
            display: flex; align-items: center; gap: .6rem;
            text-decoration: none;
        }
        .top-bar .brand img { height: 32px; object-fit: contain; }
        .top-bar .brand-name { color: #fff; font-weight: 700; font-size: .9rem; }
        .top-bar .brand-sub  { color: #666; font-size: .6rem; text-transform: uppercase; letter-spacing: .1em; }

        /* ─── HERO ─── */
        .hero {
            position: relative; z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 6rem 1.5rem 4rem;
        }

        .hero-logo {
            width: 110px; height: 110px; object-fit: contain;
            margin-bottom: 2rem;
            filter: drop-shadow(0 0 24px rgba(200,16,46,.3));
        }

        .hero-eyebrow {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(200,16,46,.15);
            border: 1px solid rgba(200,16,46,.3);
            color: #ff8a96;
            font-size: .7rem; font-weight: 700; letter-spacing: .12em;
            text-transform: uppercase;
            padding: 5px 16px; border-radius: 20px;
            margin-bottom: 1.5rem;
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: .75rem;
            color: #fff;
        }

        .hero h1 .accent {
            color: var(--red);
        }

        .hero .hero-sub {
            font-size: 1rem;
            color: #777;
            max-width: 440px;
            margin: 0 auto 2.5rem;
            line-height: 1.65;
        }

        /* Botón CTA */
        .btn-cta {
            display: inline-flex; align-items: center; gap: 9px;
            background: var(--red); color: #fff; border: none;
            font-weight: 700; font-size: .95rem;
            padding: .8rem 2.2rem; border-radius: 8px;
            text-decoration: none;
            transition: background .15s, transform .15s, box-shadow .15s;
            box-shadow: 0 4px 20px rgba(200,16,46,.35);
        }
        .btn-cta:hover {
            background: var(--red2); color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(200,16,46,.45);
        }

        /* ─── TARJETAS DE MÓDULOS ─── */
        .modules {
            position: relative; z-index: 1;
            padding: 0 1.5rem 5rem;
        }
        .module-card {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: background .2s, border-color .2s, transform .2s;
        }
        .module-card:hover {
            background: rgba(255,255,255,.07);
            border-color: rgba(200,16,46,.3);
            transform: translateY(-3px);
        }
        .module-icon {
            width: 48px; height: 48px; border-radius: 50%;
            background: rgba(200,16,46,.15);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto .9rem;
        }
        .module-icon i { font-size: 1.2rem; color: var(--red); }
        .module-card h6 { color: #fff; font-weight: 700; font-size: .88rem; margin-bottom: .35rem; }
        .module-card p  { color: #666; font-size: .78rem; margin: 0; line-height: 1.5; }

        /* ─── DIVIDER ─── */
        .section-divider {
            position: relative; z-index: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.08), transparent);
            margin: 0 auto 3.5rem;
            max-width: 600px;
        }

        /* ─── FOOTER ─── */
        .pg-footer {
            position: relative; z-index: 1;
            border-top: 1px solid rgba(255,255,255,.06);
            padding: 1.25rem 0;
            text-align: center;
            color: #444;
            font-size: .72rem;
        }
        .pg-footer span { color: var(--red); }
    </style>
</head>
<body>

<div class="pg-bg">

    {{-- Navbar superior --}}
    <nav class="top-bar">
        <div class="container d-flex align-items-center justify-content-between">
            <a class="brand" href="#">
                @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                    <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Grupo Piguma">
                @endif
                <div>
                    <div class="brand-name">Grupo Piguma</div>
                    <div class="brand-sub">Centro de Negocios</div>
                </div>
            </a>
            <a href="{{ route('login') }}"
               style="font-size:.8rem;color:#888;text-decoration:none;transition:color .15s;"
               onmouseover="this.style.color='#fff'"
               onmouseout="this.style.color='#888'">
                <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
            </a>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="hero">

        @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
            <img src="{{ asset('imagenes/logo-piguma.jpeg') }}"
                 alt="Grupo Piguma" class="hero-logo">
        @endif

        <div class="hero-eyebrow">
            <i class="bi bi-shield-lock-fill"></i> Acceso restringido
        </div>

        <h1>Sistema de<br><span class="accent">Control Interno</span></h1>

        <p class="hero-sub">
            Plataforma de gestión operativa exclusiva para el equipo de Grupo Piguma.
        </p>

        <a href="{{ route('login') }}" class="btn-cta">
            <i class="bi bi-box-arrow-in-right"></i>
            Acceder al sistema
        </a>

    </section>

    {{-- Módulos --}}
    <div class="section-divider"></div>

    <section class="modules">
        <div class="container">
            <div class="row g-3 justify-content-center" style="max-width:820px;margin:0 auto;">

                <div class="col-6 col-md-3">
                    <div class="module-card">
                        <div class="module-icon"><i class="bi bi-kanban-fill"></i></div>
                        <h6>Proyectos</h6>
                        <p>Seguimiento de trabajos y subtrabajos por estado</p>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="module-card">
                        <div class="module-icon"><i class="bi bi-building"></i></div>
                        <h6>Propiedades</h6>
                        <p>Catálogo y gestión de bienes inmuebles</p>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="module-card">
                        <div class="module-icon"><i class="bi bi-people-fill"></i></div>
                        <h6>Equipos</h6>
                        <p>Usuarios, roles y departamentos</p>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="module-card">
                        <div class="module-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <h6>Dashboard</h6>
                        <p>Métricas y línea de tiempo en tiempo real</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="pg-footer">
        &copy; {{ date('Y') }} <span>Grupo Piguma</span> &mdash; Todos los derechos reservados
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
