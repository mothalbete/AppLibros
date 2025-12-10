<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo   = trim($_POST['titulo'] ?? '');
    $sinopsis = trim($_POST['sinopsis'] ?? '');
    $autor    = trim($_POST['autor'] ?? '');
    $portada  = trim($_POST['portada'] ?? '');
    $usuario_id = $_SESSION['usuario_id'];

    if ($titulo && $sinopsis && $autor) {
        if (publicarLibro($titulo, $sinopsis, $autor, $portada, $usuario_id, $mysqli)) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "No se pudo publicar el libro.";
        }
    } else {
        $error = "Todos los campos salvo portada son obligatorios.";
    }
}
?>
<h1>Publicar nuevo libro</h1>

<?php if (!empty($error)): ?>
  <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<head>
  <link rel="stylesheet" href="styles.css">
</head>
<form method="post">
  <label>Título:</label><br>
  <input type="text" name="titulo" required><br><br>

  <label>Sinopsis:</label><br>
  <textarea name="sinopsis" required rows="5" cols="50"></textarea><br><br>

  <label>Autor:</label><br>
  <input type="text" name="autor" required><br><br>

  <label>Portada (URL):</label><br>
  <input type="url" name="portada" placeholder="https://..."><br><br>

  <button type="submit">Publicar</button>
</form>

<p><a href="dashboard.php">Volver al dashboard</a></p>
