<?php
header('Content-Type: application/json; charset=utf-8');
require_once('../config/conexion.php');

$sql = "SELECT h.hora_inicio, h.hora_fin, h.dia_semana, h.asignatura AS materia, d.nombre AS profesor
        FROM horarios h
        INNER JOIN docentes d ON h.id_docente = d.id_docente
        ORDER BY h.hora_inicio ASC";

$resultado = $conn->query($sql);
$matriz = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        $rango_hora = date('h:i A', strtotime($row['hora_inicio'])) . ' - ' . date('h:i A', strtotime($row['hora_fin']));
        $dia = ucfirst(strtolower($row['dia_semana']));

        $matriz[$rango_hora][$dia] = [
            'materia'  => $row['materia'],
            'profesor' => $row['profesor']
        ];
    }
}

echo json_encode($matriz);
?>