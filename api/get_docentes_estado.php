<?php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$dbname = 'bd_asistencia'; 
$username = 'root';          
$password = '';              

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT 
                d.id_docente,
                d.nombre,
                IFNULL(d.estatus, 'Activo') AS estatus,
                IFNULL(h.id_horario, 0) AS id_horario,
                IFNULL(h.cuatrimestre, '1° Cuatrimestre') AS cuatrimestre,
                IFNULL(h.asignatura, 'Sin asignatura') AS materia,
                IFNULL(h.dia_semana, 'Sábado') AS dia_semana,
                IFNULL(h.hora_inicio, '00:00:00') AS hora_inicio,
                IFNULL(h.hora_fin, '00:00:00') AS hora_fin
            FROM docentes d
            LEFT JOIN horarios h ON d.id_docente = h.id_docente
            ORDER BY d.id_docente ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($docentes, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(200);
    echo json_encode([
        'error' => 'Error de conexión o SQL: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>