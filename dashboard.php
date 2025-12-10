<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

$usuario_id = $_SESSION['usuario_id'];
$libros = obtenerLibrosDelUsuario($usuario_id, $mysqli);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mi Dashboard</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <h1>Mis libros</h1>
  <p><a href="add_book.php">Publicar nuevo libro</a></p>

  <div class="usuario-libreria">
    <h2>Mis publicaciones</h2>
    <div class="carrusel">
      <?php while ($libro = $libros->fetch_assoc()): ?>
        <div class="libro">
          <img src="<?= htmlspecialchars($libro['portada']) ?>" alt="Portada">
          <div class="info">
            <h3><?= htmlspecialchars($libro['titulo']) ?></h3>
            <p class="sinopsis"><?= htmlspecialchars($libro['sinopsis']) ?></p>
            <div class="acciones">
              <a href="edit_book.php?id=<?= $libro['libros_id'] ?>">Editar</a> |
              <a href="delete_book.php?id=<?= $libro['libros_id'] ?>">Eliminar</a>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <p style="text-align:center;">
    <a href="home.php">Volver a usuarios</a> |
    <a href="logout.php">Cerrar sesión</a>
  </p>
</body>
</html>
