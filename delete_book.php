<?php
require_once("session.php");
require_once("config.php");
require_once("functions.php");

$usuario_id = $_SESSION['usuario_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID de libro inválido.");
}

// Verificar que el libro pertenece al usuario
$stmt = $mysqli->prepare("SELECT usuarios_usuario_id FROM libros WHERE libros_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die("Libro no encontrado.");
}
if ((int)$row['usuarios_usuario_id'] !== (int)$usuario_id) {
    die("No tienes permiso para eliminar este libro.");
}

if (eliminarLibro($id, $mysqli)) {
    header("Location: dashboard.php");
    exit();
} else {
    die("No se pudo eliminar el libro.");
}
