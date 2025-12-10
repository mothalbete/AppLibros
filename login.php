<?php
require_once("config.php");
require_once("functions.php");
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (iniciarSesion($_POST['nombre'], $_POST['password'], $mysqli)) {
        header("Location: home.php");
        exit();
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar sesión</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h1>Iniciar sesión</h1>

  <?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="post" class="login-form">
    <label>Nombre de usuario</label>
    <input type="text" name="nombre" required>

    <label>Contraseña</label>
    <input type="password" name="password" required>

    <button type="submit">Iniciar sesión</button>

    <div class="form-footer">
      <span>¿No tienes cuenta?</span>
      <a class="register-link" href="register.php">Regístrate aquí</a>
    </div>
  </form>
</body>
</html>
