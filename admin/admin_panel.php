<?php
session_start();
require_once '../db.php';

// Verificar que el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['isAdmin'] != 1) {
    header("Location: ../login.php");
    exit();
}

// Obtener lista de usuarios
try {
    $stmt = $pdo->query("SELECT id, usuario, isAdmin, id_restaurante FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar los usuarios: " . $e->getMessage());
}

// Obtener lista de categorías
try {
    $stmt_categoria = $pdo->query("SELECT id_categoria, nombre_categoria FROM categoria");
    $categorias = $stmt_categoria->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar las categorías: " . $e->getMessage());
}

// Obtener lista de productos
try {
    $stmt_producto = $pdo->query("SELECT id_producto, nombre_producto, precio, descripcion, nombre_categoria, stock 
                                  FROM producto p
                                  JOIN categoria c ON p.id_categoria = c.id_categoria");
    $productos = $stmt_producto->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar los productos: " . $e->getMessage());
}

// Agregar un nuevo producto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre_producto'], $_POST['precio'], $_POST['id_categoria'])) {
    $nombre_producto = htmlspecialchars($_POST['nombre_producto']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    $stock = $_POST['stock'];

    try {
        $stmt = $pdo->prepare("INSERT INTO producto (nombre_producto, descripcion, precio, id_categoria, stock) 
                               VALUES (:nombre_producto, :descripcion, :precio, :id_categoria, :stock)");
        $stmt->bindParam(':nombre_producto', $nombre_producto);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':stock', $stock);
        $stmt->execute();
        header("Location: admin_panel.php");  // Recargar la página después de agregar
        exit();
    } catch (PDOException $e) {
        die("Error al agregar el producto: " . $e->getMessage());
    }
}

// Eliminar un producto
if (isset($_POST['id_producto'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM producto WHERE id_producto = :id_producto");
        $stmt->bindParam(':id_producto', $_POST['id_producto']);
        $stmt->execute();
        header("Location: admin_panel.php");  // Recargar la página después de eliminar
        exit();
    } catch (PDOException $e) {
        die("Error al eliminar el producto: " . $e->getMessage());
    }
}

// Agregar una nueva categoría
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre_categoria'])) {
    $nombre_categoria = htmlspecialchars($_POST['nombre_categoria']);

    try {
        $stmt = $pdo->prepare("INSERT INTO categoria (nombre_categoria) VALUES (:nombre_categoria)");
        $stmt->bindParam(':nombre_categoria', $nombre_categoria);
        $stmt->execute();
        header("Location: admin_panel.php");  // Recargar la página después de agregar
        exit();
    } catch (PDOException $e) {
        die("Error al agregar la categoría: " . $e->getMessage());
    }
}

// Eliminar categoría
if (isset($_POST['id_categoria'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM categoria WHERE id_categoria = :id_categoria");
        $stmt->bindParam(':id_categoria', $_POST['id_categoria']);
        $stmt->execute();
        header("Location: admin_panel.php");  // Recargar la página después de eliminar
        exit();
    } catch (PDOException $e) {
        die("Error al eliminar la categoría: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
</head>
<body>
    <div class="container">
        <h1>Panel de Administración</h1>
        <p>Bienvenido, <?php echo $_SESSION['usuario']; ?></p>

        <h2>Gestión de Usuarios</h2>
        <a href="register.php" class="btn">Registrar Usuario</a>
        <h3>Lista de Usuarios</h3>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>¿Es Administrador?</th>
                    <th>Restaurante</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                        <td><?php echo $usuario['isAdmin'] ? 'Sí' : 'No'; ?></td>
                        <td>
                            <?php 
                            if ($usuario['id_restaurante']) {
                                // Preparamos la consulta para obtener el nombre del restaurante
                                $stmt = $pdo->prepare("SELECT nombre_restaurante FROM restaurante WHERE id_restaurante = :id");
                                $stmt->bindParam(':id', $usuario['id_restaurante']);
                                $stmt->execute();
                                $restaurante = $stmt->fetch(PDO::FETCH_ASSOC);
                                
                                // Si se encuentra el restaurante, lo mostramos
                                echo htmlspecialchars($restaurante['nombre_restaurante']);
                            } else {
                                // Si el usuario no tiene restaurante, mostramos "N/A"
                                echo "N/A";
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($_SESSION['usuario'] !== $usuario['usuario']): ?>
                                <form action="delete_user.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            <?php else: ?>
                                <span>No se puede eliminar</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Gestión de Categorías</h2>
        <h3>Añadir Nueva Categoría</h3>
        <form action="admin_panel.php" method="POST">
            <label for="nombre_categoria">Nombre de la Categoría:</label>
            <input type="text" name="nombre_categoria" required>
            <button type="submit" class="btn">Añadir Categoría</button>
        </form>

        <h3>Lista de Categorías</h3>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></td>
                        <td>
                            <form action="admin_panel.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta categoría?');">
                                <input type="hidden" name="id_categoria" value="<?php echo $categoria['id_categoria']; ?>">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Gestión de Productos</h2>
        <h3>Añadir Nuevo Producto</h3>
        <form action="admin_panel.php" method="POST">
            <label for="nombre_producto">Nombre del Producto:</label>
            <input type="text" name="nombre_producto" required>
            
            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion"></textarea>
            
            <label for="precio">Precio:</label>
            <input type="number" name="precio" required step="0.01">
            
            <label for="stock">Stock:</label>
            <input type="number" name="stock" required>
            
            <label for="id_categoria">Categoría:</label>
            <select name="id_categoria" required>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?php echo $categoria['id_categoria']; ?>"><?php echo $categoria['nombre_categoria']; ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="btn">Añadir Producto</button>
        </form>

        <h3>Lista de Productos</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                        <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                        <td><?php echo $producto['precio']; ?></td>
                        <td><?php echo htmlspecialchars($producto['nombre_categoria']); ?></td>
                        <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                        <td>
                            <form action="admin_panel.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                            <a href="editar_producto.php?id=<?php echo $producto['id_producto']; ?>" class="btn">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="../logout.php" class="btn">Cerrar Sesión</a>
    </div>
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

h1, h2, h3 {
    color: #4CAF50;
    text-align: center;
}

h1 {
    font-size: 2em;
    margin-bottom: 20px;
}

h2 {
    font-size: 1.5em;
    margin-top: 30px;
}

h3 {
    font-size: 1.2em;
    margin-top: 20px;
}

/* Table Styles */
table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
}

th, td {
    padding: 10px;
    text-align: left;
    border: 1px solid #ddd;
}

th {
    background-color: #4CAF50;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
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
}

form input[type="text"],
form input[type="number"],
form select,
form textarea {
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
    width: auto;
    margin-top: 10px;
}

/* Confirm Delete Prompt */
form[onsubmit] {
    display: inline;
}

/* Utility Classes */
.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

    </style>
</body>
</html>
