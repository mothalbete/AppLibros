<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

$usuarios = obtenerUsuariosConLibros($mysqli);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios y sus libros</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <!-- Barra de navegación superior -->
  <nav class="navbar">
    <div class="nav-left">
      <h1>Usuarios y sus libros</h1>
    </div>
    <div class="nav-right">
      <a href="dashboard.php">Dashboard</a>
      <a href="logout.php">Cerrar sesión</a>
    </div>
  </nav>

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
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</body>
</html>
