<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Validar que solo el administrador pueda realizar cambios
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['status' => 'error', 'mensaje' => 'Acceso no autorizado.']);
    exit;
}

require_once('config/conexion.php'); // Utiliza $conn de tu conexion.php

// Recibir los datos enviados desde el formulario
$id_horario  = $_POST['id_horario'] ?? null;
$asignatura  = $_POST['asignatura'] ?? null;
$cuatrimestre = $_POST['cuatrimestre'] ?? null;
$hora_inicio = $_POST['hora_inicio'] ?? null;
$hora_fin    = $_POST['hora_fin'] ?? null;

if ($id_horario && $asignatura && $cuatrimestre && $hora_inicio && $hora_fin) {
    // Asegurar que las horas tengan el formato completo HH:MM:SS
    if (strlen($hora_inicio) == 5) { $hora_inicio .= ":00"; }
    if (strlen($hora_fin) == 5) { $hora_fin .= ":00"; }

    // Sentencia para actualizar la tabla horarios en MySQL
    $sql = "UPDATE horarios 
            SET asignatura = ?, cuatrimestre = ?, hora_inicio = ?, hora_fin = ? 
            WHERE id_horario = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $asignatura, $cuatrimestre, $hora_inicio, $hora_fin, $id_horario);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'mensaje' => 'Horario actualizado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error al actualizar en la base de datos: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Por favor, completa todos los campos requeridos.']);
}
?>