<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

$usuario_id = $_SESSION['usuario_id'];
$libro_id = (int)($_GET['id'] ?? 0);
$mensaje = "";

// Verificar propiedad del libro
$stmt = $mysqli->prepare("SELECT * FROM libros WHERE libros_id = ? AND usuarios_usuario_id = ?");
$stmt->bind_param("ii", $libro_id, $usuario_id);
$stmt->execute();
$libro = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$libro) {
    header("Location: dashboard.php");
    exit();
}

// Géneros actuales del libro
$generosActuales = [];
$stmt = $mysqli->prepare("SELECT g.genero_id 
                          FROM libro_genero lg 
                          INNER JOIN generos g ON g.genero_id = lg.genero_id 
                          WHERE lg.libro_id = ?");
$stmt->bind_param("i", $libro_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $generosActuales[] = (int)$row['genero_id'];
}
$stmt->close();

// Lista completa de géneros
$generos = $mysqli->query("SELECT genero_id, nombre FROM generos ORDER BY nombre ASC");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo   = trim($_POST['titulo'] ?? "");
    $sinopsis = trim($_POST['sinopsis'] ?? "");
    $autor    = trim($_POST['autor'] ?? "");
    $portada  = trim($_POST['portada'] ?? "");
    $generosSeleccionados = array_map('intval', $_POST['generos'] ?? []);

    if ($titulo === "" || $autor === "") {
        $mensaje = "Título y autor son obligatorios.";
    } else {
        if (editarLibro($libro_id, $titulo, $sinopsis, $autor, $portada, $mysqli)) {
            // Actualizar géneros: borrar y reinsertar
            $del = $mysqli->prepare("DELETE FROM libro_genero WHERE libro_id = ?");
            $del->bind_param("i", $libro_id);
            $del->execute();
            $del->close();

            if (!empty($generosSeleccionados)) {
                $ins = $mysqli->prepare("INSERT INTO libro_genero (libro_id, genero_id) VALUES (?, ?)");
                foreach ($generosSeleccionados as $gid) {
                    $ins->bind_param("ii", $libro_id, $gid);
                    $ins->execute();
                }
                $ins->close();
            }

            header("Location: dashboard.php");
            exit();
        } else {
            $mensaje = "Error al guardar cambios.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar libro</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <nav class="navbar">
    <div class="nav-left"><h1>Editar libro</h1></div>
    <div class="nav-right">
      <a href="dashboard.php">Dashboard</a>
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </nav>

  <?php if ($mensaje): ?>
    <p class="error" style="text-align:center;"><?= htmlspecialchars($mensaje) ?></p>
  <?php endif; ?>

  <form method="post" class="form edit-form">
    <div class="form-group">
      <label>Título</label>
      <input type="text" name="titulo" value="<?= htmlspecialchars($libro['titulo']) ?>" required>
    </div>

    <div class="form-group">
      <label>Autor</label>
      <input type="text" name="autor" value="<?= htmlspecialchars($libro['autor']) ?>" required>
    </div>

    <div class="form-group">
      <label>Sinopsis</label>
      <textarea name="sinopsis" rows="5"><?= htmlspecialchars($libro['sinopsis']) ?></textarea>
    </div>

    <div class="form-group">
      <label>URL de portada</label>
      <input type="url" name="portada" value="<?= htmlspecialchars($libro['portada']) ?>">
    </div>

    <div class="form-group">
      <label>Géneros (puedes seleccionar varios)</label>
      <select name="generos[]" multiple size="6" required>
        <?php while ($g = $generos->fetch_assoc()): ?>
          <option value="<?= (int)$g['genero_id'] ?>" <?= in_array((int)$g['genero_id'], $generosActuales) ? 'selected' : '' ?>>
            <?= htmlspecialchars($g['nombre']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <button type="submit">Guardar</button>
  </form>
</body>
</html>
