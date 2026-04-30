<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;

class DepartamentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-departamento',    ['only' => ['index', 'show']]);
        $this->middleware('permission:crear-departamento',  ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-departamento', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-departamento', ['only' => ['destroy']]);
    }

    public function index()
    {
        $departamentos = Departamento::withCount([
            'usuarios',
            'trabajos as proyectos_count'     => fn($q) => $q->whereNotIn('estado_trabajo', ['cancelado']),
            'subtrabajos as subtrabajos_count' => fn($q) => $q->where('es_principal', false)
                                                               ->whereNotIn('estado', ['cancelado']),
        ])
        ->orderBy('nombre_departamento')
        ->paginate(15);

        return view('departamentos.index', compact('departamentos'));
    }

    public function show(Departamento $departamento)
    {
        $usuarios = $departamento->usuarios()
            ->with(['roles'])
            ->withCount([
                'trabajosResponsable as proyectos_total'     => fn($q) => $q->whereNotIn('estado_trabajo', ['solicitud', 'rechazado']),
                'trabajosResponsable as proyectos_pendiente' => fn($q) => $q->where('estado_trabajo', 'pendiente'),
                'trabajosResponsable as proyectos_proceso'   => fn($q) => $q->where('estado_trabajo', 'proceso'),
                'trabajosResponsable as proyectos_terminado' => fn($q) => $q->where('estado_trabajo', 'terminado'),
            ])
            ->orderBy('nombre')
            ->get();

        $stats = [
            'total'     => $departamento->trabajos()->whereNotIn('estado_trabajo', ['solicitud', 'rechazado'])->count(),
            'pendiente' => $departamento->trabajos()->where('estado_trabajo', 'pendiente')->count(),
            'proceso'   => $departamento->trabajos()->where('estado_trabajo', 'proceso')->count(),
            'terminado' => $departamento->trabajos()->where('estado_trabajo', 'terminado')->count(),
            'cancelado' => $departamento->trabajos()->where('estado_trabajo', 'cancelado')->count(),
        ];

        return view('departamentos.show', compact('departamento', 'usuarios', 'stats'));
    }

    public function create()
    {
        return view('departamentos.crear');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_departamento' => 'required|string|max:100|unique:departamentos,nombre_departamento',
        ], [
            'nombre_departamento.required' => 'El nombre del departamento es obligatorio.',
            'nombre_departamento.unique'   => 'Ya existe un departamento con ese nombre.',
        ]);

        Departamento::create($request->only('nombre_departamento'));

        return redirect()->route('departamentos.index')->with('success', 'Departamento creado exitosamente.');
    }

    public function edit(Departamento $departamento)
    {
        return view('departamentos.editar', compact('departamento'));
    }

    public function update(Request $request, Departamento $departamento)
    {
        $request->validate([
            'nombre_departamento' => 'required|string|max:100|unique:departamentos,nombre_departamento,' . $departamento->id,
        ], [
            'nombre_departamento.required' => 'El nombre del departamento es obligatorio.',
            'nombre_departamento.unique'   => 'Ya existe un departamento con ese nombre.',
        ]);

        $departamento->update($request->only('nombre_departamento'));

        return redirect()->route('departamentos.index')->with('success', 'Departamento actualizado correctamente.');
    }

    public function destroy(Departamento $departamento)
    {
        $departamento->delete();
        return redirect()->route('departamentos.index')->with('success', 'Departamento eliminado exitosamente.');
    }
}
