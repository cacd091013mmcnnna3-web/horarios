<?php
date_default_timezone_set('America/Mexico_City');

if (file_exists('config/conexion.php')) {
    require_once('config/conexion.php');
} elseif (file_exists('../config/conexion.php')) {
    require_once('../config/conexion.php');
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($conn) || $conn->connect_error) {
    echo json_encode(["error" => "No hay conexión activa a la base de datos."]);
    exit;
}

$fecha_hoy = date('Y-m-d');

$sql = "SELECT 
            h.id_horario,
            h.carrera,
            h.cuatrimestre,
            h.asignatura,
            h.hora_inicio,
            h.hora_fin,
            d.nombre AS docente,
            r.hora_salida,
            r.observaciones,
            CASE 
                WHEN UPPER(TRIM(r.estatus)) LIKE '%TIEMPO%' OR UPPER(TRIM(r.estatus)) = 'A' THEN 'A TIEMPO'
                WHEN UPPER(TRIM(r.estatus)) LIKE '%RETARDO%' OR UPPER(TRIM(r.estatus)) = 'R' THEN 'RETARDO'
                WHEN UPPER(TRIM(r.estatus)) LIKE '%FALTA%' OR UPPER(TRIM(r.estatus)) = 'F' THEN 'FALTA'
                ELSE 'PENDIENTE'
            END AS estatus
        FROM horarios h
        INNER JOIN docentes d ON h.id_docente = d.id_docente
        LEFT JOIN registros_asistencia r 
               ON h.id_horario = r.id_horario 
              AND r.fecha = '$fecha_hoy'
        ORDER BY h.hora_inicio ASC";
$result = $conn->query($sql);
$horarios = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $horarios[] = $row;
    }
}

echo json_encode($horarios, JSON_UNESCAPED_UNICODE);
?>