<?php
// config/conexion.php
// Las credenciales de base de datos se leen del archivo .env

$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME')     ?: 'heron';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexion: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function getServicios(mysqli $conn, bool $destacados = false): array {
    $sql = "SELECT * FROM servicios WHERE activo = 1";
    if ($destacados) {
        $sql .= " AND destacado = 1";
    }
    $sql .= " ORDER BY categoria, nombre";
    $result   = $conn->query($sql);
    $servicios = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $servicios[] = $row;
        }
    }
    return $servicios;
}

function getServicioById(mysqli $conn, int $id): ?array {
    $sql  = "SELECT * FROM servicios WHERE id_servicio = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

function getServiciosPorCategoria(mysqli $conn): array {
    $servicios = getServicios($conn, false);
    $grupos    = [];
    foreach ($servicios as $s) {
        $cat = $s['categoria'] ?? 'Otros';
        if (!isset($grupos[$cat])) $grupos[$cat] = [];
        $grupos[$cat][] = $s;
    }
    return $grupos;
}
