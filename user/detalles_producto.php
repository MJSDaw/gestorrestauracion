<?php
session_start();
require_once '../db.php';

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Verificar que el producto existe
if (isset($_GET['id_producto']) && is_numeric($_GET['id_producto'])) {
    $id_producto = $_GET['id_producto'];

    // Obtener los detalles del producto desde la base de datos
    try {
        $stmt = $pdo->prepare("SELECT id_producto, nombre_producto, descripcion, precio FROM producto WHERE id_producto = :id_producto");
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            echo "Producto no encontrado.";
            exit();
        }
    } catch (PDOException $e) {
        die("Error al obtener los detalles del producto: " . $e->getMessage());
    }
} else {
    echo "ID de producto no válido.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Producto</title>
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
        <h1>Detalles del Producto</h1>

        <div class="producto-detalles">
            <h2><?php echo htmlspecialchars($producto['nombre_producto']); ?></h2>
            <p><strong>Descripción:</strong> <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
            <p><strong>Precio:</strong> <?php echo number_format($producto['precio'], 2, ',', '.'); ?>€</p>

            <form action="agregar_carrito.php" method="GET">
                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" name="cantidad" value="1" min="1" required>
                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                <button type="submit" class="btn">Añadir al Carrito</button>
            </form>
        </div>
    </div>
    <style>
        /* Estilos generales para el cuerpo */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
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

/* Contenedor principal */
.container {
    width: 80%;
    max-width: 900px;
    margin: 20px auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h1 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 20px;
}

/* Detalles del producto */
.producto-detalles {
    margin-top: 20px;
}

.producto-detalles h2 {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 10px;
}

.producto-detalles p {
    font-size: 1.2rem;
    color: #555;
    margin-bottom: 15px;
}

.producto-detalles label {
    font-size: 1.1rem;
    margin-right: 10px;
}

.producto-detalles input[type="number"] {
    padding: 8px;
    font-size: 1rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    width: 60px;
    margin-bottom: 20px;
}

.producto-detalles button {
    background-color: #4caf50;
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 1.1rem;
    cursor: pointer;
    border: none;
    transition: background-color 0.3s ease;
}

.producto-detalles button:hover {
    background-color: #45a049;
}

/* Estilo para el mensaje de error */
.error-message {
    color: #e74c3c;
    font-size: 1.2rem;
    margin-top: 20px;
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
