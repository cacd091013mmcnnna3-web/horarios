<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarNotificacionInasistencia($emailDocente, $nombreDocente, $materia, $fecha) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';             
        $mail->SMTPAuth   = true;
        $mail->Username   = 'dannakandia@gmail.com'; 
        $mail->Password   = 'mauruqvodpqmtxdm'; // Sin espacios entre las letras
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('dannakandia@gmail.com', 'Control Académico');
        $mail->addAddress($emailDocente, $nombreDocente);

        $mail->isHTML(true);
        $mail->Subject = 'Aviso de Inasistencia Registrada - ' . $fecha;
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <h2 style='color: #d9534f;'>Notificación de Inasistencia</h2>
                <p>Estimado/a <strong>{$nombreDocente}</strong>,</p>
                <p>Le informamos que se ha registrado una <strong>inasistencia</strong> para la clase de <strong>{$materia}</strong> el día <strong>{$fecha}</strong>.</p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar el correo: " . $mail->ErrorInfo);
        return false;
    }
}