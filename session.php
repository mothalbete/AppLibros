<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<head>
    <link rel="stylesheet" href="styles.css">
</head>
