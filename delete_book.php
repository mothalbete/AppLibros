<?php
// delete_book.php
require_once("session.php");
require_once("config.php");

$usuario_id = $_SESSION['usuario_id'];
$libro_id = (int)($_GET['id'] ?? 0);

// Verificar propiedad del libro
$stmt = $mysqli->prepare("SELECT libros_id FROM libros WHERE libros_id = ? AND usuarios_usuario_id = ?");
$stmt->bind_param("ii", $libro_id, $usuario_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if (!$res || $res->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

// Borrar relaciones de géneros (ON DELETE CASCADE cubriría, pero por seguridad):
$delRel = $mysqli->prepare("DELETE FROM libro_genero WHERE libro_id = ?");
$delRel->bind_param("i", $libro_id);
$delRel->execute();
$delRel->close();

// Borrar libro
$del = $mysqli->prepare("DELETE FROM libros WHERE libros_id = ?");
$del->bind_param("i", $libro_id);
$del->execute();
$del->close();

header("Location: dashboard.php");
exit();
