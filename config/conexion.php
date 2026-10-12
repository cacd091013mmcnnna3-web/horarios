<?php
$conn->set_charset("utf8mb4");
$conn->set_charset("utf8mb4");
$host = "mysql-26ff18b0-soycecytem-14f4.f.aivencloud.com";
$port = 22560;
$user = "avnadmin";
$password = "AVNS_IlXuQ34ssuMqmGPHc1W";
$dbname = "defaultdb";

$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

?>
