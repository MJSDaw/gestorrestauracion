<?php
session_start();
require_once '../db.php';

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Obtener el id_categoria desde la URL
if (isset($_GET['id_categoria']) && is_numeric($_GET['id_categoria'])) {
    $id_categoria = $_GET['id_categoria'];
} else {
    // Si no hay id_categoria válido, redirigir a categorías
    header("Location: categorias.php");
    exit();
}

// Obtener productos de la categoría seleccionada
try {
    $stmt = $pdo->prepare("SELECT id_producto, nombre_producto, descripcion, precio FROM producto WHERE id_categoria = :id_categoria");
    $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar los productos: " . $e->getMessage());
}

// Obtener el nombre de la categoría para mostrarlo
try {
    $stmt_categoria = $pdo->prepare("SELECT nombre_categoria FROM categoria WHERE id_categoria = :id_categoria");
    $stmt_categoria->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $stmt_categoria->execute();
    $categoria = $stmt_categoria->fetch(PDO::FETCH_ASSOC);
    $categoria_nombre = $categoria['nombre_categoria'];
} catch (PDOException $e) {
    die("Error al cargar el nombre de la categoría: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos de la Categoría: <?php echo htmlspecialchars($categoria_nombre); ?></title>
    <link rel="stylesheet" href="../styles.css">
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
        <h1>Productos de la Categoría: <?php echo htmlspecialchars($categoria_nombre); ?></h1>

        <?php if (count($productos) > 0): ?>
            <ul>
                <?php foreach ($productos as $producto): ?>
                    <li>
                        <h2><?php echo htmlspecialchars($producto['nombre_producto']); ?></h2>
                        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        <p>Precio: $<?php echo number_format($producto['precio'], 2, ',', '.'); ?></p>
                        <a href="detalles_producto.php?id_producto=<?php echo $producto['id_producto']; ?>" class="btn">Ver Detalles</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay productos disponibles en esta categoría.</p>
        <?php endif; ?>
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

/* Estilos para la lista de productos */
ul {
    list-style-type: none;
    padding: 0;
}

ul li {
    margin-bottom: 20px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 15px;
}

ul li h2 {
    font-size: 1.5rem;
    color: #333;
}

ul li p {
    font-size: 1rem;
    color: #555;
}

ul li a {
    text-decoration: none;
    background-color: #4caf50;
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    font-size: 1.1rem;
    transition: background-color 0.3s ease;
}

ul li a:hover {
    background-color: #45a049;
}

/* Estilos para el mensaje cuando no hay productos */
.container p {
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
