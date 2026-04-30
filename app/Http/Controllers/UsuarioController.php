<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Departamento;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-usuario',    ['only' => ['index', 'show']]);
        $this->middleware('permission:crear-usuario',  ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-usuario', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-usuario', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = User::with(['departamento', 'roles']);

        // Búsqueda libre: nombre, apellido, email, cédula, teléfono
        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre',   'like', $term)
                  ->orWhere('apellido', 'like', $term)
                  ->orWhere('email',    'like', $term)
                  ->orWhere('cedula',   'like', $term)
                  ->orWhere('telefono', 'like', $term);
            });
        }

        // Filtro por departamento
        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        // Filtro por rol
        if ($request->filled('rol')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->rol));
        }

        $usuarios      = $query->paginate(15)->withQueryString();
        $departamentos = Departamento::orderBy('nombre_departamento')->pluck('nombre_departamento', 'id');
        $roles         = Role::orderBy('name')->pluck('name', 'name');

        return view('usuarios.index', compact('usuarios', 'departamentos', 'roles'));
    }

    public function create()
    {
        $roles         = Role::pluck('name', 'name')->all();
        $departamentos = Departamento::orderBy('nombre_departamento')->pluck('nombre_departamento', 'id');
        return view('usuarios.crear', compact('roles', 'departamentos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'apellido'        => 'required|string|max:150',
            'email'           => 'required|email|unique:users,email',
            'cedula'          => 'required|string|max:13|unique:users,cedula',
            'telefono'        => 'required|string|max:10|min:10',
            'direccion'       => 'required|string|max:200',
            'estado'          => 'required|in:Activo,Inactivo',
            'roles'           => 'required',
            'password'        => 'required|string|min:8',
            'departamento_id' => 'nullable|exists:departamentos,id',
        ], [
            'email.unique'    => 'Este correo electrónico ya está registrado.',
            'cedula.unique'   => 'Esta cédula ya está registrada.',
            'telefono.min'    => 'El teléfono debe tener 10 caracteres.',
            'telefono.max'    => 'El teléfono debe tener 10 caracteres.',
            'roles.required'  => 'El rol es obligatorio.',
            'password.min'    => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $data             = $request->except('roles');
        $data['password'] = Hash::make($request->password);

        $user = User::create($data);
        $user->assignRole($request->input('roles'));

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function show(string $id)
    {
        $user = User::with(['departamento', 'roles'])->findOrFail($id);
        return view('usuarios.index', compact('user'));
    }

    public function edit($id)
    {
        $user          = User::findOrFail($id);
        $roles         = Role::pluck('name', 'name')->all();
        $userRole      = $user->roles->pluck('name', 'name')->all();
        $departamentos = Departamento::orderBy('nombre_departamento')->pluck('nombre_departamento', 'id');

        return view('usuarios.editar', compact('user', 'roles', 'userRole', 'departamentos'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'apellido'        => 'required|string|max:150',
            'email'           => 'required|email|unique:users,email,' . $id,
            'cedula'          => 'required|string|max:13|unique:users,cedula,' . $id,
            'telefono'        => 'required|string|max:10|min:10',
            'direccion'       => 'required|string|max:200',
            'estado'          => 'required|in:Activo,Inactivo',
            'roles'           => 'required',
            'password'        => 'nullable|string|min:8|same:confirm-password',
            'departamento_id' => 'nullable|exists:departamentos,id',
        ]);

        $data = $request->except(['roles', 'password', 'confirm-password']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user = User::findOrFail($id);
        $user->update($data);

        DB::table('model_has_roles')->where('model_id', $id)->delete();
        $user->assignRole($request->input('roles'));

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
