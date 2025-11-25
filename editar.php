<?php
session_start();

if (!isset($_SESSION['db']['loging'])) { 
    header("Location: index.php"); 
    exit; 
}

// Función local para guardar cambios en data.json
function exportarDB($ruta = "data.json") {
    $json = json_encode($_SESSION['db'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($ruta, $json);
}

$usuario = $_SESSION['db']['loging'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id === null || !isset($_SESSION['db']['miembros'][$usuario]['libros'][$id])) {
    die("Libro no encontrado.");
}

$libro = $_SESSION['db']['miembros'][$usuario]['libros'][$id];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo   = trim($_POST['titulo'] ?? "");
    $sinopsis = trim($_POST['sinopsis'] ?? "");
    $imagen   = $_POST['imagen'] ?? "";

    if ($titulo === "" || $sinopsis === "") {
        $error = "El título y la sinopsis no pueden estar vacíos.";
    } else {
        $_SESSION['db']['miembros'][$usuario]['libros'][$id] = [
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
<head>
    <meta charset="UTF-8">
    <title>Editar libro</title>
    <link rel="stylesheet" href="acciones.css">
</head>
<body>
<div class="container">
    <h2>Editar libro</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        Título: 
        <input type="text" name="titulo" value="<?= htmlspecialchars($libro['titulo']) ?>"><br>
        Sinopsis: 
        <input type="text" name="sinopsis" value="<?= htmlspecialchars($libro['sinopsis']) ?>"><br>
        Imagen:
        <select name="imagen">
            <?php
            $files = scandir(__DIR__ . "/upload");
            foreach ($files as $file) {
                if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file)) {
                    $selected = ($file === $libro['imagen']) ? "selected" : "";
                    echo "<option value='" . htmlspecialchars($file) . "' $selected>$file</option>";
                }
            }
            ?>
        </select><br>
        <button type="submit" class="primary">Guardar cambios</button>
        <a href="dashboard.php"><button type="button" class="secondary">Cancelar</button></a>
    </form>
</div>
</body>
</html>
