<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Cliente;

class ClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver-cliente',    ['only' => ['index', 'show', 'consultar']]);
        $this->middleware('permission:crear-cliente',  ['only' => ['store']]);
        $this->middleware('permission:editar-cliente', ['only' => ['update']]);
        $this->middleware('permission:borrar-cliente', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->filled('buscar')) {
            $term = '%' . $request->buscar . '%';
            $query->where(function ($q) use ($term) {
                $q->where('codigo_cliente',        'like', $term)
                  ->orWhere('nombres_clientes',      'like', $term)
                  ->orWhere('apellidos_clientes',  'like', $term)
                  ->orWhere('identificacion_clientes', 'like', $term)
                  ->orWhere('razon_social',         'like', $term)
                  ->orWhere('email_cliente',        'like', $term)
                  ->orWhere('celular_clientes',     'like', $term);
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $orden    = $request->input('orden', 'desc');
        $clientes = $query->orderBy('created_at', $orden === 'asc' ? 'asc' : 'desc')->paginate(15)->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function show(Cliente $cliente)
    {
        $trabajos = $cliente->trabajos()
            ->with(['servicio', 'cuentaCobrar', 'subtrabajos'])
            ->orderByDesc('created_at')
            ->get();

        $totalFacturado = $trabajos->sum('monto_total');
        $totalPagado    = $trabajos->sum(fn($t) => optional($t->cuentaCobrar)->monto_pagado ?? 0);
        $saldoPendiente = $totalFacturado - $totalPagado;

        $contadores = [
            'pendiente'  => $trabajos->where('estado_trabajo', 'pendiente')->count(),
            'proceso'    => $trabajos->where('estado_trabajo', 'proceso')->count(),
            'terminado'  => $trabajos->where('estado_trabajo', 'terminado')->count(),
            'cancelado'  => $trabajos->where('estado_trabajo', 'cancelado')->count(),
        ];

        return view('clientes.show', compact(
            'cliente', 'trabajos', 'totalFacturado', 'totalPagado', 'saldoPendiente', 'contadores'
        ));
    }

    public function consultar(string $identificacion)
    {
        $identificacion = preg_replace('/\D/', '', $identificacion);
        $longitud       = strlen($identificacion);

        if (!in_array($longitud, [10, 13])) {
            return response()->json(['error' => 'La identificación debe tener 10 (cédula) o 13 dígitos (RUC).'], 422);
        }

        $usuario = config('app.lr_api_usuario');
        $token   = config('app.lr_api_token');
        $baseUrl = config('app.lr_api_url');

        try {
            if ($longitud === 10) {
                // Cédula
                $response = Http::timeout(10)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post("{$baseUrl}/ConsultasCedula", [
                        'usuario' => $usuario,
                        'token'   => $token,
                        'ruc'     => $identificacion,
                    ]);
            } else {
                // RUC
                $response = Http::timeout(10)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post("{$baseUrl}/ConsultasSRI", [
                        'usuario' => $usuario,
                        'token'   => $token,
                        'ruc'     => $identificacion,
                    ]);
            }

            if (!$response->successful()) {
                return response()->json(['error' => 'Error al consultar el servicio externo.'], 502);
            }

            $body = $response->json();

            if (empty($body['resultado']['resultado'])) {
                return response()->json(['error' => $body['resultado']['mensaje'] ?? 'Sin resultados.'], 404);
            }

            $datos = $body['datos'] ?? [];

            return response()->json($this->parsearDatos($datos, $longitud));

        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo conectar con el servicio de consulta.'], 503);
        }
    }

    private function parsearDatos(array $datos, int $longitud): array
    {
        if ($longitud === 10) {
            // Respuesta de cédula
            $nombreCompleto = strtoupper(trim($datos['Nombre'] ?? ''));
            [$apellidos, $nombres] = $this->dividirNombre($nombreCompleto);

            return [
                'nombres_clientes'   => $nombres,
                'apellidos_clientes' => $apellidos,
                'razon_social'       => '',
                'tipo'               => 'cedula',
            ];
        }

        // Respuesta de RUC
        $razonSocial = strtoupper(trim($datos['Razon_social'] ?? ''));
        $tipo        = strtoupper($datos['TipoContribuyente'] ?? '');

        if (str_contains($tipo, 'PERSONA NATURAL')) {
            [$apellidos, $nombres] = $this->dividirNombre($razonSocial);
            return [
                'nombres_clientes'   => $nombres,
                'apellidos_clientes' => $apellidos,
                'razon_social'       => '',
                'tipo'               => 'ruc_natural',
            ];
        }

        return [
            'nombres_clientes'   => '',
            'apellidos_clientes' => '',
            'razon_social'       => $razonSocial,
            'tipo'               => 'ruc_juridica',
        ];
    }

    /**
     * Divide "APELLIDO1 APELLIDO2 NOMBRE1 NOMBRE2" en [apellidos, nombres].
     * Formato ecuatoriano estándar: primeras 2 palabras = apellidos, resto = nombres.
     */
    private function dividirNombre(string $nombre): array
    {
        $partes = array_filter(explode(' ', $nombre));
        $partes = array_values($partes);
        $total  = count($partes);

        if ($total === 0) return ['', ''];
        if ($total <= 2) return [implode(' ', $partes), ''];

        $apellidos = implode(' ', array_slice($partes, 0, 2));
        $nombres   = implode(' ', array_slice($partes, 2));

        return [$apellidos, $nombres];
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombres_clientes'       => 'required|string|max:150',
            'apellidos_clientes'     => 'required|string|max:150',
            'razon_social'           => 'nullable|string|max:200',
            'identificacion_clientes'=> 'required|string|max:20|unique:clientes,identificacion_clientes',
            'email_cliente'          => 'nullable|email|max:150',
            'celular_clientes'       => 'nullable|string|max:15',
            'estado'                 => 'required|in:Activo,Inactivo',
            'claves_observaciones'   => 'nullable|string',
        ], [
            'nombres_clientes.required'        => 'El nombre es obligatorio.',
            'apellidos_clientes.required'      => 'El apellido es obligatorio.',
            'identificacion_clientes.required' => 'La identificación es obligatoria.',
            'identificacion_clientes.unique'   => 'Esta identificación ya está registrada.',
            'email_cliente.email'              => 'El correo no es válido.',
        ]);

        $cliente = Cliente::create($request->all());

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.')
            ->with('nuevo_cliente_id', $cliente->id)
            ->with('nuevo_cliente_nombre', $cliente->nombre_completo);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombres_clientes'       => 'required|string|max:150',
            'apellidos_clientes'     => 'required|string|max:150',
            'razon_social'           => 'nullable|string|max:200',
            'identificacion_clientes'=> 'required|string|max:20|unique:clientes,identificacion_clientes,' . $cliente->id,
            'email_cliente'          => 'nullable|email|max:150',
            'celular_clientes'       => 'nullable|string|max:15',
            'estado'                 => 'required|in:Activo,Inactivo',
            'claves_observaciones'   => 'nullable|string',
        ], [
            'identificacion_clientes.unique' => 'Esta identificación ya está registrada.',
            'email_cliente.email'            => 'El correo no es válido.',
        ]);

        $cliente->update($request->except('codigo_cliente'));

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    }
}
