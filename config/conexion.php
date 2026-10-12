<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "bd_asistencia";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error',
        'mensaje' => 'Error de conexión a la base de datos: ' . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");
?>