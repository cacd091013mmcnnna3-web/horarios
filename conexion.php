<?php
$host = "localhost";       
$user = "root";            
$pass = "";            
$db   = "asistencias"; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("❌ Error fatal: No se pudo conectar a la base de datos. Detalle: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

?>