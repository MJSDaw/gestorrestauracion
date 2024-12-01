<?php
session_start();
require_once '../db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}

// Obtener el ID del usuario
$usuario_id = $_SESSION['usuario'];

// Verificar si la compra fue procesada correctamente
if (isset($_SESSION['pedido_id'])) {
    $id_pedido = $_SESSION['pedido_id'];

    try {
        // Obtener el pedido y detalles del pedido
        $stmt = $pdo->prepare("SELECT p.id_pedido, p.fecha_pedido, p.hora_pedido, p.total_pedido, p.estado, r.nombre_restaurante
                               FROM pedido p
                               LEFT JOIN restaurante r ON p.id_restaurante = r.id_restaurante
                               WHERE p.id_pedido = :id_pedido");
        $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
        $stmt->execute();
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pedido) {
            // Obtener los detalles de los productos del pedido
            $stmt = $pdo->prepare("SELECT p.id_producto, p.nombre_producto, dp.cantidad, dp.precio
                                   FROM detalles_pedido dp
                                   JOIN producto p ON dp.id_producto = p.id_producto
                                   WHERE dp.id_pedido = :id_pedido");
            $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
            $stmt->execute();
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            echo "Pedido no encontrado.";
            exit();
        }
    } catch (PDOException $e) {
        die("Error al obtener los detalles de la compra: " . $e->getMessage());
    }
} else {
    echo "No se ha encontrado un pedido válido.";
    exit();
}

if (isset($_POST['descargar_pdf'])) {
    // Generar PDF con los detalles del pedido
    require('../libs/FPDF-master/fpdf.php');

    // Clase PDF
class PDF extends FPDF {
    function Header() {
        // Usar una fuente compatible con UTF-8
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Confirmacion de Pedido', 0, 1, 'C');
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    function Body($pedido, $productos) {
        // Usar fuente compatible con UTF-8
        $this->SetFont('Arial', '', 12);

        // Imprimir detalles del pedido
        $this->Cell(0, 10, 'Pedido ID: ' . $pedido['id_pedido'], 0, 1);
        $this->Cell(0, 10, 'Restaurante: ' . utf8_decode($pedido['nombre_restaurante']), 0, 1);
        $this->Cell(0, 10, 'Fecha: ' . $pedido['fecha_pedido'], 0, 1);
        $this->Cell(0, 10, 'Hora: ' . $pedido['hora_pedido'], 0, 1);
        $this->Cell(0, 10, 'Estado: ' . utf8_decode($pedido['estado']), 0, 1);
        $this->Cell(0, 10, 'Total: $' . number_format($pedido['total_pedido'], 2), 0, 1);

        $this->Ln(10);  // Añadir un salto de línea

        // Título de los detalles
        $this->Cell(60, 10, 'Producto', 1, 0, 'C');
        $this->Cell(40, 10, 'Cantidad', 1, 0, 'C');
        $this->Cell(40, 10, 'Precio', 1, 0, 'C');
        $this->Cell(40, 10, 'Total', 1, 1, 'C');

        // Detalles de los productos
        foreach ($productos as $producto) {
            $this->Cell(60, 10, utf8_decode($producto['nombre_producto']), 1);
            $this->Cell(40, 10, $producto['cantidad'], 1, 0, 'C');
            $this->Cell(40, 10, '$' . number_format($producto['precio'], 2), 1, 0, 'C');
            $this->Cell(40, 10, '$' . number_format($producto['precio'] * $producto['cantidad'], 2), 1, 1, 'C');
        }
    }
}

// Crear el PDF
$pdf = new PDF();
$pdf->AddPage();

// Llamar a la función para mostrar el contenido del pedido
$pdf->Body($pedido, $productos);

// Descargar el archivo PDF
$pdf->Output('D', 'pedido_' . $pedido['id_pedido'] . '.pdf');
exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Compra</title>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">¡Compra realizada con éxito!</h2>

        <p><strong>Pedido #:</strong> <?php echo $pedido['id_pedido']; ?></p>
        <p><strong>Fecha de Pedido:</strong> <?php echo $pedido['fecha_pedido']; ?></p>
        <p><strong>Hora de Pedido:</strong> <?php echo $pedido['hora_pedido']; ?></p>
        <p><strong>Restaurante:</strong> <?php echo $pedido['nombre_restaurante'] ? $pedido['nombre_restaurante'] : 'No especificado'; ?></p>
        <p><strong>Total:</strong> $<?php echo number_format($pedido['total_pedido'], 2); ?></p>

        <h4>Detalles del Pedido:</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo $producto['nombre_producto']; ?></td>
                        <td><?php echo $producto['cantidad']; ?></td>
                        <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                        <td>$<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="text-center">
            <a href="categorias.php" class="btn btn-primary">Volver a la tienda</a>
        </p>

        <!-- Formulario para descargar PDF -->
        <form action="confirmacion_compra.php" method="post">
            <button type="submit" name="descargar_pdf" class="btn btn-success">Descargar PDF</button>
        </form>
    </div>
    <style>
        /* Estilo general para la página */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 100vh;
    box-sizing: border-box;
}

/* Contenedor principal */
.container {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
}

/* Títulos */
h2 {
    font-size: 2rem;
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

h4 {
    font-size: 1.5rem;
    margin-top: 20px;
    color: #333;
}

/* Párrafos */
p {
    font-size: 1rem;
    color: #333;
    margin-bottom: 10px;
}

/* Tabla */
table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
}

table th,
table td {
    text-align: center;
    padding: 10px;
}

table th {
    background-color: #4caf50;
    color: white;
    font-size: 1rem;
}

table td {
    background-color: #f9f9f9;
    font-size: 1rem;
}

table tr:nth-child(even) td {
    background-color: #f1f1f1;
}

/* Botones */
.btn {
    width: 100%;
    padding: 12px;
    background-color: #4caf50;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    text-align: center;
    margin-top: 20px;
}

.btn:hover {
    background-color: #45a049;
}

/* Formulario para descargar PDF */
form {
    text-align: center;
    margin-top: 30px;
}

    </style>
</body>
</html>
