<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jsonPath = storage_path('app/clientes_excel.json');
$jsonRaw  = file_get_contents($jsonPath);
// Eliminar BOM UTF-8 si existe
$jsonRaw  = ltrim($jsonRaw, "\xEF\xBB\xBF");
$clientes = json_decode($jsonRaw, true);

$baseUrl = config('app.lr_api_url');
$usuario = config('app.lr_api_usuario');
$token   = config('app.lr_api_token');

$inserts = [];
$errores = [];
$total   = count($clientes);
$i       = 0;

foreach ($clientes as $row) {
    $i++;
    $cedula  = $row['cedula'];
    $celular = $row['celular'];
    $ciudad  = $row['ciudad'];
    $tipo    = strlen($cedula) === 10 ? 'cedula' : 'ruc';
    $endpoint = $tipo === 'cedula' ? 'ConsultasCedula' : 'ConsultasSRI';

    echo "[$i/$total] Consultando $cedula... ";

    try {
        $response = Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$baseUrl}/{$endpoint}", [
                'usuario' => $usuario,
                'token'   => $token,
                'ruc'     => $cedula,
            ]);

        if (!$response->successful()) {
            echo "ERROR HTTP " . $response->status() . "\n";
            $errores[] = $cedula;
            continue;
        }

        $body = $response->json();

        if (empty($body['resultado']['resultado'])) {
            echo "SIN RESULTADO\n";
            $errores[] = $cedula;
            continue;
        }

        $datos = $body['datos'] ?? [];

        if ($tipo === 'cedula') {
            $nombreCompleto = strtoupper(trim($datos['Nombre'] ?? ''));
        } else {
            $nombreCompleto = strtoupper(trim($datos['Razon_social'] ?? ''));
        }

        $partes    = array_values(array_filter(explode(' ', $nombreCompleto)));
        $total_p   = count($partes);
        if ($total_p === 0) {
            $apellidos = '';
            $nombres   = '';
        } elseif ($total_p <= 2) {
            $apellidos = implode(' ', $partes);
            $nombres   = '';
        } else {
            $apellidos = implode(' ', array_slice($partes, 0, 2));
            $nombres   = implode(' ', array_slice($partes, 2));
        }

        $esc = fn($s) => str_replace("'", "''", $s);

        $inserts[] = sprintf(
            "INSERT INTO clientes (nombres_clientes, apellidos_clientes, razon_social, identificacion_clientes, celular_clientes, email_cliente, estado, created_at, updated_at) VALUES ('%s', '%s', '', '%s', '%s', '', 'Activo', NOW(), NOW());",
            $esc($nombres),
            $esc($apellidos),
            $esc($cedula),
            $esc($celular)
        );

        echo "OK — $apellidos $nombres\n";

    } catch (Exception $e) {
        echo "EXCEPCION: " . $e->getMessage() . "\n";
        $errores[] = $cedula;
    }
}

// Escribir el archivo SQL
$sqlPath = __DIR__ . '/storage/app/clientes_insert.sql';
$sql  = "-- Clientes generados desde Cartera Matriz -- " . date('Y-m-d H:i:s') . "\n";
$sql .= "-- Total: " . count($inserts) . " registros\n\n";
$sql .= implode("\n", $inserts) . "\n";
file_put_contents($sqlPath, $sql);

echo "\n--- RESUMEN ---\n";
echo "OK:     " . count($inserts) . "\n";
echo "Errores: " . count($errores) . "\n";
if ($errores) {
    echo "Cédulas sin resultado:\n";
    foreach ($errores as $c) echo "  $c\n";
}
echo "\nSQL guardado en: storage/app/clientes_insert.sql\n";
