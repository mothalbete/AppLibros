<?php
session_start();

function importarDB($ruta = "data.json") {
    if (file_exists($ruta)) {
        $json = file_get_contents($ruta);
        $data = json_decode($json, true);
        if (is_array($data)) {
            $_SESSION['db'] = $data;
        }
    }
}

function exportarDB($ruta = "data.json") {
    $json = json_encode($_SESSION['db'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($ruta, $json);
}

// Reinicializar si no existe
if (!isset($_SESSION['db'])) {
    importarDB();
    if (!isset($_SESSION['db'])) {
        $_SESSION['db'] = ["loging" => null, "miembros" => []];
    }
}

// Funciones
function login($usuario, $password) {
    if (isset($_SESSION['db']['miembros'][$usuario]) &&
        $_SESSION['db']['miembros'][$usuario]['password'] === $password) {
        $_SESSION['db']['loging'] = $usuario;
        return true;
    }
    return false;
}

function logout() {
    $_SESSION['db']['loging'] = null;
}

function registrarLibro($usuario, $titulo, $sinopsis, $imagen) {
    if (trim($titulo) === "" || trim($sinopsis) === "") {
        return "El título y la sinopsis no pueden estar vacíos.";
    }
    $_SESSION['db']['miembros'][$usuario]['libros'][] = [
        "titulo"   => $titulo,
        "sinopsis" => $sinopsis,
        "imagen"   => $imagen
    ];
    exportarDB();
    return null;
}

// Procesar formularios
if (isset($_POST['accion'])) {
    if ($_POST['accion'] === 'login') {
        login($_POST['usuario'], $_POST['password']);
    }
    if ($_POST['accion'] === 'logout') {
        logout();
    }
    if ($_POST['accion'] === 'libro' && $_SESSION['db']['loging']) {
        $errorLibro = registrarLibro($_SESSION['db']['loging'], $_POST['titulo'], $_POST['sinopsis'], $_POST['imagen']);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>AppLibros</title>
    <link rel="stylesheet" href="libros.css">
</head>
<body>
    <h1>AppLibros</h1>

    <?php if (!$_SESSION['db']['loging']): ?>
        <h2>Iniciar sesión</h2>
        <form method="post">
            Usuario: <input type="text" name="usuario"><br>
            Contraseña: <input type="password" name="password"><br>
            <button type="submit" name="accion" value="login">Entrar</button>
        </form>
        <form action="registro.php" method="get">
            <button type="submit">Registrarse</button>
        </form>
    <?php else: ?>
        <p>
            Usuario conectado: <b><?= $_SESSION['db']['loging'] ?></b>
            <a href="dashboard.php">Ir al Dashboard</a>
        </p>
        <form method="post">
            <button type="submit" name="accion" value="logout">Cerrar sesión</button>
        </form>

        <h2>Registrar nuevo libro</h2>
        <?php if (!empty($errorLibro)): ?>
            <p style="color:red;"><?= htmlspecialchars($errorLibro) ?></p>
        <?php endif; ?>
        <form method="post">
            Título: <input type="text" name="titulo"><br>
            Sinopsis: <input type="text" name="sinopsis"><br>
            Imagen:
            <select name="imagen">
                <?php
                $files = scandir(__DIR__ . "/upload");
                foreach ($files as $file) {
                    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                        echo "<option value='" . htmlspecialchars($file) . "'>$file</option>";
                    }
                }
                ?>
            </select><br>
            <button type="submit" name="accion" value="libro">Guardar libro</button>
        </form>
    <?php endif; ?>

    <h2>Libros registrados por usuario</h2>
    <?php foreach ($_SESSION['db']['miembros'] as $usuario => $datos): ?>
        <h3><?= htmlspecialchars($usuario) ?></h3>
        <div class="carrusel">
            <?php foreach ($datos['libros'] as $libro): ?>
                <div class="libro">
                    <img src="upload/<?= htmlspecialchars($libro['imagen']) ?>" alt="<?= htmlspecialchars($libro['titulo']) ?>">
                    <div class="overlay">
                        <strong><?= htmlspecialchars($libro['titulo']) ?></strong><br>
                        <?= htmlspecialchars($libro['sinopsis']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
