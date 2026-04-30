<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Propiedad;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $query = Propiedad::with('portada')
            ->where('estado', 'disponible')
            ->latest();

        if ($request->filled('tipo')) {
            $query->where('tipo_propiedad', $request->tipo);
        }

        if ($request->filled('ciudad')) {
            $query->where('ciudad', 'like', '%' . $request->ciudad . '%');
        }

        if ($request->filled('precio_max')) {
            $query->where('precio_estimado', '<=', $request->precio_max);
        }

        if ($request->filled('precio_min')) {
            $query->where('precio_estimado', '>=', $request->precio_min);
        }

        $propiedades = $query->paginate(12)->withQueryString();

        $ciudades = Propiedad::where('estado', 'disponible')
            ->distinct()
            ->orderBy('ciudad')
            ->pluck('ciudad');

        return view('catalogo.index', compact('propiedades', 'ciudades'));
    }

    public function show(Propiedad $propiedad)
    {
        if (! in_array($propiedad->estado, ['disponible', 'en_proceso'])) {
            abort(404);
        }

        $propiedad->load('fotos');

        return view('catalogo.show', compact('propiedad'));
    }
}
