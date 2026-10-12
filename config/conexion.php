PHP
<?php
$host = "localhost";
$user = "root";
$password = ""; // En XAMPP la contraseña por defecto es vacía
$dbname = "bd_asistencia";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
