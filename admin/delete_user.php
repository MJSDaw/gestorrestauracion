<?php
session_start();
require_once '../db.php';

// Verificar que el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['isAdmin'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Verificar si se ha enviado el ID del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id_usuario = (int) $_POST['id'];

    // No permitir que un administrador se elimine a sí mismo
    if ($_SESSION['id'] == $id_usuario) {
        header("Location: admin_panel.php?error=No puedes eliminarte a ti mismo");
        exit();
    }

    try {
        // Eliminar usuario
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id_usuario");
        $stmt->execute([':id_usuario' => $id_usuario]);

        header("Location: admin_panel.php?success=Usuario eliminado");
        exit();
    } catch (PDOException $e) {
        header("Location: admin_panel.php?error=Error al eliminar el usuario");
        exit();
    }
} else {
    header("Location: admin_panel.php");
    exit();
}
