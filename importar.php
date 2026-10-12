<?php
$host = 'mysql-26ff18b0-soycecytem-14f4.f.aivencloud.com';
$port = 22560;
$user = 'avnadmin';
$password = 'TU_CONTRASEÑA_DE_AIVEN'; // Reemplaza por la contraseña real
$dbname = 'defaultdb';

$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = file_get_contents('bd_asistencia'); // Si tiene extensión .sql pon 'bd_asistencia.sql'

if ($conn->multi_query($sql)) {
    echo "<h1>¡Base de datos importada con éxito a Aiven!</h1>";
} else {
    echo "Error al importar: " . $conn->error;
}
?>