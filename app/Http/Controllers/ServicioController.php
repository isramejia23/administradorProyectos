<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Departamento;

class ServicioController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-servicio',    ['only' => ['index']]);
        $this->middleware('permission:crear-servicio',  ['only' => ['store']]);
        $this->middleware('permission:editar-servicio', ['only' => ['update']]);
        $this->middleware('permission:borrar-servicio', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = Servicio::with('departamento');

        if ($request->filled('buscar')) {
            $query->where('nombre_servicio', 'like', '%' . $request->buscar . '%');
        }

        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->departamento_id);
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $servicios     = $query->orderBy('nombre_servicio')->paginate(15)->withQueryString();
        $departamentos = Departamento::orderBy('nombre_departamento')->pluck('nombre_departamento', 'id');

        return view('servicios.index', compact('servicios', 'departamentos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_servicio'  => 'required|string|max:150',
            'departamento_id'  => 'required|exists:departamentos,id',
            'precio_sugerido'  => 'required|numeric|min:0',
            'estatus'          => 'required|in:Activo,Inactivo',
        ], [
            'nombre_servicio.required' => 'El nombre del servicio es obligatorio.',
            'departamento_id.required' => 'El departamento es obligatorio.',
            'precio_sugerido.required' => 'El precio es obligatorio.',
            'precio_sugerido.numeric'  => 'El precio debe ser un número.',
        ]);

        Servicio::create($request->only('nombre_servicio', 'departamento_id', 'precio_sugerido', 'estatus'));

        return redirect()->route('servicios.index')->with('success', 'Servicio creado exitosamente.');
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'nombre_servicio'  => 'required|string|max:150',
            'departamento_id'  => 'required|exists:departamentos,id',
            'precio_sugerido'  => 'required|numeric|min:0',
            'estatus'          => 'required|in:Activo,Inactivo',
        ]);

        $servicio->update($request->only('nombre_servicio', 'departamento_id', 'precio_sugerido', 'estatus'));

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado correctamente.');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();
        return redirect()->route('servicios.index')->with('success', 'Servicio eliminado exitosamente.');
    }
}
