<?php
session_start();
require_once '../db.php';

// Verificar si el carrito tiene productos
if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) == 0) {
    echo "No tienes productos en el carrito.";
    exit();
}

// Verificar si el usuario está autenticado
if (isset($_SESSION['usuario'])) {
    // Obtener el ID del usuario
    $usuario_id = $_SESSION['usuario']; // Asegúrate de tener el id del usuario almacenado en la sesión

    // Calcular el total del carrito
    $total = 0;
    foreach ($_SESSION['carrito'] as $producto) {
        $total += $producto['precio'] * $producto['cantidad'];
    }

    // Obtener el ID del restaurante, si corresponde
    $restaurante_id = isset($_SESSION['restaurante_id']) ? $_SESSION['restaurante_id'] : null; // Si los usuarios no son admins, este campo debería estar en la sesión

    // Crear el pedido
    try {
        $id_pedido = crear_pedido($usuario_id, $total, $restaurante_id);

        // Agregar los detalles del pedido
        agregar_detalles_pedido($id_pedido);

        // Vaciar el carrito
        unset($_SESSION['carrito']);

        // Redirigir al usuario a la página de confirmación
        header("Location: confirmacion_compra.php");
        exit();
    } catch (PDOException $e) {
        die("Error al procesar la compra: " . $e->getMessage());
    }
} else {
    echo "Debes iniciar sesión para finalizar la compra.";
    exit();
}

// Función para crear un pedido
function crear_pedido($usuario_id, $total, $restaurante_id = null) {
    global $pdo;

    $fecha_pedido = date('Y-m-d');
    $hora_pedido = date('H:i:s');
    $estado = 'pendiente'; // El estado puede ser 'pendiente', 'finalizado', etc.

    // Insertar el pedido
    $sql = "INSERT INTO pedido (id_restaurante, fecha_pedido, hora_pedido, estado, total_pedido) 
            VALUES (:restaurante_id, :fecha_pedido, :hora_pedido, :estado, :total_pedido)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':restaurante_id', $restaurante_id, PDO::PARAM_INT);
    $stmt->bindParam(':fecha_pedido', $fecha_pedido, PDO::PARAM_STR);
    $stmt->bindParam(':hora_pedido', $hora_pedido, PDO::PARAM_STR);
    $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
    $stmt->bindParam(':total_pedido', $total, PDO::PARAM_STR);
    $stmt->execute();

    // Obtener el ID del pedido insertado
    $id_pedido = $pdo->lastInsertId();

    // Guardar el ID del pedido en la sesión
    $_SESSION['pedido_id'] = $id_pedido;

    return $id_pedido; // Retorna el ID del pedido
}


// Función para agregar los productos del carrito al pedido
function agregar_detalles_pedido($id_pedido) {
    global $pdo;

    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $producto) {
            if (empty($producto['id_producto'])) {
                echo "Error: El producto no tiene un ID válido.";
                exit();
            }
        
            // Insertar el producto en la base de datos
            $stmt = $pdo->prepare("INSERT INTO detalles_pedido (id_pedido, id_producto, cantidad, precio) VALUES (:pedido_id, :producto_id, :cantidad, :precio)");
            $stmt->bindParam(':pedido_id', $id_pedido, PDO::PARAM_INT);
            $stmt->bindParam(':producto_id', $producto['id_producto'], PDO::PARAM_INT); // Usar 'id_producto' aquí
            $stmt->bindParam(':cantidad', $producto['cantidad'], PDO::PARAM_INT);
            $stmt->bindParam(':precio', $producto['precio'], PDO::PARAM_STR);
            $stmt->execute();
        }
    }
}
?>
