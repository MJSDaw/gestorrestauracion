<?php
session_start();
require_once '../db.php';

// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Obtener lista de categorías
try {
    $stmt = $pdo->query("SELECT id_categoria, nombre_categoria FROM categoria");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar las categorías: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Categorías</title>
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
        <h1>Lista de Categorías</h1>
        <ul>
            <?php foreach ($categorias as $categoria): ?>
                <li><a href="productos_categoria.php?id_categoria=<?php echo $categoria['id_categoria']; ?>"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></a></li>
            <?php endforeach; ?>
        </ul>
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

/* Estilos para la lista de categorías */
ul {
    list-style-type: none;
    padding: 0;
}

ul li {
    margin-bottom: 10px;
}

ul li a {
    text-decoration: none;
    color: #4caf50;
    font-size: 1.2rem;
    display: inline-block;
    padding: 8px 16px;
    border: 2px solid #4caf50;
    border-radius: 4px;
    transition: background-color 0.3s ease, color 0.3s ease;
}

ul li a:hover {
    background-color: #4caf50;
    color: white;
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
