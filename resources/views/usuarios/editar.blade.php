@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-0" style="font-size:1.15rem;">
                <i class="bi bi-person-gear me-2" style="color:#0d6efd;"></i>Editar Usuario
            </h4>
            <p class="text-muted mb-0" style="font-size:.78rem;">
                {{ $user->nombre }} {{ $user->apellido }} &mdash; {{ $user->email }}
            </p>
        </div>
        <a href="{{ route('usuarios.index') }}"
           class="btn btn-sm btn-outline-secondary px-3">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    {{-- Errores --}}
    @if($errors->any())
    <div class="alert alert-danger d-flex gap-2 align-items-start mb-4 py-2 px-3" style="font-size:.83rem;border-radius:8px;">
        <i class="bi bi-exclamation-circle-fill mt-1 flex-shrink-0"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
    @endif

    <form action="{{ route('usuarios.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ── Columna izquierda ── --}}
            <div class="col-lg-8">

                {{-- Información Personal --}}
                <div class="bg-white rounded shadow-sm p-4 mb-4">
                    <p class="mb-3" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:#888;">
                        <i class="bi bi-person me-1"></i>Información Personal
                    </p>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $user->nombre) }}"
                                   required>
                            @error('nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">
                                Apellido <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="apellido"
                                   class="form-control @error('apellido') is-invalid @enderror"
                                   value="{{ old('apellido', $user->apellido) }}"
                                   required>
                            @error('apellido')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">
                                Cédula <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="cedula"
                                   id="cedula"
                                   class="form-control @error('cedula') is-invalid @enderror"
                                   value="{{ old('cedula', $user->cedula) }}"
                                   inputmode="numeric"
                                   maxlength="13"
                                   required>
                            @error('cedula')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">
                                Teléfono <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="telefono"
                                   id="telefono"
                                   class="form-control @error('telefono') is-invalid @enderror"
                                   value="{{ old('telefono', $user->telefono) }}"
                                   inputmode="numeric"
                                   maxlength="10"
                                   required>
                            @error('telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">
                                Dirección <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="direccion"
                                   class="form-control @error('direccion') is-invalid @enderror"
                                   value="{{ old('direccion', $user->direccion) }}"
                                   required>
                            @error('direccion')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Acceso al Sistema --}}
                <div class="bg-white rounded shadow-sm p-4">
                    <p class="mb-3" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:#888;">
                        <i class="bi bi-shield-lock me-1"></i>Acceso al Sistema
                    </p>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">
                                Correo electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-sm-6">
                            {{-- Espaciador para alinear la fila --}}
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">
                                Nueva contraseña
                                <span class="text-muted fw-normal" style="font-size:.72rem;">(dejar vacío para no cambiar)</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Mínimo 8 caracteres">
                                <button type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="togglePass('password','pass-icon')"
                                        tabindex="-1"
                                        style="border-left:0;">
                                    <i class="bi bi-eye" id="pass-icon" style="font-size:.9rem;"></i>
                                </button>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label class="form-label fw-semibold" style="font-size:.8rem;">
                                Confirmar contraseña
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       name="confirm-password"
                                       id="confirm-password"
                                       class="form-control"
                                       placeholder="Repetir contraseña">
                                <button type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="togglePass('confirm-password','confirm-icon')"
                                        tabindex="-1"
                                        style="border-left:0;">
                                    <i class="bi bi-eye" id="confirm-icon" style="font-size:.9rem;"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            {{-- ── Columna derecha ── --}}
            <div class="col-lg-4">
                <div class="bg-white rounded shadow-sm p-4">
                    <p class="mb-3" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.09em;color:#888;">
                        <i class="bi bi-sliders me-1"></i>Asignación y Estado
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.8rem;">
                            Departamento
                        </label>
                        <select name="departamento_id"
                                class="form-select @error('departamento_id') is-invalid @enderror">
                            <option value="">— Sin departamento —</option>
                            @foreach($departamentos as $dId => $nombre)
                                <option value="{{ $dId }}"
                                    {{ old('departamento_id', $user->departamento_id) == $dId ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('departamento_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.8rem;">
                            Rol <span class="text-danger">*</span>
                        </label>
                        <select name="roles"
                                class="form-select @error('roles') is-invalid @enderror"
                                required>
                            <option value="">— Seleccione un rol —</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}"
                                    {{ in_array($role, $userRole) ? 'selected' : '' }}>
                                    {{ $role }}
                                </option>
                            @endforeach
                        </select>
                        @error('roles')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:.8rem;">
                            Estado <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex gap-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                       name="estado" id="activo" value="Activo"
                                       {{ old('estado', $user->estado) === 'Activo' ? 'checked' : '' }}>
                                <label class="form-check-label" for="activo" style="font-size:.85rem;">
                                    <span class="badge bg-success">Activo</span>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio"
                                       name="estado" id="inactivo" value="Inactivo"
                                       {{ old('estado', $user->estado) === 'Inactivo' ? 'checked' : '' }}>
                                <label class="form-check-label" for="inactivo" style="font-size:.85rem;">
                                    <span class="badge bg-secondary">Inactivo</span>
                                </label>
                            </div>
                        </div>
                        @error('estado')<div class="text-danger" style="font-size:.75rem;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Acciones --}}
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy-fill me-1"></i>Guardar Cambios
                        </button>
                        <a href="{{ route('usuarios.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-1"></i>Cancelar
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
    // Solo dígitos en cédula y teléfono
    ['cedula', 'telefono'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
        document.getElementById(id).addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key)) e.preventDefault();
        });
    });

    // Mostrar/ocultar contraseña
    function togglePass(inputId, iconId) {
        var input = document.getElementById(inputId);
        var icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>
@endpush
