<?php
function iniciarSesion($nombre, $password, $mysqli) {
    $stmt = $mysqli->prepare("SELECT usuario_id FROM usuarios WHERE nombre=? AND password=?");
    $stmt->bind_param("ss", $nombre, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['usuario_id'] = $row['usuario_id'];
        $stmt->close();
        return true;
    }

    $stmt->close();
    return false;
}

function registrarUsuario($nombre, $email, $password, $mysqli) {
    $stmt = $mysqli->prepare("SELECT usuario_id FROM usuarios WHERE nombre=?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return "nombre_existente";
    }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT usuario_id FROM usuarios WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        return "email_existente";
    }
    $stmt->close();

    $stmt = $mysqli->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $email, $password);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? "ok" : "error";
}

function obtenerUsuariosConLibros($mysqli) {
    $sql = "SELECT u.nombre, l.libros_id, l.titulo, l.sinopsis, l.portada 
            FROM usuarios u 
            LEFT JOIN libros l ON u.usuario_id = l.usuarios_usuario_id";
    $result = $mysqli->query($sql);

    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[$row['nombre']][] = $row;
    }
    return $usuarios;
}

function obtenerLibrosDelUsuario($usuario_id, $mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM libros WHERE usuarios_usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

function publicarLibro($titulo, $sinopsis, $autor, $portada, $usuario_id, $mysqli) {
    $stmt = $mysqli->prepare("INSERT INTO libros (titulo, sinopsis, autor, portada, usuarios_usuario_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $titulo, $sinopsis, $autor, $portada, $usuario_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function editarLibro($id, $titulo, $sinopsis, $autor, $portada, $mysqli) {
    $stmt = $mysqli->prepare("UPDATE libros SET titulo=?, sinopsis=?, autor=?, portada=? WHERE libros_id=?");
    $stmt->bind_param("ssssi", $titulo, $sinopsis, $autor, $portada, $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function eliminarLibro($id, $mysqli) {
    $stmt = $mysqli->prepare("DELETE FROM libros WHERE libros_id=?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

/* --- NUEVO: géneros --- */
function obtenerGenerosDelLibro($libro_id, $mysqli) {
    $stmt = $mysqli->prepare("SELECT g.nombre 
                              FROM libro_genero lg 
                              INNER JOIN generos g ON g.genero_id = lg.genero_id 
                              WHERE lg.libro_id = ?");
    $stmt->bind_param("i", $libro_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $generos = [];
    while ($row = $result->fetch_assoc()) {
        $generos[] = $row['nombre'];
    }
    $stmt->close();
    return $generos;
}
?>
