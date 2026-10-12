<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Mexico_City');

if (file_exists('config/conexion.php')) {
    require_once('config/conexion.php');
} elseif (file_exists('../config/conexion.php')) {
    require_once('../config/conexion.php');
}

$id_horario = $_POST['id_horario'] ?? null;
$motivo = $_POST['motivo'] ?? '';
$detalle = $_POST['detalle'] ?? '';

if (!$id_horario) {
    echo json_encode(['status' => 'error', 'message' => 'Falta el ID del horario.']);
    exit;
}

$fecha_hoy = date('Y-m-d');
$observacion = "Salida anticipada: [" . $motivo . "] - " . $detalle;

$stmt_check = $conn->prepare("SELECT id_registro FROM registros_asistencia WHERE id_horario = ? AND fecha = ?");
$stmt_check->bind_param("is", $id_horario, $fecha_hoy);
$stmt_check->execute();
$res = $stmt_check->get_result();

if ($res->num_rows > 0) {
    $reg = $res->fetch_assoc();
    $stmt_upd = $conn->prepare("UPDATE registros_asistencia SET observaciones = ? WHERE id_registro = ?");
    $stmt_upd->bind_param("si", $observacion, $reg['id_registro']);
    $stmt_upd->execute();
} else {
    $stmt_doc = $conn->prepare("SELECT id_docente FROM horarios WHERE id_horario = ?");
    $stmt_doc->bind_param("i", $id_horario);
    $stmt_doc->execute();
    $doc = $stmt_doc->get_result()->fetch_assoc();
    $id_docente = $doc['id_docente'] ?? 0;

    $stmt_ins = $conn->prepare("INSERT INTO registros_asistencia (id_docente, id_horario, fecha, observaciones, estatus) VALUES (?, ?, ?, ?, 'Pendiente Salida')");
    $stmt_ins->bind_param("iiss", $id_docente, $id_horario, $fecha_hoy, $observacion);
    $stmt_ins->execute();
}

echo json_encode(['status' => 'success']);
?>