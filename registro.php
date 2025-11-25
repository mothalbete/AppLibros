<?php
session_start();

// Inicializar estructura si no existe
if (!isset($_SESSION['db'])) {
    $_SESSION['db'] = [
        "loging"   => null,
        "miembros" => []
    ];
}

// Guardar en JSON
function exportarDB($ruta = "data.json") {
    $json = json_encode($_SESSION['db'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($ruta, $json);
}

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? "");
    $password = trim($_POST['password'] ?? "");

    if ($usuario === "" || $password === "") {
        $error = "Debes rellenar todos los campos.";
    } elseif (isset($_SESSION['db']['miembros'][$usuario])) {
        $error = "Ese nombre de usuario ya existe.";
    } else {
        $_SESSION['db']['miembros'][$usuario] = [
            "password" => $password, // manteniendo tu decisión de no hashear
            "libros"   => []
        ];
        exportarDB();
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de usuario</title>
    <link rel="stylesheet" href="libros.css">
</head>
<body>
    <h1>Registro de nuevo usuario</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        Usuario: <input type="text" name="usuario"><br>
        Contraseña: <input type="password" name="password"><br>
        <button type="submit">Registrar</button>
    </form>

    <p><a href="index.php">Volver al inicio</a></p>
</body>
</html>
