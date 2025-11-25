<?php
session_start();
require_once "index.php"; // para usar exportarDB/importarDB

if (!$_SESSION['db']['loging']) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_SESSION['db']['loging'];
    $titulo = trim($_POST['titulo']);
    $sinopsis = trim($_POST['sinopsis']);
    $imagen = $_POST['imagen'];

    if ($titulo === "" || $sinopsis === "") {
        $error = "El título y la sinopsis no pueden estar vacíos.";
    } else {
        $_SESSION['db']['miembros'][$usuario]['libros'][] = [
            "titulo"   => $titulo,
            "sinopsis" => $sinopsis,
            "imagen"   => $imagen
        ];
        exportarDB();
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Crear libro</title>
    <link rel="stylesheet" href="acciones.css">
</head>
<body>
<h2>Crear nuevo libro</h2>
<?php if (!empty($error)): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
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
    <button type="submit">Guardar</button>
</form>
</body>
</html>
