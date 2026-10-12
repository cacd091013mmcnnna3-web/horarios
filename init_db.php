<?php
require_once "conexion.php";
$sql = file_get_contents("bd_asistencia.sql");
$conn->exec($sql);
echo "¡Base de datos cargada localmente con éxito!";
?>
