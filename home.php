<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

$autores = $mysqli->query("SELECT DISTINCT autor FROM libros WHERE autor IS NOT NULL AND autor <> ''");
$titulos = $mysqli->query("SELECT DISTINCT titulo FROM libros WHERE titulo IS NOT NULL AND titulo <> ''");
$generos = $mysqli->query("SELECT DISTINCT nombre FROM generos");

$filtroAutor = $_GET['autor'] ?? '';
$filtroTitulo = $_GET['titulo'] ?? '';
$filtroGenero = $_GET['genero'] ?? '';
$recomendacion = isset($_GET['recomendacion']);

$hayFiltros = $filtroAutor || $filtroTitulo || $filtroGenero || $recomendacion;

$librosFiltrados = null;
$libroRecomendado = null;

if ($hayFiltros) {
    if ($recomendacion) {
        $randQuery = "SELECT u.nombre, l.libros_id, l.titulo, l.sinopsis, l.autor, l.portada 
                      FROM usuarios u 
                      INNER JOIN libros l ON u.usuario_id = l.usuarios_usuario_id
                      ORDER BY RAND() LIMIT 1";
        $randResult = $mysqli->query($randQuery);
        $libroRecomendado = $randResult->fetch_assoc();
    } else {
        $query = "SELECT u.nombre, l.libros_id, l.titulo, l.sinopsis, l.autor, l.portada 
                  FROM usuarios u 
                  INNER JOIN libros l ON u.usuario_id = l.usuarios_usuario_id";
        $conditions = [];
        $params = [];
        $types = "";

        if ($filtroAutor) {
            $conditions[] = "l.autor = ?";
            $params[] = $filtroAutor;
            $types .= "s";
        }
        if ($filtroTitulo) {
            $conditions[] = "l.titulo = ?";
            $params[] = $filtroTitulo;
            $types .= "s";
        }
        if ($filtroGenero) {
            $conditions[] = "l.libros_id IN (
                SELECT lg.libro_id 
                FROM libro_genero lg 
                INNER JOIN generos g ON g.genero_id = lg.genero_id 
                WHERE g.nombre = ?
            )";
            $params[] = $filtroGenero;
            $types .= "s";
        }

        if ($conditions) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $mysqli->prepare($query);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $librosFiltrados = $stmt->get_result();
    }
} else {
    $usuarios = obtenerUsuariosConLibros($mysqli);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios y sus libros</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <nav class="navbar">
    <div class="nav-left">
      <h1>Usuarios y sus libros</h1>
    </div>
    <div class="nav-right">
      <a href="dashboard.php">Dashboard</a>
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </nav>

  <!-- Filtros -->
  <form method="get" class="filters">
    <div class="filter-group">
      <label>Filtrar por autor:</label>
      <select name="autor">
        <option value="">-- Todos --</option>
        <?php while ($a = $autores->fetch_assoc()): ?>
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
        <?php while ($t = $titulos->fetch_assoc()): ?>
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
        <?php while ($g = $generos->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($g['nombre']) ?>" <?= $filtroGenero == $g['nombre'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($g['nombre']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="filter-actions">
      <button type="submit">Aplicar filtros</button>
      <a href="home.php" class="btn">Limpiar</a>
      <a href="home.php?recomendacion=1" class="btn">Recomendación aleatoria</a>
    </div>
  </form>

  <!-- Mostrar recomendación o resultados filtrados -->
  <?php if ($hayFiltros): ?>
    <?php if ($libroRecomendado): ?>
      <div class="usuario-libreria">
        <h2>Recomendación aleatoria</h2>
        <div class="carrusel">
          <div class="libro">
            <img src="<?= htmlspecialchars($libroRecomendado['portada']) ?>" alt="Portada">
            <div class="info">
              <h3><?= htmlspecialchars($libroRecomendado['titulo']) ?></h3>
              <p class="sinopsis"><?= htmlspecialchars($libroRecomendado['sinopsis']) ?></p>
              <p class="generos">Géneros: <?= htmlspecialchars(implode(", ", obtenerGenerosDelLibro($libroRecomendado['libros_id'], $mysqli))) ?></p>
              <div class="acciones">
                <em>Subido por <?= htmlspecialchars($libroRecomendado['nombre']) ?></em>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php elseif ($librosFiltrados): ?>
      <div class="usuario-libreria">
        <h2>Resultados filtrados</h2>
        <div class="carrusel">
          <?php while ($libro = $librosFiltrados->fetch_assoc()): ?>
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
    <?php else: ?>
      <p style="text-align:center;">No se encontraron libros con esos criterios.</p>
    <?php endif; ?>
  <?php else: ?>
    <!-- Vista normal de librerías -->
    <?php foreach ($usuarios as $nombre => $libros): ?>
      <div class="usuario-libreria">
        <h2><?= htmlspecialchars($nombre) ?></h2>
        <div class="carrusel">
          <?php foreach ($libros as $libro): ?>
            <?php if (!empty($libro['titulo'])): ?>
              <div class="libro">
                <img src="<?= htmlspecialchars($libro['portada']) ?>" alt="Portada">
                <div class="info">
                  <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
                  <p class="sinopsis"><?= htmlspecialchars($libro['sinopsis']) ?></p>
                  <p class="generos">Géneros: <?= htmlspecialchars(implode(", ", obtenerGenerosDelLibro($libro['libros_id'], $mysqli))) ?></p>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
