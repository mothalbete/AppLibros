<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

$usuario_id = $_SESSION['usuario_id'];

// --- Filtros disponibles ---
$autoresStmt = $mysqli->prepare("SELECT DISTINCT autor FROM libros WHERE usuarios_usuario_id = ?");
$autoresStmt->bind_param("i", $usuario_id);
$autoresStmt->execute();
$autoresResult = $autoresStmt->get_result();

$titulosStmt = $mysqli->prepare("SELECT DISTINCT titulo FROM libros WHERE usuarios_usuario_id = ?");
$titulosStmt->bind_param("i", $usuario_id);
$titulosStmt->execute();
$titulosResult = $titulosStmt->get_result();

$generosStmt = $mysqli->prepare("SELECT DISTINCT g.nombre 
                                 FROM libro_genero lg 
                                 INNER JOIN generos g ON g.genero_id = lg.genero_id
                                 INNER JOIN libros l ON l.libros_id = lg.libro_id
                                 WHERE l.usuarios_usuario_id = ?");
$generosStmt->bind_param("i", $usuario_id);
$generosStmt->execute();
$generosResult = $generosStmt->get_result();

// --- Procesar filtros seleccionados ---
$filtroAutor  = $_GET['autor'] ?? '';
$filtroTitulo = $_GET['titulo'] ?? '';
$filtroGenero = $_GET['genero'] ?? '';

$query = "SELECT * FROM libros WHERE usuarios_usuario_id = ?";
$conditions = [];
$params = [$usuario_id];
$types = "i";

if ($filtroAutor) {
    $conditions[] = "autor = ?";
    $params[] = $filtroAutor;
    $types .= "s";
}
if ($filtroTitulo) {
    $conditions[] = "titulo = ?";
    $params[] = $filtroTitulo;
    $types .= "s";
}
if ($filtroGenero) {
    $conditions[] = "libros_id IN (
        SELECT lg.libro_id 
        FROM libro_genero lg 
        INNER JOIN generos g ON g.genero_id = lg.genero_id 
        WHERE g.nombre = ?
    )";
    $params[] = $filtroGenero;
    $types .= "s";
}

if ($conditions) {
    $query .= " AND " . implode(" AND ", $conditions);
}

$stmt = $mysqli->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$libros = $stmt->get_result();
$totalLibros = $libros->num_rows;
$stmt->close();

// --- Coincidencias por título ---
$queryTitulo = "SELECT u.nombre, l.libros_id, l.titulo, l.sinopsis, l.portada
                FROM libros l
                INNER JOIN usuarios u ON u.usuario_id = l.usuarios_usuario_id
                WHERE l.titulo IN (
                    SELECT titulo FROM libros WHERE usuarios_usuario_id = ?
                )
                AND l.usuarios_usuario_id <> ?";
$stmt = $mysqli->prepare($queryTitulo);
$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();
$coincidenciasTitulo = $stmt->get_result();
$totalCoincidenciasTitulo = $coincidenciasTitulo->num_rows;
$stmt->close();

// --- Coincidencias por autor ---
$queryAutor = "SELECT u.nombre, l.libros_id, l.titulo, l.sinopsis, l.portada, l.autor
               FROM libros l
               INNER JOIN usuarios u ON u.usuario_id = l.usuarios_usuario_id
               WHERE l.autor IN (
                   SELECT autor FROM libros WHERE usuarios_usuario_id = ?
               )
               AND l.usuarios_usuario_id <> ?";
$stmt = $mysqli->prepare($queryAutor);
$stmt->bind_param("ii", $usuario_id, $usuario_id);
$stmt->execute();
$coincidenciasAutor = $stmt->get_result();
$totalCoincidenciasAutor = $coincidenciasAutor->num_rows;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Dashboard</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <nav class="navbar">
    <div class="nav-left">
      <h1>Mi Dashboard</h1>
    </div>
    <div class="nav-right">
      <a href="home.php">Usuarios</a>
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </nav>

  <p><a href="add_book.php">Publicar nuevo libro</a></p>

  <!-- Filtros -->
  <form method="get" class="filters">
    <div class="filter-group">
      <label>Filtrar por autor:</label>
      <select name="autor">
        <option value="">-- Todos --</option>
        <?php while ($a = $autoresResult->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($a['autor']) ?>" <?= $filtroAutor == $a['autor'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($a['autor']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="filter-group">
      <label>Filtrar por título:</label>
      <select name="titulo">
        <option value="">-- Todos --</option>
        <?php while ($t = $titulosResult->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($t['titulo']) ?>" <?= $filtroTitulo == $t['titulo'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['titulo']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="filter-group">
      <label>Filtrar por género:</label>
      <select name="genero">
        <option value="">-- Todos --</option>
        <?php while ($g = $generosResult->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($g['nombre']) ?>" <?= $filtroGenero == $g['nombre'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($g['nombre']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="filter-actions">
      <button type="submit">Aplicar filtros</button>
      <a href="dashboard.php" class="btn">Limpiar</a>
    </div>
  </form>

  <!-- Mis publicaciones -->
  <div class="usuario-libreria">
    <h2>Mis publicaciones (<?= $totalLibros ?>)</h2>
    <div class="carrusel">
      <?php while ($libro = $libros->fetch_assoc()): ?>
        <div class="libro">
          <img src="<?= htmlspecialchars($libro['portada']) ?>" alt="Portada">
          <div class="info">
            <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
            <p class="sinopsis"><?= htmlspecialchars($libro['sinopsis']) ?></p>
            <p class="generos">Géneros: <?= htmlspecialchars(implode(", ", obtenerGenerosDelLibro($libro['libros_id'], $mysqli))) ?></p>
            <div class="acciones">
              <a href="edit_book.php?id=<?= $libro['libros_id'] ?>">Editar</a> |
              <a href="delete_book.php?id=<?= $libro['libros_id'] ?>">Eliminar</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <!-- Coincidencias por título -->
  <?php if ($totalCoincidenciasTitulo > 0): ?>
    <div class="usuario-libreria">
      <h2>Otros usuarios con mis mismos títulos (<?= $totalCoincidenciasTitulo ?>)</h2>
      <div class="carrusel">
        <?php while ($libro = $coincidenciasTitulo->fetch_assoc()): ?>
          <div class="libro">
            <img src="<?= htmlspecialchars($libro['portada']) ?>" alt="Portada">
            <div class="info">
              <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
              <p class="sinopsis"><?= htmlspecialchars($libro['sinopsis']) ?></p>
              <p class="generos">Géneros: <?= htmlspecialchars(implode(", ", obtenerGenerosDelLibro($libro['libros_id'], $mysqli))) ?></p>
              <div class="acciones">
                <em>Subido por <?= htmlspecialchars($libro['nombre']) ?></em>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Coincidencias por autor -->
  <?php if ($totalCoincidenciasAutor > 0): ?>
    <div class="usuario-libreria">
      <h2>Otros usuarios con mis mismos autores (<?= $totalCoincidenciasAutor ?>)</h2>
      <div class="carrusel">
        <?php while ($libro = $coincidenciasAutor->fetch_assoc()): ?>
          <div class="libro">
            <img src="<?= htmlspecialchars($libro['portada']) ?>" alt="Portada">
            <div class="info">
              <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
              <p class="sinopsis"><?= htmlspecialchars($libro['sinopsis']) ?></p>
              <div class="acciones">
                <em><?= htmlspecialchars($libro['autor']) ?> subido por <?= htmlspecialchars($libro['nombre']) ?></em>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>
</body>
</html>
