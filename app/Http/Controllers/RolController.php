<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class RolController extends Controller
{
    public function __construct()
    {
       //$this->middleware('auth');
       $this->middleware('permission:ver-rol|crear-rol|editar-rol|borrar-rol', ['only' => ['index']]);
       $this->middleware('permission:crear-rol', ['only' => ['create', 'store']]);
       $this->middleware('permission:editar-rol', ['only' => ['edit', 'update']]);
       $this->middleware('permission:borrar-rol', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $roles = Role::paginate(5);
            return view('roles.index', compact('roles'));
        } catch (Exception $e) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Error al cargar los roles: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $permission = Permission::all();
            return view('roles.crear', compact('permission'));
        } catch (Exception $e) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Error al cargar los permisos: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $messages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'permission.required' => 'El campo permiso es obligatorio.',
        ];

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'permission' => 'required|array',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->route('roles.create')->withErrors($validator)->withInput();
        }

        try {
            $role = Role::create(['name' => $request->input('name')]);

            $permissions = Permission::whereIn('id', $request->input('permission'))->pluck('id');
            $role->syncPermissions($permissions);

            return redirect()->route('roles.index')->with('success', 'Rol creado exitosamente.');
        } catch (Exception $e) {
            return redirect()->route('roles.create')->withErrors(['error' => 'Error al crear el rol: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $role = Role::findOrFail($id); // findOrFail lanzará una excepción si no se encuentra el rol
            $permission = Permission::all();
            $rolePermissions = DB::table('role_has_permissions')->where('role_id', $id)
                ->pluck('permission_id')
                ->all();

            return view('roles.editar', compact('role', 'permission', 'rolePermissions'));
        } catch (ModelNotFoundException $e) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Rol no encontrado.']);
        } catch (Exception $e) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Error al cargar el rol: ' . $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $messages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'permission.required' => 'El campo permiso es obligatorio.',
        ];

        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|max:255',
            'permission' => 'required|array',
        ], $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $role = Role::findOrFail($id);
            $role->name = $request->input('name');
            $role->save();

            $permissions = Permission::whereIn('id', $request->input('permission'))->get();

            // Verificar si todos los permisos existen
            if ($permissions->count() !== count($request->input('permission'))) {
                return back()->withErrors(['permissions' => 'Uno o más permisos no existen.']);
            }

            // Sincronizar los permisos con el rol
            $role->syncPermissions($permissions);

            return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Rol no encontrado.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Error al actualizar el rol: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id); // Asegúrate de que el rol existe antes de eliminarlo
            $role->delete();

            return redirect()->route('roles.index')->with('success', 'Rol eliminado exitosamente.');
        } catch (ModelNotFoundException $e) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Rol no encontrado para eliminar.']);
        } catch (Exception $e) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Error al eliminar el rol: ' . $e->getMessage()]);
        }
    }
}
