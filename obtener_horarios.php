<?php
date_default_timezone_set('America/Mexico_City');
require_once('config/conexion.php');

$fecha_hoy = date('Y-m-d');

// Consulta SQL con LEFT JOIN flexible
$sql = "SELECT 
            h.id_horario,
            h.carrera,
            h.cuatrimestre,
            h.docente,
            h.asignatura AS materia,
            h.hora_inicio,
            h.hora_fin,
            COALESCE(r.estatus, 'PENDIENTE') AS estatus
        FROM horarios_materias h
        LEFT JOIN registros_asistencia r 
               ON (h.id_horario = r.id_horario OR h.id_docente = r.id_docente)
              AND r.fecha = '$fecha_hoy'";

$resultado = mysqli_query($conn, $sql);

// Fallback por si la tabla principal de horarios se llama 'horarios' en lugar de 'horarios_materias'
if (!$resultado) {
    $sql = "SELECT 
                h.id_horario,
                h.carrera,
                h.cuatrimestre,
                h.docente,
                h.materia,
                h.hora_inicio,
                h.hora_fin,
                COALESCE(r.estatus, 'PENDIENTE') AS estatus
            FROM horarios h
            LEFT JOIN registros_asistencia r 
                   ON (h.id_horario = r.id_horario OR h.id_docente = r.id_docente)
                  AND r.fecha = '$fecha_hoy'";
    $resultado = mysqli_query($conn, $sql);
}

$datos = array();

if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        // Limpiar el estatus para garantizar que no llegue nulo o vacío
        if (empty($fila['estatus']) || trim($fila['estatus']) === '') {
            $fila['estatus'] = 'PENDIENTE';
        }
        $datos[] = $fila;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($datos);
exit();
?>