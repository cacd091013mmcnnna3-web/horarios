<?php
include('conexion.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$carpeta_mailer = '';
if (is_dir('phpmailer')) {
    $carpeta_mailer = 'phpmailer/';
} elseif (is_dir('PHPMailer')) {
    $carpeta_mailer = 'PHPMailer/';
} else {
    die("<div style='font-family:sans-serif; padding:20px; background:#FFEBEE; color:#C62828; border-radius:8px; margin:20px; border:2px solid #000;'>
            <strong>⚠️ Error de Sistema:</strong> No se encuentra la carpeta de la librería. <br>
            Asegúrate de tener una carpeta llamada <code>phpmailer</code> dentro de <code>C:\\xampp\\htdocs\\horarios\\</code>
         </div>");
}

$archivos_en_carpeta = scandir($carpeta_mailer);
$exception_file = '';
$phpmailer_file = '';
$smtp_file = '';

foreach ($archivos_en_carpeta as $archivo) {
    if (strcasecmp($archivo, 'Exception.php') === 0) $exception_file = $carpeta_mailer . $archivo;
    if (strcasecmp($archivo, 'PHPMailer.php') === 0) $phpmailer_file = $carpeta_mailer . $archivo;
    if (strcasecmp($archivo, 'SMTP.php') === 0) $smtp_file = $carpeta_mailer . $archivo;
}

if ($exception_file && $phpmailer_file && $smtp_file) {
    require $exception_file;
    require $phpmailer_file;
    require $smtp_file;
} else {
    die("<div style='font-family:sans-serif; padding:20px; background:#FFEBEE; color:#C62828; border-radius:8px; margin:20px; border:2px solid #000;'>
            <strong>⚠️ Error de Archivos Internos:</strong> Se detectó la carpeta, pero faltan componentes esenciales.<br>
            Verifica que dentro de <code>C:\\xampp\\htdocs\\horarios\\$carpeta_mailer</code> se encuentren estrictamente estos 3 archivos:
            <ul>
                <li>Exception.php " . ($exception_file ? '✅' : '❌') . "</li>
                <li>PHPMailer.php " . ($phpmailer_file ? '✅' : '❌') . "</li>
                <li>SMTP.php " . ($smtp_file ? '✅' : '❌') . "</li>
            </ul>
         </div>");
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asistencia'])) {
    
    $fecha_actual = date('Y-m-d');
    
    $id_horario = isset($_POST['id_horario']) ? intval($_POST['id_horario']) : 0;

    $consulta_materia = mysqli_query($conn, "SELECT asignatura FROM horarios_materias WHERE id_horario = '$id_horario'");
    $datos_materia = mysqli_fetch_assoc($consulta_materia);
    $nombre_materia = isset($datos_materia['asignatura']) ? $datos_materia['asignatura'] : "Asignatura Escolar";

    foreach ($_POST['asistencia'] as $id_alumno => $estatus) {
        
        $id_alumno = intval($id_alumno);
        $estatus_limpio = mysqli_real_escape_string($conn, $estatus);

        $sql = "INSERT INTO asistencias (id_alumno, id_horario, fecha, estatus) 
                VALUES ('$id_alumno', '$id_horario', '$fecha_actual', '$estatus_limpio')";
        mysqli_query($conn, $sql);

        if ($estatus_limpio == 'F') {
            
            $consulta_alumno = mysqli_query($conn, "SELECT nombre_completo, correo_alumno FROM alumnos WHERE id_alumno = '$id_alumno'");
            $datos_alumno = mysqli_fetch_assoc($consulta_alumno);
            
            if ($datos_alumno) {
                $nombre_alumno = $datos_alumno['nombre_completo'];
                $correo_destino = $datos_alumno['correo_alumno'];

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com'; 
                    $mail->SMTPAuth   = true; 
                    
                    $mail->Username   = 'dannakandia@gmail.com';  
                    $mail->Password   = 'W3b1W4b0l0l';       

                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
                    $mail->Port       = 587; 
                    $mail->CharSet    = 'UTF-8'; 

                    $mail->setFrom('dannakandia@gmail.com', 'Universidad Anáhuac - Control de Asistencias');
                    $mail->addAddress($correo_destino, $nombre_alumno);

                    $mail->isHTML(true);
                    $mail->Subject = "⚠️ Aviso de Inasistencia - $nombre_materia";
                    
                    $mail->Body = "
                        <div style='font-family: \"Segoe UI\", Arial, sans-serif; max-width: 600px; margin: 20px auto; border: 2px solid #000000; border-radius: 8px; overflow: hidden; background-color: #EFF2F7;'>
                            
                            <div style='background-color: #511B95; color: #FFFFFF; padding: 20px; text-align: left; border-bottom: 2px solid #000000;'>
                                <h1 style='margin: 0; font-size: 22px; font-weight: 600;'>Universidad Anáhuac</h1>
                                <p style='margin: 4px 0 0 0; font-size: 13px; opacity: 0.85;'>Sistema Automatizado de Control Escolar</p>
                            </div>
                            
                            <div style='background-color: #FFFFFF; padding: 25px; margin: 20px; border: 2px solid #000000; border-radius: 6px;'>
                                <h2 style='margin-top: 0; color: #1F2937; font-size: 18px;'>Notificación Oficial de Falta</h2>
                                <p>Estimado(a) Alumno(a): <strong>$nombre_alumno</strong>,</p>
                                <p>Se le comunica que con fecha del <strong>$fecha_actual</strong> se ha asentado un registro de <strong>INASISTENCIA</strong> en los controles correspondientes a la materia:</p>
                                
                                <div style='background-color: #FF8A50; color: #FFFFFF; padding: 12px 15px; font-size: 16px; font-weight: bold; border: 2px solid #000000; border-radius: 6px; text-align: center; margin: 15px 0;'>
                                    $nombre_materia
                                division>
                                
                                <p style='color: #C62828; font-weight: bold; background-color: #FFEBEE; padding: 12px; border-radius: 6px; border: 2px solid #C62828; font-size: 13px; margin-top: 20px;'>
                                    ⚠️ IMPORTANTE: Recuerde que acumular faltas recurrentes pone en riesgo su derecho a presentar evaluaciones de conformidad con el reglamento de la institución.
                                </p>
                                
                                <p style='font-size: 13px; color: #555555; margin-top: 20px;'>Si cuenta con un comprobante justificable institucional o médico válido, recuerde presentarlo con su docente en la siguiente sesión presencial.</p>
                            </div>
                            
                            <div style='background-color: #230847; color: #FFFFFF; text-align: center; padding: 12px; font-size: 12px; border-top: 2px solid #000000;'>
                                Este es un correo automatizado de gestión universitaria, por favor evite responder directamente.
                            </div>
                        </div>
                    ";

                    $mail->send();

                } catch (Exception $e) {
                   
                    error_log("Error al enviar correo al alumno ID $id_alumno: {$mail->ErrorInfo}");
                }
            }
        }
    }

    header("Location: lista.php?horario=$id_horario&status=success");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>