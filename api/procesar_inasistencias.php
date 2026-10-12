<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/mailer.php';

$db = null;
if (isset($conn) && $conn) {
    $db = $conn;
} elseif (isset($conexion) && $conexion) {
    $db = $conexion;
}

if (!$db) {
    echo json_encode(['exito' => false, 'error' => 'Error de conexión con la base de datos']);
    exit;
}

$fecha_hoy = date('Y-m-d');

try {
    $sql = "SELECT d.id_docente, d.nombre, d.correo, h.asignatura 
            FROM registros_asistencia r
            INNER JOIN docentes d ON r.id_docente = d.id_docente
            LEFT JOIN horarios h ON d.id_docente = h.id_docente
            WHERE r.fecha = '$fecha_hoy' 
              AND (r.estatus = 'Falta' OR r.estatus = 'Inasistencia')
            GROUP BY d.id_docente";

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

    $enviados = 0;
    $sin_correo = 0;

    foreach ($faltantes as $docente) {
        $correo = $docente['correo'];
        $nombre = $docente['nombre'];
        $materia = $docente['asignatura'] ?? 'Asignatura programada';

        if (!empty($correo)) {
            if (enviarNotificacionInasistencia($correo, $nombre, $materia, $fecha_hoy)) {
                $enviados++;
            }
        } else {
            $sin_correo++;
        }
    }

    if ($enviados > 0) {
        echo json_encode([
            'exito' => true,
            'mensaje' => "Se enviaron $enviados correo(s) exitosamente." . ($sin_correo > 0 ? " ($sin_correo docentes no tienen correo registrado)" : "")
        ]);
    } else {
        echo json_encode([
            'exito' => false,
            'error' => "No se pudo entregar ningún correo. Revisa la configuración de Gmail/SMTP."
        ]);
    }
    exit;

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'error' => 'Error en el servidor: ' . $e->getMessage()]);
    exit;
}
?>