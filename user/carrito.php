<?php
session_start();
require_once '../db.php';

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Función para calcular el total del carrito
function calcular_total() {
    $total = 0;
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $producto) {
            $total += $producto['precio'] * $producto['cantidad'];
        }
    }
    return $total;
}

// Eliminar un producto del carrito
if (isset($_GET['eliminar'])) {
    $id_producto = $_GET['eliminar'];
    unset($_SESSION['carrito'][$id_producto]);
    header("Location: carrito.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="categorias.php">Lista de Categorías</a></li>
                <li><a href="carrito.php">Carrito</a></li>
                <li><a href="../logout.php" class="btn">Cerrar Sesión</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1>Carrito de Compras</h1>

        <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['carrito'] as $id_producto => $producto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo $producto['cantidad']; ?></td>
                            <td><?php echo number_format($producto['precio'], 2, ',', '.'); ?>€</td>
                            <td><?php echo number_format($producto['precio'] * $producto['cantidad'], 2, ',', '.'); ?>€</td>
                            <td>
                                <a href="carrito.php?eliminar=<?php echo $id_producto; ?>" class="btn btn-danger">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Total: <?php echo number_format(calcular_total(), 2, ',', '.'); ?>€</h3>
            <a href="finalizar_compra.php" class="btn">Finalizar Compra</a>
        <?php else: ?>
            <p>Tu carrito está vacío.</p>
        <?php endif; ?>
    </div>
    <style>
        /* Estilos generales */
body {
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

/* Cabecera con menú de navegación */
header {
    background-color: #4caf50;
    padding: 10px 0;
}

header nav ul {
    list-style-type: none;
    padding: 0;
    text-align: center;
    margin: 0;
}

header nav ul li {
    display: inline;
    margin-right: 20px;
}

header nav ul li a {
    text-decoration: none;
    color: white;
    font-size: 1.2rem;
    padding: 8px 16px;
    border-radius: 4px;
}

header nav ul li a:hover {
    background-color: #45a049;
}


/* Estilo de la tabla */
.container {
    width: 80%;
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

h1 {
    font-size: 2rem;
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table th, table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

table th {
    background-color: #4caf50;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f2f2f2;
}

.btn {
    text-decoration: none;
    padding: 8px 16px;
    background-color: #4caf50;
    color: white;
    border-radius: 4px;
}

.btn:hover {
    background-color: #45a049;
}

.btn-danger {
    background-color: #e74c3c;
}

.btn-danger:hover {
    background-color: #c0392b;
}

/* Mensaje cuando el carrito está vacío */
p {
    font-size: 1.2rem;
    color: #e74c3c;
}

/* Botón de Cerrar Sesión */
header nav ul li a.btn {
    background-color: #e74c3c;
    padding: 8px 16px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

header nav ul li a.btn:hover {
    background-color: #c0392b;
}


    </style>
</body>
</html>
