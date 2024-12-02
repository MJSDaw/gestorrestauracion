<?php
session_start();
require_once '../db.php';

// Verificar autenticación y permisos
if (!isset($_SESSION['usuario']) || $_SESSION['isAdmin'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Verificar si se pasó un ID de producto válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de producto inválido.");
}

$id_producto = $_GET['id'];

// Obtener los datos del producto actual
try {
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE id_producto = :id");
    $stmt->bindParam(':id', $id_producto);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        die("Producto no encontrado.");
    }
} catch (PDOException $e) {
    die("Error al cargar el producto: " . $e->getMessage());
}

// Obtener las categorías para el select
try {
    $stmt_categoria = $pdo->query("SELECT id_categoria, nombre_categoria FROM categoria");
    $categorias = $stmt_categoria->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar las categorías: " . $e->getMessage());
}

// Actualizar los datos del producto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_producto = htmlspecialchars($_POST['nombre_producto']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $stock = $_POST['stock'];

    try {
        $stmt = $pdo->prepare("UPDATE producto 
                               SET nombre_producto = :nombre_producto, 
                                   descripcion = :descripcion, 
                                   precio = :precio, 
                                   id_categoria = :id_categoria,
                                   stock = :stock 
                               WHERE id_producto = :id_producto");
        $stmt->bindParam(':nombre_producto', $nombre_producto);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':id_producto', $id_producto);
        $stmt->bindParam(':stock', $stock);
        $stmt->execute();

        header("Location: admin_panel.php");  // Redirigir al panel después de la edición
        exit();
    } catch (PDOException $e) {
        die("Error al actualizar el producto: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
</head>
<body>
    <div class="container">
        <h1>Editar Producto</h1>
        <form action="editar_producto.php?id=<?php echo $id_producto; ?>" method="POST">
            <label for="nombre_producto">Nombre del Producto:</label>
            <input type="text" name="nombre_producto" value="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" required>
            
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
            
            <label for="precio">Precio:</label>
            <input type="number" name="precio" value="<?php echo $producto['precio']; ?>" required step="0.01">
            
            <label for="stock">Stock:</label>
            <input type="number" id="stock" name="stock" min="0" value="<?php echo $producto['stock']; ?>" required>


            <label for="id_categoria">Categoría:</label>
            <select name="id_categoria" required>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id_categoria']; ?>" 
                        <?php echo $categoria['id_categoria'] == $producto['id_categoria'] ? 'selected' : ''; ?>>
                        <?php echo $categoria['nombre_categoria']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn">Guardar Cambios</button>
        </form>
        <a href="admin_panel.php" class="btn">Cancelar</a>
    </div>
    <style>
        /* Reutiliza los estilos del panel de administración */
        <?php include 'admin_panel.css'; ?>
    </style>
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    color: #333;
    margin: 0;
    padding: 0;
}

.container {
    width: 80%;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
    color: #4CAF50;
    text-align: center;
    font-size: 2em;
    margin-bottom: 20px;
}

/* Form Styles */
form {
    margin-top: 20px;
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

form label {
    display: block;
    margin: 10px 0 5px;
    font-weight: bold;
}

form input[type="text"],
form input[type="number"],
form textarea,
form select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

form textarea {
    height: 100px;
    resize: vertical;
}

form button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

form button:hover {
    background-color: #45a049;
}

/* Button Styles */
.btn {
    display: inline-block;
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    margin: 10px 0;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
}

.btn:hover {
    background-color: #45a049;
}

.btn-danger {
    background-color: #f44336;
}

.btn-danger:hover {
    background-color: #d32f2f;
}

/* Utility Classes */
.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.success-message {
    color: #4CAF50;
    font-weight: bold;
    margin-top: 10px;
    text-align: center;
}

.error-message {
    color: #f44336;
    font-weight: bold;
    margin-top: 10px;
    text-align: center;
}

    </style>
</body>
</html>
