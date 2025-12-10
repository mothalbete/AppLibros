<?php
$host = "";
$user = "";
$password = "";
$database = "";

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}
?>
