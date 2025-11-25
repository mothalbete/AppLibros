<?php
session_start();

if (!isset($_SESSION['db']['loging'])) {
    header("Location: index.php");
    exit;
}

// Funciones para exportar/importar JSON
function exportarDB($ruta = "data.json") {
    $json = json_encode($_SESSION['db'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($ruta, $json);
}

function importarDB($ruta = "data.json") {
    if (file_exists($ruta)) {
        $json = file_get_contents($ruta);
        $data = json_decode($json, true);
        if (is_array($data)) {
            $_SESSION['db'] = $data;
        }
    }
}

$usuario = $_SESSION['db']['loging'];
$libros = $_SESSION['db']['miembros'][$usuario]['libros'];

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] === 'crear') {
        $titulo   = trim($_POST['titulo'] ?? "");
        $sinopsis = trim($_POST['sinopsis'] ?? "");
        $imagen   = $_POST['imagen'] ?? "";

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
    if ($_POST['accion'] === 'guardar') {
        exportarDB();
        $mensaje = "Datos guardados en data.json";
    }
    if ($_POST['accion'] === 'cargar') {
        importarDB();
        $mensaje = "Datos cargados desde data.json";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard de <?= htmlspecialchars($usuario) ?></title>
    <link rel="stylesheet" href="libros.css">
</head>
<body>
    <h1>Dashboard de <?= htmlspecialchars($usuario) ?></h1>

    <!-- Menú de acciones -->
    <nav>
        <a href="index.php">🏠 Volver al inicio</a> |
        <form method="post" action="index.php" style="display:inline;">
            <button type="submit" name="accion" value="logout">Cerrar sesión</button>
        </form>
    </nav>

    <?php if (!empty($mensaje)): ?>
        <p style="color:green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <!-- Formulario de creación de libro -->
    <h2>Registrar nuevo libro</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="accion" value="crear">
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
        <button type="submit" class="primary">Guardar libro</button>
    </form>

    <!-- Botones de guardar/cargar JSON solo para admin -->
    <?php if ($usuario === "admin"): ?>
        <h2>Gestión de datos</h2>
        <form method="post" style="display:inline;">
            <button type="submit" name="accion" value="guardar">💾 Guardar JSON</button>
        </form>
        <form method="post" style="display:inline;">
            <button type="submit" name="accion" value="cargar">📂 Cargar JSON</button>
        </form>
    <?php endif; ?>

    <!-- Lista de libros -->
    <h2>Mis libros</h2>
    <div class="carrusel">
        <?php foreach ($libros as $i => $libro): ?>
            <div class="libro">
                <img src="upload/<?= htmlspecialchars($libro['imagen']) ?>" 
                     alt="<?= htmlspecialchars($libro['titulo']) ?>">
                <div class="overlay">
                    <strong><?= htmlspecialchars($libro['titulo']) ?></strong><br>
                    <?= htmlspecialchars($libro['sinopsis']) ?><br>
                    <a href="editar.php?id=<?= $i ?>">✏️ Editar</a> |
                    <a href="eliminar.php?id=<?= $i ?>">🗑️ Eliminar</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
