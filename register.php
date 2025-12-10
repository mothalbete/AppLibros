<?php
require_once("config.php");
require_once("functions.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $resultado = registrarUsuario($_POST['nombre'], $_POST['email'], $_POST['password'], $mysqli);

    if ($resultado === "ok") {
        // Redirigir automáticamente al login
        header("Location: login.php");
        exit();
    } elseif ($resultado === "nombre_existente") {
        echo "El nombre de usuario ya existe. Elige otro.";
    } elseif ($resultado === "email_existente") {
        echo "El email ya está registrado. Usa otro.";
    } else {
        echo "Error al registrar usuario.";
    }
}
?>
<head>
    <link rel="stylesheet" href="styles.css">
</head>
<form method="post">
  Nombre: <input type="text" name="nombre" required><br>
  Email: <input type="email" name="email" required><br>
  Contraseña: <input type="password" name="password" required><br>
  <button type="submit">Registrar</button>
</form>
