<?php
session_start();
require_once '../db.php';

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar que los datos del producto y cantidad son válidos
if (isset($_GET['id_producto'], $_GET['cantidad']) && is_numeric($_GET['id_producto']) && is_numeric($_GET['cantidad']) && $_GET['cantidad'] > 0) {
    $id_producto = (int)$_GET['id_producto'];
    $cantidad = (int)$_GET['cantidad'];

    try {
        // Obtener información del producto
        $query = "SELECT id_producto, nombre_producto, precio, stock FROM producto WHERE id_producto = :id_producto";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id_producto' => $id_producto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            if ($producto['stock'] >= $cantidad) {
                // Actualizar el stock del producto
                $nuevoStock = $producto['stock'] - $cantidad;
                $updateQuery = "UPDATE producto SET stock = :stock WHERE id_producto = :id_producto";
                $updateStmt = $pdo->prepare($updateQuery);
                $updateStmt->execute([':stock' => $nuevoStock, ':id_producto' => $id_producto]);

                // Agregar el producto al carrito
                $precio_producto = floatval($producto['precio']); // Asegurar que sea un valor decimal
                if (isset($_SESSION['carrito'][$id_producto])) {
                    $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
                } else {
                    $_SESSION['carrito'][$id_producto] = [
                        'id_producto' => $producto['id_producto'],
                        'nombre' => $producto['nombre_producto'],
                        'precio' => $precio_producto,
                        'cantidad' => $cantidad
                    ];
                }

                $_SESSION['mensaje'] = "Producto agregado al carrito y stock actualizado. Nuevo stock: $nuevoStock.";
                header("Location: carrito.php");
                exit();
            } else {
                $_SESSION['error'] = "Stock insuficiente para este producto.";
                header("Location: detalles_producto.php?id_producto=$id_producto");
                exit();
            }
        } else {
            $_SESSION['error'] = "Producto no encontrado.";
            header("Location: carrito.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error al procesar la solicitud: " . $e->getMessage());
    }
} else {
    $_SESSION['error'] = "Datos del producto o cantidad inválidos.";
    header("Location: carrito.php");
    exit();
}
