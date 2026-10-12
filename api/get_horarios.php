<?php
header('Content-Type: application/json; charset=utf-8');
require_once('../config/conexion.php');

$tipo  = $_GET['tipo'] ?? 'todos';
$valor = urldecode($_GET['valor'] ?? '');

$sql = "SELECT h.carrera, h.cuatrimestre, h.asignatura, h.dia_semana, h.hora_inicio, h.hora_fin, d.nombre AS docente 
        FROM horarios h 
        JOIN docentes d ON h.id_docente = d.id_docente";

if ($tipo === 'carrera' && !empty($valor)) {
    $sql .= " WHERE h.carrera = '" . $conn->real_escape_string($valor) . "'";
    $filtro_nombre = "Carrera: " . $valor;
} elseif ($tipo === 'docente' && !empty($valor)) {
    $sql .= " WHERE h.id_docente = " . intval($valor);
    $filtro_nombre = "Horario por Docente";
} else {
    $filtro_nombre = "Todos los Horarios";
}

$sql .= " ORDER BY h.carrera ASC, h.cuatrimestre ASC, h.hora_inicio ASC";
$result = $conn->query($sql);

$horarios = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['hora_inicio'] = date("g:i A", strtotime($row['hora_inicio']));
        $row['hora_fin']    = date("g:i A", strtotime($row['hora_fin']));
        $horarios[] = $row;
    }
}

echo json_encode([
    'status' => 'success',
    'filtro' => $filtro_nombre,
    'data'   => $horarios
]);
?>