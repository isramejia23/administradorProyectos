<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// ── Cargar mapa cedula → codigo ───────────────────────────────────────────
$mapaRaw = json_decode(ltrim(file_get_contents(storage_path('app/mapa_codigos.json')), "\xEF\xBB\xBF"), true);

// Limpiar códigos: quitar espacios y anotaciones tipo "(1)", "(2)"
$mapa = [];
foreach ($mapaRaw as $cedula => $codigo) {
    $codigoLimpio = trim(preg_replace('/\s*\(\d+\).*$/', '', $codigo));
    $mapa[$cedula] = $codigoLimpio;
}

// ── Actualizar INSERTs existentes en el SQL ───────────────────────────────
$sqlPath    = storage_path('app/clientes_insert.sql');
$sqlContent = ltrim(file_get_contents($sqlPath), "\xEF\xBB\xBF");
$lineas     = explode("\n", $sqlContent);

$actualizadas = 0;
$sinCodigo    = 0;
$nuevasLineas = [];

foreach ($lineas as $linea) {
    if (!preg_match("/identificacion_clientes.*?'(\d{10,13})'/", $linea, $m)) {
        $nuevasLineas[] = $linea;
        continue;
    }

    $cedula = $m[1];

    // Ya tiene codigo_cliente en la línea → dejar igual
    if (str_contains($linea, 'codigo_cliente')) {
        $nuevasLineas[] = $linea;
        continue;
    }

    if (!isset($mapa[$cedula])) {
        // No hay código en el Excel para esta cédula → insertar NULL
        $sinCodigo++;
        $linea = str_replace(
            'INSERT INTO clientes (nombres_clientes,',
            'INSERT INTO clientes (codigo_cliente, nombres_clientes,',
            $linea
        );
        $linea = str_replace("VALUES ('", "VALUES (NULL, '", $linea);
        $nuevasLineas[] = $linea;
        continue;
    }

    $codigo = $mapa[$cedula];
    $linea  = str_replace(
        'INSERT INTO clientes (nombres_clientes,',
        'INSERT INTO clientes (codigo_cliente, nombres_clientes,',
        $linea
    );
    $linea = str_replace("VALUES ('", "VALUES ('" . $codigo . "', '", $linea);
    $actualizadas++;
    $nuevasLineas[] = $linea;
}

echo "Inserts actualizados con codigo: $actualizadas\n";
echo "Inserts sin codigo en Excel (NULL): $sinCodigo\n";

// ── Consultar API para las cedulas nuevas ─────────────────────────────────
$nuevosRaw = json_decode(ltrim(file_get_contents(storage_path('app/clientes_nuevos2.json')), "\xEF\xBB\xBF"), true);

$baseUrl = config('app.lr_api_url');
$usuario = config('app.lr_api_usuario');
$token   = config('app.lr_api_token');

$insertNuevos = [];
$errores      = [];
$total        = count($nuevosRaw);
$i            = 0;

foreach ($nuevosRaw as $row) {
    $i++;
    $cedula   = $row['cedula'];
    $celular  = $row['celular'];
    $codigo   = isset($mapa[$cedula]) ? $mapa[$cedula] : null;
    $longitud = strlen($cedula);
    $endpoint = $longitud === 10 ? 'ConsultasCedula' : 'ConsultasSRI';

    echo "[$i/$total] Consultando $cedula (codigo: " . ($codigo ?? 'sin codigo') . ")... ";

    try {
        $response = Illuminate\Support\Facades\Http::timeout(10)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$baseUrl}/{$endpoint}", ['usuario' => $usuario, 'token' => $token, 'ruc' => $cedula]);

        if (!$response->successful()) {
            echo "ERROR " . $response->status() . "\n";
            $errores[] = $cedula;
            continue;
        }

        $body = $response->json();

        if (empty($body['resultado']['resultado'])) {
            echo "SIN RESULTADO\n";
            $errores[] = $cedula;
            continue;
        }

        $datos          = $body['datos'] ?? [];
        $nombreCompleto = strtoupper(trim($longitud === 10 ? ($datos['Nombre'] ?? '') : ($datos['Razon_social'] ?? '')));
        $partes         = array_values(array_filter(explode(' ', $nombreCompleto)));
        $total_p        = count($partes);

        if ($total_p === 0)     { $apellidos = '';                              $nombres = ''; }
        elseif ($total_p <= 2)  { $apellidos = implode(' ', $partes);           $nombres = ''; }
        else                    { $apellidos = implode(' ', array_slice($partes, 0, 2));
                                  $nombres   = implode(' ', array_slice($partes, 2)); }

        $esc        = fn($s) => str_replace("'", "''", $s);
        $codigoVal  = $codigo ? "'{$codigo}'" : 'NULL';

        $insertNuevos[] = sprintf(
            "INSERT INTO clientes (codigo_cliente, nombres_clientes, apellidos_clientes, razon_social, identificacion_clientes, celular_clientes, email_cliente, estado, created_at, updated_at) VALUES (%s, '%s', '%s', '', '%s', '%s', '', 'Activo', NOW(), NOW());",
            $codigoVal, $esc($nombres), $esc($apellidos), $esc($cedula), $esc($celular)
        );

        echo "OK — $apellidos $nombres\n";

    } catch (Exception $e) {
        echo "EXCEPCION: " . $e->getMessage() . "\n";
        $errores[] = $cedula;
    }
}

// ── Ensamblar y guardar SQL final ─────────────────────────────────────────
if ($insertNuevos) {
    $nuevasLineas[] = '';
    $nuevasLineas[] = '-- ============================================================';
    $nuevasLineas[] = '-- LEGAL GENERAL 2026 (1)(1) -- cedulas nuevas con codigo ' . date('Y-m-d H:i:s');
    $nuevasLineas[] = '-- ============================================================';
    foreach ($insertNuevos as $ins) {
        $nuevasLineas[] = $ins;
    }
}

file_put_contents($sqlPath, implode("\n", $nuevasLineas));

echo "\n--- RESUMEN ---\n";
echo "Codigos asignados en SQL existente: $actualizadas\n";
echo "Sin codigo (NULL):                  $sinCodigo\n";
echo "Nuevos INSERT agregados:            " . count($insertNuevos) . "\n";
echo "Errores API:                        " . count($errores) . "\n";
if ($errores) echo "Cedulas sin resultado: " . implode(', ', $errores) . "\n";
echo "\nSQL guardado: storage/app/clientes_insert.sql\n";
