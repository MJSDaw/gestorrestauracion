<?php
session_start();
require_once '../db.php';

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['isAdmin'] != 1) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = htmlspecialchars($_POST['usuario']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $isAdmin = isset($_POST['isAdmin']) ? 1 : 0;
    $restaurante_id = null;

    // Verificar si el nombre de usuario ya está registrado
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario = :usuario");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    $usuarioExistente = $stmt->fetchColumn();

    if ($usuarioExistente > 0) {
        die("El nombre de usuario ya está registrado.");
    }

    // Si el usuario no es administrador, crear un nuevo restaurante y asociarlo al usuario
    if (!$isAdmin) {
        // Verificar si el restaurante ya existe
        $restaurante_nombre = htmlspecialchars($_POST['restaurante_nombre']);
        $restaurante_ubicacion = htmlspecialchars($_POST['restaurante_ubicacion']);
        $restaurante_correo = htmlspecialchars($_POST['restaurante_correo']);
        
        // Verificar si ya existe un restaurante con el mismo nombre o correo
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM restaurante WHERE nombre_restaurante = :nombre_restaurante OR correo = :correo");
        $stmt->bindParam(':nombre_restaurante', $restaurante_nombre);
        $stmt->bindParam(':correo', $restaurante_correo);
        $stmt->execute();
        $restauranteExistente = $stmt->fetchColumn();

        if ($restauranteExistente > 0) {
            die("El restaurante con ese nombre o correo ya está registrado.");
        }

        // Insertar el restaurante en la base de datos
        try {
            $stmt = $pdo->prepare("INSERT INTO restaurante (nombre_restaurante, ubicacion, correo) VALUES (:nombre, :ubicacion, :correo)");
            $stmt->bindParam(':nombre', $restaurante_nombre);
            $stmt->bindParam(':ubicacion', $restaurante_ubicacion);
            $stmt->bindParam(':correo', $restaurante_correo);
            $stmt->execute();
            // Obtener el ID del restaurante recién creado
            $restaurante_id = $pdo->lastInsertId();
        } catch (PDOException $e) {
            die("Error al crear el restaurante: " . $e->getMessage());
        }
    }

    // Insertar el usuario en la base de datos
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, isAdmin, id_restaurante) VALUES (:usuario, :password, :isAdmin, :id_restaurante)");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':password', $contrasena);
        $stmt->bindParam(':isAdmin', $isAdmin);
        $stmt->bindParam(':id_restaurante', $restaurante_id); // Puede ser NULL si es admin
        $stmt->execute();

        echo "Usuario registrado exitosamente.";
    } catch (PDOException $e) {
        die("Error al registrar el usuario: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <h1>Registrar Usuario</h1>
        <form action="register.php" method="POST">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required><br>

            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required><br>

            <label for="isAdmin">¿Es Administrador?</label>
            <input type="checkbox" id="isAdmin" name="isAdmin"><br>

            <!-- Mostrar campo de restaurante solo si no es administrador -->
            <div id="restauranteField" >
                <label for="restaurante_nombre">Nombre del Restaurante:</label>
                <input type="text" id="restaurante_nombre" name="restaurante_nombre" required><br>
                
                <label for="restaurante_ubicacion">Ubicacion del Restaurante:</label>
                <input type="text" id="restaurante_ubicacion" name="restaurante_ubicacion"><br>
                
                <label for="restaurante_correo">Correo del Restaurante:</label>
                <input type="mail" id="restaurante_correo" name="restaurante_correo" required><br>
            </div>

            <!-- Botón para enviar el formulario -->
            <button type="submit">Registrar</button>
        </form>
        <a href="admin_panel.php">Volver</a>
    </div>

    <script>
        // Mostrar el campo de restaurante solo si no es administrador
        document.getElementById('isAdmin').addEventListener('change', function() {
            var restauranteField = document.getElementById('restauranteField');
            if (this.checked) {
                restauranteField.style.display = 'none';
            } else {
                restauranteField.style.display = 'block';
            }
        });
    </script>
    <style>
        /* General */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    width: 50%;
    margin: 50px auto;
    padding: 20px;
    background-color: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

h1 {
    text-align: center;
    color: #333;
    margin-bottom: 20px;
}

/* Estilos del formulario */
form {
    display: flex;
    flex-direction: column;
}

label {
    font-size: 1rem;
    margin-bottom: 8px;
    color: #333;
}

input[type="text"],
input[type="mail"],
input[type="password"],
input[type="checkbox"] {
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

input[type="checkbox"] {
    width: auto;
    margin-top: 10px;
}

button {
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #218838;
}

a {
    display: block;
    text-align: center;
    margin-top: 20px;
    color: #007bff;
    text-decoration: none;
    font-size: 1rem;
}

a:hover {
    text-decoration: underline;
}

/* Estilos de los campos de restaurante */
#restauranteField {
    margin-top: 20px;
}

/* Respuestas a la selección de Administrador */
input[type="checkbox"]:checked + #restauranteField {
    display: none;
}

    </style>
</body>
</html>
