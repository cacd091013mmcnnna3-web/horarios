<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once('config/conexion.php');
$db = $conexion ?? $conn ?? $pdo ?? $link ?? null;

$id_horario = $_POST['id_horario'] ?? null;

if (!$id_horario) {
    echo json_encode(['exito' => false, 'error' => 'ID de horario no proporcionado']);
    exit;
}

$sql = "SELECT hora_inicio, hora_fin FROM horarios WHERE id_horario = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $id_horario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($horario = $resultado->fetch_assoc()) {
    $hora_actual = time();
    $hora_clase = strtotime(date('Y-m-d') . ' ' . $horario['hora_inicio']);
    
    // Diferencia en minutos
    $diferencia_minutos = ($hora_actual - $hora_clase) / 60;

    // Evaluación automática según regla de tiempo
    if ($diferencia_minutos <= 15) {
        $estatus = 'ASISTIO';
        $mensaje = 'Asistencia registrada a tiempo';
    } elseif ($diferencia_minutos > 15 && $diferencia_minutos <= 30) {
        $estatus = 'RETARDO';
        $mensaje = 'Asistencia registrada con retardo';
    } else {
        $estatus = 'FALTA';
        $mensaje = 'Tiempo límite excedido. Se marcó inasistencia';
    }

    // Guardar o actualizar registro en base de datos
    $fecha_hoy = date('Y-m-d');
    $hora_registro = date('H:i:s');

    $sql_insert = "INSERT INTO asistencias (id_horario, fecha, hora_marcada, estatus) 
                   VALUES (?, ?, ?, ?) 
                   ON DUPLICATE KEY UPDATE hora_marcada = VALUES(hora_marcada), estatus = VALUES(estatus)";
    
    $stmt_ins = $db->prepare($sql_insert);
    $stmt_ins->bind_param("isss", $id_horario, $fecha_hoy, $hora_registro, $estatus);
    
    if ($stmt_ins->execute()) {
        echo json_encode(['exito' => true, 'estatus' => $estatus, 'mensaje' => $mensaje]);
    } else {
        echo json_encode(['exito' => false, 'error' => 'Error al guardar en BD']);
    }
} else {
    echo json_encode(['exito' => false, 'error' => 'Horario no encontrado']);
}
?>