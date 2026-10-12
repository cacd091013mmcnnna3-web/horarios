<?php
header('Content-Type: application/json');
require_once('config/conexion.php');

$id_horario = $_POST['id_horario'] ?? null;
$docente_nombre = trim($_POST['docente'] ?? '');
$carrera = trim($_POST['carrera'] ?? '');
$asignatura = trim($_POST['asignatura'] ?? '');
$cuatrimestre = trim($_POST['cuatrimestre'] ?? '');
$hora_inicio = $_POST['hora_inicio'] ?? '';
$hora_fin = $_POST['hora_fin'] ?? '';

if (!$id_horario || empty($docente_nombre)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    exit;
}

$stmt_get = $conn->prepare("SELECT id_docente FROM horarios WHERE id_horario = ?");
$stmt_get->bind_param("i", $id_horario);
$stmt_get->execute();
$res_get = $stmt_get->get_result()->fetch_assoc();

if ($res_get) {
    $id_docente = $res_get['id_docente'];
    
    $stmt_upd_doc = $conn->prepare("UPDATE docentes SET nombre = ? WHERE id_docente = ?");
    $stmt_upd_doc->bind_param("si", $docente_nombre, $id_docente);
    $stmt_upd_doc->execute();
}

// Actualizar los datos del horario
$stmt_upd = $conn->prepare("UPDATE horarios SET carrera = ?, asignatura = ?, cuatrimestre = ?, hora_inicio = ?, hora_fin = ? WHERE id_horario = ?");
$stmt_upd->bind_param("sssssi", $carrera, $asignatura, $cuatrimestre, $hora_inicio, $hora_fin, $id_horario);

if ($stmt_upd->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Cambios actualizados en la base de datos.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar: ' . $conn->error]);
}
?>