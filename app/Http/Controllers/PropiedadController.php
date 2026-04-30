<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Propiedad;
use App\Models\PropiedadFoto;
use App\Models\Cliente;

class PropiedadController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-propiedad',            ['only' => ['index', 'show']]);
        $this->middleware('permission:crear-propiedad',          ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-propiedad',         ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-propiedad',         ['only' => ['destroy']]);
        $this->middleware('permission:editar-propiedad',         ['only' => ['destroyFoto', 'setPortada']]);
    }

    public function index(Request $request)
    {
        $query = Propiedad::with('portada')->latest();

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->where(function ($q) use ($term) {
                $q->where('titulo',   'like', $term)
                  ->orWhere('ciudad', 'like', $term)
                  ->orWhere('sector', 'like', $term)
                  ->orWhere('nombre_dueno', 'like', $term);
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo_propiedad', $request->tipo);
        }

        if ($request->filled('ciudad')) {
            $query->where('ciudad', 'like', '%' . $request->ciudad . '%');
        }

        if ($request->filled('precio_min')) {
            $query->where('precio_estimado', '>=', $request->precio_min);
        }

        if ($request->filled('precio_max')) {
            $query->where('precio_estimado', '<=', $request->precio_max);
        }

        $propiedades = $query->paginate(12)->withQueryString();

        return view('propiedades.index', compact('propiedades'));
    }

    public function create()
    {
        $clientes  = Cliente::orderBy('nombres_clientes')->get();
        $usuarios  = \App\Models\User::orderBy('nombre')->get();
        return view('propiedades.create', compact('clientes', 'usuarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo'              => 'required|string|max:200',
            'tipo_propiedad'      => 'required|in:casa,departamento,terreno,local_comercial,oficina',
            'precio_estimado'     => 'required|numeric|min:0',
            'negociable'          => 'boolean',
            'estado'              => 'required|in:disponible,en_proceso,vendido,cancelado',
            'ciudad'              => 'required|string|max:100',
            'sector'              => 'nullable|string|max:150',
            'direccion'           => 'required|string|max:250',
            'metros_terreno'      => 'nullable|numeric|min:0',
            'metros_construccion' => 'nullable|numeric|min:0',
            'numero_habitaciones' => 'nullable|integer|min:0|max:99',
            'numero_banos'        => 'nullable|integer|min:0|max:99',
            'parqueaderos'        => 'nullable|integer|min:0|max:99',
            'nombre_dueno'        => 'required|string|max:200',
            'telefono_dueno'      => 'required|string|max:20',
            'descripcion'         => 'nullable|string',
            'numero_propietarios' => 'nullable|integer|min:1|max:99',
            'captador_id'         => 'nullable|exists:users,id',
            'cliente_id'          => 'nullable|exists:clientes,id',
            'fotos'               => 'nullable|array|max:5',
            'fotos.*'             => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'portada_index'       => 'nullable|integer|min:0',
        ]);

        $data['negociable'] = $request->boolean('negociable');

        $propiedad = Propiedad::create($data);

        $this->guardarFotos($propiedad, $request);

        return redirect()->route('propiedades.show', $propiedad)
                         ->with('success', 'Propiedad registrada exitosamente.');
    }

    public function show(Propiedad $propiedad)
    {
        $propiedad->load(['fotos', 'cliente']);
        return view('propiedades.show', compact('propiedad'));
    }

    public function edit(Propiedad $propiedad)
    {
        $propiedad->load('fotos');
        $clientes = Cliente::orderBy('nombres_clientes')->get();
        $usuarios = \App\Models\User::orderBy('nombre')->get();
        return view('propiedades.edit', compact('propiedad', 'clientes', 'usuarios'));
    }

    public function update(Request $request, Propiedad $propiedad)
    {
        $data = $request->validate([
            'titulo'              => 'required|string|max:200',
            'tipo_propiedad'      => 'required|in:casa,departamento,terreno,local_comercial,oficina',
            'precio_estimado'     => 'required|numeric|min:0',
            'negociable'          => 'boolean',
            'estado'              => 'required|in:disponible,en_proceso,vendido,cancelado',
            'ciudad'              => 'required|string|max:100',
            'sector'              => 'nullable|string|max:150',
            'direccion'           => 'required|string|max:250',
            'metros_terreno'      => 'nullable|numeric|min:0',
            'metros_construccion' => 'nullable|numeric|min:0',
            'numero_habitaciones' => 'nullable|integer|min:0|max:99',
            'numero_banos'        => 'nullable|integer|min:0|max:99',
            'parqueaderos'        => 'nullable|integer|min:0|max:99',
            'nombre_dueno'        => 'required|string|max:200',
            'telefono_dueno'      => 'required|string|max:20',
            'descripcion'         => 'nullable|string',
            'numero_propietarios' => 'nullable|integer|min:1|max:99',
            'captador_id'         => 'nullable|exists:users,id',
            'cliente_id'          => 'nullable|exists:clientes,id',
            'fotos'               => 'nullable|array',
            'fotos.*'             => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'portada_index'       => 'nullable|integer|min:0',
        ]);

        $data['negociable'] = $request->boolean('negociable');

        $propiedad->update($data);

        $slots = 5 - $propiedad->fotos()->count();
        if ($slots > 0) {
            $this->guardarFotos($propiedad, $request, $slots);
        }

        return redirect()->route('propiedades.show', $propiedad)
                         ->with('success', 'Propiedad actualizada correctamente.');
    }

    public function destroy(Propiedad $propiedad)
    {
        foreach ($propiedad->fotos as $foto) {
            Storage::disk('public')->delete($foto->ruta);
        }
        $propiedad->delete();

        return redirect()->route('propiedades.index')
                         ->with('success', 'Propiedad eliminada.');
    }

    public function destroyFoto(Propiedad $propiedad, PropiedadFoto $foto)
    {
        Storage::disk('public')->delete($foto->ruta);
        $wasPortada = $foto->es_portada;
        $foto->delete();

        if ($wasPortada) {
            $primera = $propiedad->fotos()->orderBy('orden')->first();
            $primera?->update(['es_portada' => true]);
        }

        return back()->with('success', 'Foto eliminada.');
    }

    public function setPortada(Propiedad $propiedad, PropiedadFoto $foto)
    {
        $propiedad->fotos()->update(['es_portada' => false]);
        $foto->update(['es_portada' => true]);

        return back()->with('success', 'Foto de portada actualizada.');
    }

    private function guardarFotos(Propiedad $propiedad, Request $request, int $maxSlots = 5): void
    {
        if (! $request->hasFile('fotos')) {
            return;
        }

        $portadaIndex = (int) $request->input('portada_index', 0);
        $yaHayPortada = $propiedad->fotos()->where('es_portada', true)->exists();
        $orden        = $propiedad->fotos()->max('orden') ?? -1;

        foreach ($request->file('fotos') as $index => $archivo) {
            if ($index >= $maxSlots) break;

            $ruta = $archivo->store('propiedades', 'public');
            $orden++;

            $esPortada = !$yaHayPortada && $index === $portadaIndex;
            if ($esPortada) {
                $yaHayPortada = true;
            }

            $propiedad->fotos()->create([
                'ruta'       => $ruta,
                'es_portada' => $esPortada,
                'orden'      => $orden,
            ]);
        }

        // Si no se marcó ninguna como portada, la primera pasa a serlo
        if (! $propiedad->fotos()->where('es_portada', true)->exists()) {
            $propiedad->fotos()->orderBy('orden')->first()?->update(['es_portada' => true]);
        }
    }
}
