<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso — Grupo Piguma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --red:  #C8102E;
            --red2: #a80d24;
            --dark: #1a1a1a;
            --gray: #58595B;
        }

        html, body {
            height: 100%; margin: 0; padding: 0;
            font-family: 'Inter', sans-serif;
        }

        .login-wrap {
            display: flex;
            min-height: 100vh;
        }

        /* ─── PANEL IZQUIERDO ─── */
        .panel-brand {
            flex: 1;
            background: var(--dark);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 3.5rem;
            overflow: hidden;
        }

        /* Patrón de fondo sutil */
        .panel-brand::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 90% 70% at 30% 80%, rgba(200,16,46,.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 10%, rgba(200,16,46,.07) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Líneas decorativas edificio */
        .panel-brand::after {
            content: '';
            position: absolute;
            bottom: 0; right: 0;
            width: 280px; height: 280px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 200'%3E%3Crect x='20' y='40' width='16' height='160' fill='%23C8102E' opacity='.06'/%3E%3Crect x='44' y='20' width='16' height='180' fill='%23C8102E' opacity='.06'/%3E%3Crect x='68' y='55' width='16' height='145' fill='%23C8102E' opacity='.06'/%3E%3Crect x='100' y='10' width='22' height='190' fill='%23C8102E' opacity='.04'/%3E%3Crect x='132' y='30' width='22' height='170' fill='%23C8102E' opacity='.04'/%3E%3Crect x='164' y='50' width='22' height='150' fill='%23C8102E' opacity='.04'/%3E%3C/svg%3E") no-repeat center/cover;
            pointer-events: none;
        }

        .brand-content { position: relative; z-index: 1; text-align: center; }

        .brand-logo {
            width: 120px; height: 120px; object-fit: contain;
            margin-bottom: 1.75rem;
            filter: drop-shadow(0 4px 16px rgba(0,0,0,.4));
        }

        .brand-name {
            color: #fff; font-size: 1.6rem; font-weight: 800;
            letter-spacing: .01em; margin-bottom: .2rem;
        }

        .brand-tagline {
            color: #888; font-size: .8rem;
            letter-spacing: .14em; text-transform: uppercase;
            margin-bottom: 2.5rem;
        }

        .brand-divider {
            width: 40px; height: 3px;
            background: var(--red);
            margin: 0 auto 2.5rem;
            border-radius: 2px;
        }

        .brand-features { list-style: none; padding: 0; margin: 0; text-align: left; }
        .brand-features li {
            display: flex; align-items: center; gap: .75rem;
            color: #777; font-size: .82rem; margin-bottom: .85rem;
        }
        .brand-features li i {
            color: var(--red); font-size: 1rem;
            flex-shrink: 0; width: 18px; text-align: center;
        }

        .brand-copy {
            position: absolute; bottom: 1.5rem; left: 0; right: 0;
            text-align: center;
            color: #444; font-size: .68rem;
        }

        /* ─── PANEL DERECHO ─── */
        .panel-form {
            width: 460px;
            background: #f5f5f7;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.75rem;
        }

        .form-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.25rem 2rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }

        .form-eyebrow {
            font-size: .68rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .1em;
            color: var(--red); margin-bottom: .4rem;
        }

        .form-heading {
            font-size: 1.5rem; font-weight: 800;
            color: var(--dark); margin-bottom: .3rem; line-height: 1.2;
        }

        .form-sub {
            font-size: .8rem; color: #aaa; margin-bottom: 1.75rem;
        }

        .form-group-label {
            font-size: .75rem; font-weight: 600; color: #555; margin-bottom: 5px; display: block;
        }

        .form-input {
            height: 44px;
            border-radius: 8px;
            border: 1.5px solid #e5e5e5;
            font-size: .88rem;
            padding: 0 .85rem 0 2.5rem;
            width: 100%; outline: none;
            background: #fafafa;
            transition: border-color .15s, box-shadow .15s, background .15s;
            color: var(--dark);
        }
        .form-input:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(200,16,46,.1);
            background: #fff;
        }
        .form-input.is-invalid { border-color: #dc3545; }
        .form-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(220,53,69,.1); }

        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
            color: #bbb; font-size: .95rem; pointer-events: none;
        }
        .input-toggle {
            position: absolute; right: .75rem; top: 50%; transform: translateY(-50%);
            color: #bbb; font-size: .95rem; cursor: pointer; border: none; background: none;
            padding: 0; transition: color .15s;
        }
        .input-toggle:hover { color: var(--gray); }

        .invalid-msg {
            font-size: .74rem; color: #dc3545; margin-top: 4px; display: block;
        }

        .remember-row {
            display: flex; align-items: center; justify-content: space-between;
            margin: 1rem 0 1.5rem;
        }
        .form-check-input:checked { background-color: var(--red); border-color: var(--red); }
        .form-check-label { font-size: .8rem; color: #666; }

        .btn-login {
            width: 100%; height: 46px;
            background: var(--red); border: none; color: #fff;
            font-weight: 700; font-size: .92rem; border-radius: 8px;
            cursor: pointer; transition: background .15s, transform .1s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { background: var(--red2); transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .form-footer {
            text-align: center; margin-top: 1.25rem;
            font-size: .75rem; color: #bbb;
        }

        /* Alerta de error general */
        .login-alert {
            background: #fff2f3; border: 1px solid #f5c6cb;
            border-radius: 8px; padding: .65rem .9rem;
            font-size: .8rem; color: #842029;
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 1.25rem;
        }

        /* Responsive: en móvil oculta el panel izquierdo */
        @media (max-width: 768px) {
            .panel-brand { display: none; }
            .panel-form  { width: 100%; padding: 2rem 1.5rem; background: #f5f5f7; }
        }
    </style>
</head>
<body>

<div class="login-wrap">

    {{-- ─── PANEL IZQUIERDO — MARCA ─── --}}
    <div class="panel-brand">
        <div class="brand-content">

            @if(file_exists(public_path('imagenes/logo-piguma.jpeg')))
                <img src="{{ asset('imagenes/logo-piguma.jpeg') }}"
                     alt="Grupo Piguma" class="brand-logo">
            @endif

            <div class="brand-name">Grupo Piguma</div>
            <div class="brand-tagline">Centro de Negocios</div>
            <div class="brand-divider"></div>

            <ul class="brand-features">
                <li><i class="bi bi-graph-up-arrow"></i>Gestión de proyectos en tiempo real</li>
                <li><i class="bi bi-building"></i>Control de propiedades y catálogo</li>
                <li><i class="bi bi-people-fill"></i>Administración de equipos y departamentos</li>
                <li><i class="bi bi-shield-check"></i>Acceso seguro para tu organización</li>
            </ul>

        </div>
        <div class="brand-copy">&copy; {{ date('Y') }} Grupo Piguma</div>
    </div>

    {{-- ─── PANEL DERECHO — FORMULARIO ─── --}}
    <div class="panel-form">
        <div class="form-card">

            <div class="form-eyebrow">Bienvenido de vuelta</div>
            <h1 class="form-heading">Inicia sesión</h1>
            <p class="form-sub">Ingresa tus credenciales para acceder al sistema.</p>

            {{-- Alerta de errores --}}
            @if($errors->any())
            <div class="login-alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-group-label">Correo electrónico</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope input-icon"></i>
                        <input id="email"
                               type="email"
                               name="email"
                               class="form-input @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               required
                               autocomplete="email"
                               autofocus
                               placeholder="correo@empresa.com">
                    </div>
                    @error('email')
                        <span class="invalid-msg">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="mb-1">
                    <label for="password" class="form-group-label">Contraseña</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon"></i>
                        <input id="password"
                               type="password"
                               name="password"
                               class="form-input @error('password') is-invalid @enderror"
                               required
                               autocomplete="current-password"
                               placeholder="••••••••"
                               style="padding-right:2.5rem;">
                        <button type="button" class="input-toggle" onclick="togglePassword()" id="toggle-btn">
                            <i class="bi bi-eye" id="toggle-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-msg">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Recuérdame --}}
                <div class="remember-row">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               name="remember" id="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Recuérdame</label>
                    </div>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}"
                           style="font-size:.78rem;color:var(--red);text-decoration:none;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Ingresar al sistema
                </button>

            </form>

            <div class="form-footer">
                Sistema de gestión interno &mdash; Grupo Piguma
            </div>

        </div>
    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('toggle-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>

</body>
</html>
