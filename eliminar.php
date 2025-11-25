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
    unset($_SESSION['db']['miembros'][$usuario]['libros'][$id]);
    $_SESSION['db']['miembros'][$usuario]['libros'] = array_values($_SESSION['db']['miembros'][$usuario]['libros']); // reindexar
    exportarDB();
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar libro</title>
    <link rel="stylesheet" href="acciones.css">
</head>
<body>
<div class="container">
    <h2>¿Seguro que quieres eliminar este libro?</h2>
    <p><strong><?= htmlspecialchars($libro['titulo']) ?></strong></p>
    <form method="post">
        <button type="submit" class="danger">Sí, eliminar</button>
        <a href="dashboard.php"><button type="button" class="secondary">Cancelar</button></a>
    </form>
</div>
</body>
</html>
