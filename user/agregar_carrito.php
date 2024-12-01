<?php
session_start();
require_once '../db.php';

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar que el producto y la cantidad están presentes
if (isset($_GET['id_producto']) && isset($_GET['cantidad']) && is_numeric($_GET['id_producto']) && is_numeric($_GET['cantidad'])) {
    $id_producto = $_GET['id_producto'];
    $cantidad = $_GET['cantidad'];

    // Obtener el producto desde la base de datos
    try {
        $stmt = $pdo->prepare("SELECT id_producto, nombre_producto, precio FROM producto WHERE id_producto = :id_producto");
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            // Asegurarse de que el precio se almacene como un decimal
            $precio_producto = floatval($producto['precio']);  // Convertir a float para asegurar que sea un valor decimal

            // Verificar si el producto ya está en el carrito
            if (isset($_SESSION['carrito'][$id_producto])) {
                $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
            } else {
                $_SESSION['carrito'][$id_producto] = [
                    'id_producto' => $producto['id_producto'], // Asegurarse de que sea id_producto
                    'nombre' => $producto['nombre_producto'],
                    'precio' => $precio_producto,  // Usar el valor decimal
                    'cantidad' => $cantidad
                ];
            }
            header("Location: carrito.php");
        } else {
            echo "Producto no encontrado.";
        }
    } catch (PDOException $e) {
        die("Error al agregar el producto al carrito: " . $e->getMessage());
    }
} else {
    echo "Datos del producto o cantidad inválidos.";
}
?>
