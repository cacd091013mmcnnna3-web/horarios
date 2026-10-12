<?php
// Activar reporte de errores para ver el fallo exacto si vuelve a ocurrir
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Mexico_City');

// Intentar incluir la conexión
if (file_exists('config/conexion.php')) {
    require_once('config/conexion.php');
} elseif (file_exists('../config/conexion.php')) {
    require_once('../config/conexion.php');
} else {
    die("Error: No se encontró el archivo de conexión a la base de datos.");
}

if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión a la Base de Datos: " . ($conn->connect_error ?? 'Conexión no definida'));
}

$id_horario = $_GET['id'] ?? null;
$mensaje = "";
$mostrar_cuestionario = false;
$id_registro = null;

$fecha_hoy = date('Y-m-d');
$hora_actual_str = date('H:i:s');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_registro'])) {
    // Procesar cuestionario enviado
    $id_reg = $_POST['id_registro'];
    $motivo = $_POST['motivo_salida'] ?? '';
    $detalle = $_POST['detalle_motivo'] ?? '';
    
    $observacion_final = "Salida anticipada: [" . $motivo . "] " . $detalle;

    $stmt_upd = $conn->prepare("UPDATE registros_asistencia SET hora_salida = ?, observaciones = ? WHERE id_registro = ?");
    if ($stmt_upd) {
        $stmt_upd->bind_param("ssi", $hora_actual_str, $observacion_final, $id_reg);
        $stmt_upd->execute();
        $mensaje = "¡Motivo de salida registrado correctamente!";
    } else {
        $mensaje = "Error al actualizar observaciones: " . $conn->error;
    }

} elseif ($id_horario) {
    // Consultar horario
    $stmt = $conn->prepare("SELECT id_docente, hora_fin FROM horarios WHERE id_horario = ?");
    $stmt->bind_param("i", $id_horario);
    $stmt->execute();
    $horario = $stmt->get_result()->fetch_assoc();

    if ($horario) {
        $id_docente = $horario['id_docente'];
        
        // Buscar el registro de entrada de hoy
        $stmt_check = $conn->prepare("SELECT id_registro FROM registros_asistencia WHERE id_horario = ? AND fecha = ?");
        $stmt_check->bind_param("is", $id_horario, $fecha_hoy);
        $stmt_check->execute();
        $res = $stmt_check->get_result();

        if ($res->num_rows > 0) {
            $reg = $res->fetch_assoc();
            $id_registro = $reg['id_registro'];

            // Actualizar la hora de salida
            $stmt_upd = $conn->prepare("UPDATE registros_asistencia SET hora_salida = ? WHERE id_registro = ?");
            $stmt_upd->bind_param("si", $hora_actual_str, $id_registro);
            $stmt_upd->execute();
        } else {
            // Crear registro directo si no marcó entrada
            $stmt_ins = $conn->prepare("INSERT INTO registros_asistencia (id_docente, id_horario, fecha, hora_salida, estatus) VALUES (?, ?, ?, ?, 'Salida Registrada')");
            $stmt_ins->bind_param("iiss", $id_docente, $id_horario, $fecha_hoy, $hora_actual_str);
            $stmt_ins->execute();
            $id_registro = $conn->insert_id;
        }

        // Comparar hora actual vs hora final del horario
        $hora_fin_obj = new DateTime($fecha_hoy . ' ' . $horario['hora_fin']);
        $hora_actual_obj = new DateTime($fecha_hoy . ' ' . $hora_actual_str);

        if ($hora_actual_obj < $hora_fin_obj) {
            $mostrar_cuestionario = true;
            $mensaje = "Has registrado tu salida ANTES de tu horario límite (" . substr($horario['hora_fin'], 0, 5) . " hrs).";
        } else {
            $mensaje = "¡Salida registrada exitosamente a tiempo!";
        }
    } else {
        $mensaje = "El horario con ID " . htmlspecialchars($id_horario) . " no existe en la base de datos.";
    }
} else {
    $mensaje = "No se especificó un ID de horario válido.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Salida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card-custom {
            max-width: 440px; width: 100%; border: 3px solid #000; border-radius: 15px; box-shadow: 6px 6px 0px #000;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100 p-3">

    <div class="card card-custom p-4 bg-white text-center">
        <h4 class="fw-bold mb-3 text-uppercase">CHECADOR DE SALIDA</h4>

        <?php if ($mostrar_cuestionario): ?>
            <div class="alert alert-warning border border-dark fw-bold mb-3">
                <i class="fa-solid fa-triangle-exclamation text-warning display-4 d-block mb-2"></i>
                <?= htmlspecialchars($mensaje) ?>
            </div>

            <!-- CUESTIONARIO DE SALIDA ANTICIPADA -->
            <form method="POST" action="marcar_salida_qr.php?id=<?= htmlspecialchars($id_horario) ?>" class="text-start">
                <input type="hidden" name="id_registro" value="<?= htmlspecialchars($id_registro) ?>">
                
                <label class="form-label fw-bold small text-uppercase">Motivo de Salida Anticipada:</label>
                <select name="motivo_salida" class="form-select border-dark mb-3" required>
                    <option value="">-- Selecciona una opción --</option>
                    <option value="Asunto Personal / Familiar">Asunto Personal / Familiar</option>
                    <option value="Motivo de Salud / Médica">Motivo de Salud / Médica</option>
                    <option value="Comisión Académica / Institucional">Comisión Académica / Institucional</option>
                    <option value="Fin de Temario / Examen Concluido">Fin de Temario / Examen Concluido</option>
                    <option value="Otro">Otro Motivo</option>
                </select>

                <label class="form-label fw-bold small text-uppercase">Justificación / Detalles:</label>
                <textarea name="detalle_motivo" class="form-control border-dark mb-3" rows="3" placeholder="Explica brevemente la razón..." required></textarea>

                <button type="submit" class="btn btn-warning btn-outline-dark fw-bold w-100 shadow-sm">
                    <i class="fa-solid fa-paper-plane me-1"></i> GUARDAR MOTIVO
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-success border border-dark fw-bold mb-3">
                <i class="fa-solid fa-circle-check text-success display-3 d-block mb-2"></i>
                <?= htmlspecialchars($mensaje) ?>
            </div>
            <p class="small text-muted fw-bold mb-0"><i class="fa-solid fa-mobile-screen me-1"></i> Ya puedes cerrar esta ventana.</p>
        <?php endif; ?>
    </div>

</body>
</html>