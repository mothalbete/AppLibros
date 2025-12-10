<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo   = trim($_POST['titulo'] ?? "");
    $sinopsis = trim($_POST['sinopsis'] ?? "");
    $autor    = trim($_POST['autor'] ?? "");
    $portada  = trim($_POST['portada'] ?? "");
    $generosSeleccionados = array_map('intval', $_POST['generos'] ?? []);

    if ($titulo === "" || $autor === "") {
        $mensaje = "Título y autor son obligatorios.";
    } else {
        if (publicarLibro($titulo, $sinopsis, $autor, $portada, $usuario_id, $mysqli)) {
            $libro_id = $mysqli->insert_id;

            // Insertar géneros seleccionados
            if (!empty($generosSeleccionados)) {
                $stmt = $mysqli->prepare("INSERT INTO libro_genero (libro_id, genero_id) VALUES (?, ?)");
                foreach ($generosSeleccionados as $gid) {
                    $stmt->bind_param("ii", $libro_id, $gid);
                    $stmt->execute();
                }
                $stmt->close();
            }

            header("Location: dashboard.php");
            exit();
        } else {
            $mensaje = "Error al publicar el libro.";
        }
    }
}

// Cargar lista de géneros
$generos = $mysqli->query("SELECT genero_id, nombre FROM generos ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Publicar nuevo libro</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <nav class="navbar">
    <div class="nav-left"><h1>Publicar nuevo libro</h1></div>
    <div class="nav-right">
      <a href="home.php">Usuarios</a>
      <a href="dashboard.php">Dashboard</a>
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </nav>

  <?php if ($mensaje): ?>
    <p class="error" style="text-align:center;"><?= htmlspecialchars($mensaje) ?></p>
  <?php endif; ?>

  <form method="post" class="form add-form">
    <div class="form-group">
      <label>Título</label>
      <input type="text" name="titulo" required>
    </div>

    <div class="form-group">
      <label>Autor</label>
      <input type="text" name="autor" required>
    </div>

    <div class="form-group">
      <label>Sinopsis</label>
      <textarea name="sinopsis" rows="5"></textarea>
    </div>

    <div class="form-group">
      <label>URL de portada</label>
      <input type="url" name="portada" placeholder="https://...">
    </div>

    <div class="form-group">
      <label>Géneros (puedes seleccionar varios)</label>
      <select name="generos[]" multiple size="6" required>
        <?php while ($g = $generos->fetch_assoc()): ?>
          <option value="<?= (int)$g['genero_id'] ?>">
            <?= htmlspecialchars($g['nombre']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <button type="submit">Publicar</button>
  </form>
</body>
</html>
