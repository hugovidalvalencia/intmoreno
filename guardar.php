<?php
// API ultraligera para leer y guardar datos.json directamente en el servidor
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$jsonFile = __DIR__ . '/datos.json';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (file_exists($jsonFile)) {
        readfile($jsonFile);
    } else {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'datos.json no encontrado']);
    }
    exit;
}

if ($method === 'POST' || $method === 'PUT') {
    $rawBody = file_get_contents('php://input');
    $decoded = json_decode($rawBody, true);

    if ($decoded === null || !isset($decoded['participants'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
        exit;
    }

    $formatted = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $written = @file_put_contents($jsonFile, $formatted, LOCK_EX);

    if ($written === false) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'No se pudo escribir en datos.json. Verifica permisos de escritura (664 o 666) en el servidor.'
        ]);
        exit;
    }

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
