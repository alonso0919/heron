<?php

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../config/facturama.php';

require_login();

$idVenta = (int)($_GET['id_venta'] ?? 0);
if ($idVenta <= 0) {
    http_response_code(422);
    exit('id_venta inválido');
}

$idUsuario = current_user_id();
$stmt = $conn->prepare("SELECT f.*, v.id_usuario
    FROM facturas_emitidas f
    INNER JOIN ventas v ON v.id_venta = f.id_venta
    WHERE f.id_venta = ? LIMIT 1");
$stmt->bind_param('i', $idVenta);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if (!$invoice || empty($invoice['facturama_cfdi_id'])) {
    http_response_code(404);
    exit('Factura no encontrada');
}

// Solo el dueño puede descargar
if ($idUsuario && (int)($invoice['id_usuario'] ?? 0) !== $idUsuario) {
    http_response_code(403);
    exit('No autorizado');
}

$cfdiId = trim((string)$invoice['facturama_cfdi_id']);
$url = rtrim(FACTURAMA_API_BASE, '/') . '/Cfdi/pdf/issued/' . rawurlencode($cfdiId);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
    CURLOPT_USERPWD => FACTURAMA_API_USER . ':' . FACTURAMA_API_PASSWORD,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 5,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_CAINFO => __DIR__ . '/../../config/cacert.pem',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json, application/pdf'
    ],
]);

$response = curl_exec($ch);
$http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Error cURL: ' . $error);
}

if ($http < 200 || $http >= 300) {
    http_response_code($http ?: 500);
    header('Content-Type: text/plain; charset=utf-8');
    echo $response;
    exit;
}

// Caso 1: Facturama regresó JSON con el PDF en base64
if (stripos($contentType, 'application/json') !== false) {
    $json = json_decode($response, true);

    if (!is_array($json)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo "La respuesta JSON no se pudo interpretar.\n\n";
        echo $response;
        exit;
    }

    if (!empty($json['Content'])) {
        $pdf = base64_decode($json['Content'], true);
        if ($pdf === false || strncmp($pdf, '%PDF', 4) !== 0) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo "El contenido base64 no produjo un PDF válido.";
            exit;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="factura_venta_' . $idVenta . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "JSON recibido, pero no contiene la propiedad Content.\n\n";
    echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Caso 2: Facturama regresó PDF binario directo
if (strncmp($response, '%PDF', 4) === 0 || stripos($contentType, 'application/pdf') !== false) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="factura_venta_' . $idVenta . '.pdf"');
    header('Content-Length: ' . strlen($response));
    echo $response;
    exit;
}

// Caso 3: respuesta inesperada
http_response_code(500);
header('Content-Type: text/plain; charset=utf-8');
echo "La respuesta no fue un PDF válido.\n\n";
echo "HTTP: " . $http . "\n";
echo "Content-Type: " . $contentType . "\n\n";
echo $response;
exit;
