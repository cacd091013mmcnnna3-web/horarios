<?php
date_default_timezone_set('America/Mexico_City');

if (file_exists('config/conexion.php')) {
    require_once('config/conexion.php');
} elseif (file_exists('../config/conexion.php')) {
    require_once('../config/conexion.php');
}

$id_horario = $_GET['id'] ?? null;
$mensaje = "";
$clase_alert = "alert-danger";
$icono = "fa-circle-xmark text-danger";
$estatus_db = "Falta";

if ($id_horario) {
    // Consultar el horario y docente
    $sql = "SELECT id_docente, hora_inicio, hora_fin, dia_semana FROM horarios WHERE id_horario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_horario);
    $stmt->execute();
    $horario = $stmt->get_result()->fetch_assoc();

    if ($horario) {
        $id_docente = $horario['id_docente'];
        $fecha_hoy = date('Y-m-d');
        $hora_actual_str = date('H:i:s');

        // Tomar hora de inicio de la clase hoy
        $hora_inicio_clase = new DateTime($fecha_hoy . ' ' . $horario['hora_inicio']);
        $hora_registro_actual = new DateTime($fecha_hoy . ' ' . $hora_actual_str);

        // Minutos de diferencia
        $diferencia_segundos = $hora_registro_actual->getTimestamp() - $hora_inicio_clase->getTimestamp();
        $diferencia_minutos = $diferencia_segundos / 60;

        // Evaluación estricta de tolerancia:
        // Si llega hasta 15 minutos después de iniciar la clase -> A Tiempo
        // Entre 16 y 30 minutos -> Retardo
        // Más de 30 minutos o antes de tiempo desfasado -> Falta
        if ($diferencia_minutos <= 15 && $diferencia_minutos >= -60) { 
            $estatus_db = 'A Tiempo';
            $mensaje = "¡Asistencia registrada A TIEMPO!";
            $clase_alert = "alert-success";
            $icono = "fa-circle-check text-success";
        } elseif ($diferencia_minutos > 15 && $diferencia_minutos <= 30) {
            $estatus_db = 'Retardo';
            $mensaje = "Asistencia registrada con RETARDO.";
            $clase_alert = "alert-warning";
            $icono = "fa-triangle-exclamation text-warning";
        } else {
            $estatus_db = 'Falta';
            $mensaje = "Has llegado fuera del tiempo de tolerancia (FALTA).";
            $clase_alert = "alert-danger";
            $icono = "fa-circle-xmark text-danger";
        }

        // Guardar o actualizar registro de hoy
        $sql_check = "SELECT id_registro FROM registros_asistencia WHERE id_horario = ? AND fecha = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("is", $id_horario, $fecha_hoy);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $sql_upd = "UPDATE registros_asistencia SET hora = ?, estatus = ? WHERE id_horario = ? AND fecha = ?";
            $stmt_upd = $conn->prepare($sql_upd);
            $stmt_upd->bind_param("ssis", $hora_actual_str, $estatus_db, $id_horario, $fecha_hoy);
            $stmt_upd->execute();
        } else {
            $sql_ins = "INSERT INTO registros_asistencia (id_docente, id_horario, fecha, hora, estatus) VALUES (?, ?, ?, ?, ?)";
            $stmt_ins = $conn->prepare($sql_ins);
            $stmt_ins->bind_param("iisss", $id_docente, $id_horario, $fecha_hoy, $hora_actual_str, $estatus_db);
            $stmt_ins->execute();
        }

    } else {
        $mensaje = "Horario no encontrado.";
    }
} else {
    $mensaje = "Falta el ID de horario.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando Asistencia | Anáhuac</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .card-loader {
            border: 3px solid #000;
            box-shadow: 6px 6px 0px #000;
            background: #fff;
            border-radius: 12px;
        }
        .gif-procesando {
            width: 140px;
            height: 140px;
            object-fit: contain;
            margin: 0 auto;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100 p-3">

    <div class="card card-loader text-center p-4" style="max-width: 420px; width: 100%;">
        
        <!-- PANTALLA DE CARGA CON GIF -->
        <div id="loader-pantalla" class="py-3 d-flex flex-column align-items-center">
            <img src="varess.gif" alt="Cargando..." class="gif-procesando mb-3">
            <h3 style="font-family: 'Bebas Neue', sans-serif;" class="fs-2 mb-2 text-dark">PROCESANDO ASISTENCIA...</h3>
            <div class="d-flex align-items-center gap-2">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="text-muted fw-bold small">Validando datos...</span>
            </div>
        </div>

        <!-- RESULTADO FINAL -->
        <div id="resultado-pantalla" class="d-none py-2">
            <h3 style="font-family: 'Bebas Neue', sans-serif;" class="mb-3">REGISTRO DE ASISTENCIA</h3>
            <div class="alert <?= $clase_alert ?> border border-dark fw-bold mb-3 shadow-sm">
                <i class="fa-solid <?= $estatus_final === 'A TIEMPO' ? 'fa-circle-check text-success' : ($estatus_final === 'RETARDO' ? 'fa-triangle-exclamation text-warning' : 'fa-circle-xmark text-danger') ?> fa-3x d-block mb-2"></i>
                <?= $mensaje ?>
            </div>
            <p class="small text-muted fw-bold mb-0"><i class="fa-solid fa-mobile-screen me-1"></i> Ya puedes cerrar esta ventana.</p>
        </div>

    </div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => {
            document.getElementById('loader-pantalla').classList.add('d-none');
            document.getElementById('resultado-pantalla').classList.remove('d-none');
        }, 2000); 
    });
</script>

</body>
</html>