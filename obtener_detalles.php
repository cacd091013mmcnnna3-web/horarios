<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

include('conexion.php');

$respuesta = [
    'success' => false,
    'docente' => 'No asignado',
    'horarios' => [],
    'error' => 'Error desconocido'
];

if (!$conn) {
    $respuesta['error'] = 'Error de conexión a la base de datos: ' . mysqli_connect_error();
    echo json_encode($respuesta);
    exit();
}

$materia = isset($_GET['materia']) ? $_GET['materia'] : '';

if (!empty($materia)) {
    
    $materia_limpia = mysqli_real_escape_string($conn, $materia);

    $query_docente = "SELECT d.nombre_completo 
                      FROM horarios_materias h 
                      INNER JOIN docentes d ON h.id_docente = d.id_docente 
                      WHERE h.asignatura = '$materia_limpia' LIMIT 1";
                      
    $res_docente = mysqli_query($conn, $query_docente);
    
    if ($res_docente) {
        $docente = mysqli_fetch_assoc($res_docente);
        if ($docente) {
            $respuesta['docente'] = $docente['nombre_completo'];
        }
    }

    $query_horarios = "SELECT id_horario, dia_semana, hora_inicio, hora_fin, tipo_modalidad 
                       FROM horarios_materias 
                       WHERE asignatura = '$materia_limpia'";
                       
    $res_horarios = mysqli_query($conn, $query_horarios);
    
    if ($res_horarios) {
        $horarios_lista = [];
        while($h = mysqli_fetch_assoc($res_horarios)) {
            $horarios_lista[] = [
                'id_horario' => $h['id_horario'],
                'dia' => $h['dia_semana'],
                'inicio' => $h['hora_inicio'],
                'fin' => $h['hora_fin'],
                'mod' => $h['tipo_modalidad']
            ];
        }
        
        if (count($horarios_lista) > 0) {
            $respuesta['success'] = true;
            $respuesta['horarios'] = $horarios_lista;
            unset($respuesta['error']); // Quitamos el mensaje de error de la respuesta
        } else {
            $respuesta['error'] = 'No se encontraron horarios para la materia: ' . $materia;
        }
    } else {
        $respuesta['error'] = 'Error en la consulta SQL de horarios: ' . mysqli_error($conn);
    }

} else {
    $respuesta['error'] = 'El parámetro de la materia no llegó al servidor.';
}

echo json_encode($respuesta);
exit();
?>