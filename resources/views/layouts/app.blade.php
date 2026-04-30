<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupo Piguma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --color-primary:   #111111;
            --color-sidebar:   #1a1a1a;
            --color-active:    #333333;
            --color-section:   #666666;
            --color-accent:    #ffffff;
            --color-muted:     #aaaaaa;
        }

        /* ── Layout base ── */
        body { margin: 0; display: flex; flex-direction: column; min-height: 100vh; background: #ffffff; }

        /* ── Navbar superior ── */
        .navbar-top {
            background-color: var(--color-primary);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1050;
            box-shadow: 0 2px 8px rgba(0,0,0,.5);
        }
        .navbar-top .brand {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            text-decoration: none;
            margin-left: .75rem;
            letter-spacing: .02em;
        }
        .navbar-top .ms-auto { margin-left: auto; display: flex; align-items: center; gap: .75rem; }
        .navbar-top .user-name { color: var(--color-muted); font-size: .88rem; }

        /* ── Burger ── */
        #burgerBtn {
            background: none; border: none; cursor: pointer;
            display: flex; flex-direction: column; justify-content: center;
            gap: 5px; width: 36px; height: 36px; padding: 4px;
        }
        #burgerBtn span {
            display: block; height: 2.5px; width: 100%;
            background: #fff; border-radius: 2px;
            transition: all .28s ease;
        }
        body.sidebar-open #burgerBtn span:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
        body.sidebar-open #burgerBtn span:nth-child(2) { opacity: 0; transform: scaleX(0); }
        body.sidebar-open #burgerBtn span:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

        /* ── Sidebar ── */
        #sidebar {
            position: fixed;
            top: 56px; left: -225px;
            width: 225px;
            height: calc(100vh - 56px);
            background-color: var(--color-sidebar);
            z-index: 1040;
            overflow-y: auto;
            transition: left .28s ease;
            padding-top: .5rem;
        }
        body.sidebar-open #sidebar { left: 0; }

        #sidebar .nav-section {
            padding: .5rem 1.1rem .25rem;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--color-section);
            font-weight: 600;
        }
        #sidebar a.sidebar-link {
            display: flex; align-items: center; gap: .65rem;
            padding: .62rem 1.2rem;
            color: #cccccc;
            text-decoration: none;
            font-size: .9rem;
            transition: background .18s, color .18s;
            border-left: 3px solid transparent;
        }
        #sidebar a.sidebar-link:hover {
            background-color: var(--color-active);
            color: #fff;
        }
        #sidebar a.sidebar-link.active {
            background-color: var(--color-active);
            color: #fff;
            border-left-color: #fff;
        }
        #sidebar hr { border-color: #2e2e2e; margin: .35rem 1rem; }

        /* Botón de sección colapsable */
        #sidebar .sidebar-collapse-btn {
            display: flex; align-items: center; justify-content: space-between;
            width: 100%; padding: .62rem 1.2rem;
            color: #cccccc; font-size: .9rem;
            background: none; border: none; border-left: 3px solid transparent;
            cursor: pointer; text-align: left;
            transition: background .18s, color .18s;
        }
        #sidebar .sidebar-collapse-btn:hover { background: var(--color-active); color: #fff; }
        #sidebar .sidebar-collapse-btn.open   { color: #fff; }
        #sidebar .sidebar-collapse-btn .chev {
            font-size: .7rem; transition: transform .22s ease; flex-shrink: 0;
        }
        #sidebar .sidebar-collapse-btn.open .chev { transform: rotate(180deg); }
        #sidebar .collapse-sub a.sidebar-link { padding-left: 2.4rem; }

        /* Overlay — deshabilitado: sidebar es push, no modal */
        #sidebarOverlay { display: none !important; }

        /* ── Contenido ── */
        #mainContent {
            margin-top: 56px;
            margin-left: 0;
            padding: 1.75rem;
            flex: 1;
            transition: margin-left .28s ease;
        }
        body.sidebar-open #mainContent { margin-left: 225px; }

        /* ── Tablas (sin divisiones verticales) ── */
        .table { border-collapse: collapse; }
        .table thead th {
            background-color: #2d2d2d;
            color: #fff;
            border: none;
            font-weight: 500;
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            padding: .525rem 1rem;
        }
        .table tbody td {
            border-left: none;
            border-right: none;
            border-top: 1px solid #e8e8e8;
            padding: .42rem 1rem;
            vertical-align: middle;
        }
        .table tbody tr:hover td { background-color: #f0f0f0; }

        /* ── Botones principales (filled) ── */
        .btn-primary { background-color: #0d6efd; border-color: #0d6efd; color: #fff; font-weight: 500; }
        .btn-primary:hover, .btn-primary:focus { background-color: #0b5ed7; border-color: #0b5ed7; color: #fff; }
        .btn-outline-secondary { border-color: #aaa; color: #555; }
        .btn-outline-secondary:hover { background-color: #555; color: #fff; border-color: #555; }

        /* ── SweetAlert2: confirm = azul outline ── */
        .swal2-confirm {
            background: transparent !important;
            border: 2px solid #0d6efd !important;
            color: #0d6efd !important;
        }
        .swal2-confirm:hover {
            background: #0d6efd !important;
            color: #fff !important;
        }

        /* ── SweetAlert2: cancel = rojo outline ── */
        .swal2-cancel {
            background: transparent !important;
            border: 2px solid #dc3545 !important;
            color: #dc3545 !important;
        }
        .swal2-cancel:hover {
            background: #dc3545 !important;
            color: #fff !important;
        }

        /* ── Cabeceras de tarjetas (cards) ── */
        .card-section-title {
            background: linear-gradient(90deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            margin: -1rem -1rem 1rem -1rem;
            padding: .6rem 1rem;
            border-radius: .375rem .375rem 0 0;
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            border-bottom: none !important;
        }

        /* ── Título de página con logo ── */
        .page-title {
            display: flex;
            align-items: center;
            gap: .6rem;
            font-size: 1.15rem;
            font-weight: 600;
            margin: 0;
        }
        .page-title img {
            height: 36px;
            width: auto;
            object-fit: contain;
        }
        .page-title .title-icon {
            font-size: 1.4rem;
            color: #111;
            line-height: 1;
        }

        /* Logout */
        .logout-link { color: var(--color-muted); font-size: .88rem; cursor: pointer; background: none; border: none; }
        .logout-link:hover { color: #fff; }

        /* ── Badge naranja personalizado ── */
        .bg-naranja { background-color: #db6b10ff !important; color: #fff !important; }

        /* ── Barra de urgencia ── */
        .urgencia-bar {
            display: flex;
            gap: 3px;
            align-items: center;
        }
        .urgencia-seg {
            display: block;
            width: 22px;
            height: 8px;
            border-radius: 3px;
            background: #dee2e6;
        }
        .urgencia-seg.active { opacity: 1; }

        /* ── Bandeja navbar ── */
        .navbar-inbox {
            position: relative;
            color: #ccc;
            font-size: 1.25rem;
            line-height: 1;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .navbar-inbox:hover { color: #fff; }
        .inbox-badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #dc3545;
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            line-height: 1;
        }

        /* ── Spinner de carga global ── */
        #global-spinner {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(2px);
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .75rem;
        }
        #global-spinner.visible { display: flex; }
        #global-spinner .spinner-ring {
            width: 52px;
            height: 52px;
            border: 4px solid #e0e0e0;
            border-top-color: #0d6efd;
            border-radius: 50%;
            animation: spin .75s linear infinite;
        }
        #global-spinner p {
            margin: 0;
            font-size: .95rem;
            color: #555;
            letter-spacing: .02em;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

    {{-- ═══════════ NAVBAR ═══════════ --}}
    <header class="navbar-top">
        @auth
        <button id="burgerBtn" title="Menú" onclick="toggleSidebar()">
            <span></span><span></span><span></span>
        </button>
        @endauth
        <a class="brand" href="{{ url('/home') }}">
            @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                <img src="{{ asset('imagenes/logo-piguma.jpeg') }}" alt="Grupo Piguma"
                     style="height:32px;width:auto;object-fit:contain;vertical-align:middle;">
            @else
                Grupo Piguma
            @endif
        </a>

        <div class="ms-auto">
            @auth
                @can('ver-bandeja-trabajo')
                @php
                    $authUser     = Auth::user();
                    $deptoId      = $authUser->departamento_id;
                    $globalView   = $authUser->veeTodosLosDepartamentos();
                    $bandejaCount = \App\Models\Trabajo::whereNull('responsable_id')
                            ->when(! $globalView, fn($q) => $q->where('departamento_id', $deptoId))
                            ->count()
                        + \App\Models\Subtrabajo::whereNull('responsable_id')
                            ->where('es_principal', false)
                            ->when(! $globalView, fn($q) => $q->where('departamento_id', $deptoId))
                            ->count();
                @endphp
                <a href="{{ route('proyectos.bandeja') }}" class="navbar-inbox" title="Bandeja de entrada">
                    <i class="bi bi-inbox-fill"></i>
                    @if($bandejaCount > 0)
                    <span class="inbox-badge">{{ $bandejaCount > 99 ? '99+' : $bandejaCount }}</span>
                    @endif
                </a>
                @endcan
                <span class="user-name">{{ Auth::user()->nombre }} {{ Auth::user()->apellido }}</span>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                <button class="logout-link" onclick="document.getElementById('logout-form').submit()">Salir</button>
            @endauth
        </div>
    </header>

    {{-- ═══════════ SPINNER GLOBAL ═══════════ --}}
    <div id="global-spinner">
        <div class="spinner-ring"></div>
        <p>Cargando...</p>
    </div>

    {{-- ═══════════ OVERLAY ═══════════ --}}
    <div id="sidebarOverlay" onclick="toggleSidebar()"></div>

    {{-- ═══════════ SIDEBAR ═══════════ --}}
    @auth
    @php
        $configOpen = request()->routeIs('departamentos.*')
                   || request()->routeIs('usuarios.*')
                   || request()->routeIs('roles.*');
    @endphp
    <nav id="sidebar">

        @can('ver-bandeja-trabajo')
        <a class="sidebar-link {{ request()->routeIs('proyectos.bandeja') ? 'active' : '' }}" href="{{ route('proyectos.bandeja') }}">
            <i class="bi bi-inbox"></i> Bandeja de Entrada
        </a>
        @endcan

        @can('ver-cliente')
        <a class="sidebar-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
            <i class="bi bi-people"></i> Clientes
        </a>
        @endcan

        @canany(['ver-trabajo', 'ver-trabajo-departamento'])
        <a class="sidebar-link {{ request()->routeIs('proyectos.index') || request()->routeIs('proyectos.show') || request()->routeIs('proyectos.create') || request()->routeIs('proyectos.edit') ? 'active' : '' }}"
           href="{{ route('proyectos.index') }}">
            <i class="bi bi-kanban"></i> Proyectos
        </a>
        @endcanany

        @can('ver-subtrabajo')
        <a class="sidebar-link {{ request()->routeIs('subtrabajos.index') ? 'active' : '' }}" href="{{ route('subtrabajos.index') }}">
            <i class="bi bi-diagram-3"></i> Subtrabajos
        </a>
        @endcan

        @can('ver-mis-trabajos')
        <a class="sidebar-link {{ request()->routeIs('proyectos.mis') ? 'active' : '' }}" href="{{ route('proyectos.mis') }}">
            <i class="bi bi-person-check"></i> Mis Proyectos
        </a>
        @endcan

        @can('ver-mis-trabajos')
        <a class="sidebar-link {{ request()->routeIs('subtrabajos.mis') ? 'active' : '' }}" href="{{ route('subtrabajos.mis') }}">
            <i class="bi bi-diagram-3"></i> Mis Subtrabajos
        </a>
        @endcan

        @can('ver-solicitudes-trabajo')
        <a class="sidebar-link {{ request()->routeIs('proyectos.solicitudes') || request()->routeIs('proyectos.aprobar') ? 'active' : '' }}"
           href="{{ route('proyectos.solicitudes') }}">
            <i class="bi bi-file-earmark-check"></i> Solicitud de Proyectos
        </a>
        @endcan

        @can('ver-ventas-admin')
        <a class="sidebar-link {{ request()->routeIs('proyectos.vendedores') ? 'active' : '' }}" href="{{ route('proyectos.vendedores') }}">
            <i class="bi bi-people-fill"></i> Proyectos por Vendedor
        </a>
        @endcan

        @can('ver-mis-ventas')
        <a class="sidebar-link {{ request()->routeIs('proyectos.ventas') ? 'active' : '' }}" href="{{ route('proyectos.ventas') }}">
            <i class="bi bi-graph-up-arrow"></i> Mis Ventas
        </a>
        @endcan

        @can('ver-cuenta-cobrar')
        <a class="sidebar-link {{ request()->routeIs('cobros.*') ? 'active' : '' }}" href="{{ route('cobros.index') }}">
            <i class="bi bi-cash-stack"></i> Cuentas por Cobrar
        </a>
        @endcan

        @can('ver-propiedad')
        <a class="sidebar-link {{ request()->routeIs('propiedades.*') ? 'active' : '' }}" href="{{ route('propiedades.index') }}">
            <i class="bi bi-house-door"></i> Propiedades en Venta
        </a>
        @endcan

        @can('ver-servicio')
        <a class="sidebar-link {{ request()->routeIs('servicios.*') ? 'active' : '' }}" href="{{ route('servicios.index') }}">
            <i class="bi bi-briefcase"></i> Servicios
        </a>
        @endcan

        @canany(['ver-departamento', 'ver-usuario', 'ver-rol'])
        <hr>
        <button class="sidebar-collapse-btn {{ $configOpen ? 'open' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseConfig"
                aria-expanded="{{ $configOpen ? 'true' : 'false' }}">
            <span class="d-flex align-items-center gap-2">
                <i class="bi bi-gear-fill"></i> Configuración
            </span>
            <i class="bi bi-chevron-down chev"></i>
        </button>
        <div class="collapse collapse-sub {{ $configOpen ? 'show' : '' }}" id="collapseConfig">
            @can('ver-departamento')
            <a class="sidebar-link {{ request()->routeIs('departamentos.*') ? 'active' : '' }}" href="{{ route('departamentos.index') }}">
                <i class="bi bi-building"></i> Departamentos
            </a>
            @endcan
            @can('ver-usuario')
            <a class="sidebar-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}" href="{{ route('usuarios.index') }}">
                <i class="bi bi-person"></i> Usuarios
            </a>
            @endcan
            @can('ver-rol')
            <a class="sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                <i class="bi bi-shield-lock"></i> Roles y Permisos
            </a>
            @endcan
        </div>
        @endcanany

    </nav>
    @endauth

    {{-- ═══════════ CONTENIDO ═══════════ --}}
    <main id="mainContent">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if(session('success'))
    <script>
        Swal.fire({ icon:'success', title:'{{ session('success') }}', showConfirmButton:false, timer:1500 });
    </script>
    @endif

    @if(session('error'))
    <script>
        Swal.fire({ icon:'error', title:'{{ session('error') }}', showConfirmButton:false, timer:2500 });
    </script>
    @endif

    @if(session('warning'))
    <script>
        Swal.fire({ icon:'warning', title:'Atención', text:'{{ session('warning') }}', confirmButtonText:'Entendido' });
    </script>
    @endif

    @if(session('nuevo_cliente_id'))
    <script>
        Swal.fire({
            icon: 'question',
            title: '¿Crear un proyecto?',
            html: 'El cliente <strong>{{ session("nuevo_cliente_nombre") }}</strong> fue registrado.<br>¿Desea crear un proyecto para este cliente?',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-plus-lg me-1"></i> Sí, crear proyecto',
            cancelButtonText: 'No por ahora',
            customClass: { confirmButton: 'btn btn-primary me-2', cancelButton: 'btn btn-outline-secondary' },
            buttonsStyling: false,
        }).then(result => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("proyectos.create") }}?cliente_id={{ session("nuevo_cliente_id") }}';
            }
        });
    </script>
    @endif

    <script>
        // ── Spinner global ──
        const spinner = document.getElementById('global-spinner');
        function showSpinner() { spinner.classList.add('visible'); }
        function hideSpinner() { spinner.classList.remove('visible'); }

        // Interceptar form.submit() programático (no dispara el evento 'submit' del DOM)
        const _nativeSubmit = HTMLFormElement.prototype.submit;
        HTMLFormElement.prototype.submit = function () {
            showSpinner();
            _nativeSubmit.call(this);
        };

        // Mostrar en envíos manuales (botón submit)
        document.addEventListener('submit', showSpinner);

        // Mostrar en navegación (links internos)
        document.addEventListener('click', function (e) {
            const a = e.target.closest('a');
            if (!a) return;
            const href = a.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript') || a.target === '_blank') return;
            if (a.hostname && a.hostname !== window.location.hostname) return;
            showSpinner();
        });

        // Ocultar si el navegador retrocede con bfcache
        window.addEventListener('pageshow', hideSpinner);

        function toggleSidebar() { document.body.classList.toggle('sidebar-open'); }

        // Sincronizar clase 'open' del botón de configuración con Bootstrap collapse
        document.addEventListener('DOMContentLoaded', function () {
            const collapseEl = document.getElementById('collapseConfig');
            const btn = collapseEl ? collapseEl.previousElementSibling : null;
            if (!collapseEl || !btn) return;
            collapseEl.addEventListener('show.bs.collapse',  () => btn.classList.add('open'));
            collapseEl.addEventListener('hide.bs.collapse',  () => btn.classList.remove('open'));
        });

        function confirmDelete(event, button) {
            event.preventDefault();
            Swal.fire({
                title: '¿Eliminar registro?',
                text: 'Esta acción no se puede revertir.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: 'transparent',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) button.closest('form').submit();
            });
        }

        /**
         * Confirmación genérica con botones Bootstrap.
         * @param {Event}       event
         * @param {HTMLElement} button      - botón dentro del <form>
         * @param {Object}      opts        - sobreescribe título/texto/icono/confirmButtonText
         */
        function confirmAction(event, button, opts = {}) {
            event.preventDefault();
            const swalMixin = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton:  'btn btn-danger'
                },
                buttonsStyling: false
            });
            swalMixin.fire({
                title:             opts.title             ?? '¿Estás seguro?',
                text:              opts.text              ?? 'Esta acción no se puede revertir.',
                icon:              opts.icon              ?? 'warning',
                showCancelButton:  true,
                confirmButtonText: opts.confirmButtonText ?? 'Sí, continuar',
                cancelButtonText:  opts.cancelButtonText  ?? 'Cancelar',
                reverseButtons:    true
            }).then((result) => {
                if (result.isConfirmed) {
                    if (opts.formId) {
                        document.getElementById(opts.formId).submit();
                    } else {
                        button.closest('form').submit();
                    }
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>