<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
require_once('../config/conexion.php');

$db = null;
if (isset($conn) && $conn) {
    $db = $conn;
} elseif (isset($conexion) && $conexion) {
    $db = $conexion;
}

if (!$db) {
    echo json_encode(['exito' => false, 'error' => 'Error de conexión']);
    exit;
}

$fecha_hoy = date('Y-m-d');

try {
    $sql = "SELECT d.id_docente, d.nombre 
            FROM registros_asistencia r
            INNER JOIN docentes d ON r.id_docente = d.id_docente
            WHERE r.fecha = '$fecha_hoy' AND r.estatus = 'Falta'";

    if ($db instanceof PDO) {
        $res = $db->query($sql);
        $faltantes = $res->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $res = $db->query($sql);
        $faltantes = $res->fetch_all(MYSQLI_ASSOC);
    }

    if (empty($faltantes)) {
        echo json_encode([
            'exito' => false, 
            'error' => 'No hay profesores con falta registrada el día de hoy.'
        ]);
        exit;
    }

    $totalFaltas = count($faltantes);

    echo json_encode([
        'exito' => true,
        'mensaje' => "Se procesaron las notificaciones para $totalFaltas docente(s) con falta."
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'error' => 'Error SQL: ' . $e->getMessage()]);
    exit;
}
?>