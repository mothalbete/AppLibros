<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

$usuario_id = $_SESSION['usuario_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID de libro inválido.");
}

// Obtener libro y verificar propiedad
$stmt = $mysqli->prepare("SELECT * FROM libros WHERE libros_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$libro = $result->fetch_assoc();
$stmt->close();

if (!$libro) {
    die("Libro no encontrado.");
}
if ((int)$libro['usuarios_usuario_id'] !== (int)$usuario_id) {
    die("No tienes permiso para editar este libro.");
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo   = trim($_POST['titulo'] ?? '');
    $sinopsis = trim($_POST['sinopsis'] ?? '');
    $autor    = trim($_POST['autor'] ?? '');
    $portada  = trim($_POST['portada'] ?? '');

    if ($titulo && $sinopsis && $autor) {
        if (editarLibro($id, $titulo, $sinopsis, $autor, $portada, $mysqli)) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "No se pudieron guardar los cambios.";
        }
    } else {
        $error = "Todos los campos salvo portada son obligatorios.";
    }
}
?>
<h1>Editar libro</h1>

<?php if (!empty($error)): ?>
  <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($libro['portada'])): ?>
  <p>
    <strong>Portada actual:</strong><br>
    <img src="<?= htmlspecialchars($libro['portada']) ?>" alt="Portada" width="160">
  </p>
<?php endif; ?>
<head>
    <link rel="stylesheet" href="styles.css">
</head>
<form method="post">
  <label>Título:</label><br>
  <input type="text" name="titulo" value="<?= htmlspecialchars($libro['titulo']) ?>" required><br><br>

  <label>Sinopsis:</label><br>
  <textarea name="sinopsis" rows="6" cols="60" required><?= htmlspecialchars($libro['sinopsis']) ?></textarea><br><br>

  <label>Autor:</label><br>
  <input type="text" name="autor" value="<?= htmlspecialchars($libro['autor']) ?>" required><br><br>

  <label>Portada (URL):</label><br>
  <input type="url" name="portada" value="<?= htmlspecialchars($libro['portada']) ?>"><br><br>

  <button type="submit">Guardar cambios</button>
</form>

<p><a href="dashboard.php">Volver al dashboard</a></p>
