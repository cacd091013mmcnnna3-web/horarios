<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

$rutaConexion = __DIR__ . '/config/conexion.php';
if (!file_exists($rutaConexion)) {
    die('Error: No se encontró config/conexion.php');
}

require_once($rutaConexion);
$db = $conexion ?? $conn ?? $pdo ?? $link ?? null;

if (!$db) {
    die('Error: No hay conexión a la base de datos');
}

// Inicializar la variable de error vacía para evitar avisos de PHP
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_input = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password_input = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($usuario_input) || empty($password_input)) {
        $error = 'Por favor llena todos los campos';
    } else {
        // Buscar el usuario sin importar mayúsculas o minúsculas
        $sql = "SELECT id_usuario, usuario, password, rol FROM usuarios WHERE LOWER(usuario) = LOWER(?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $usuario_input);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($user = $resultado->fetch_assoc()) {
            // Validar si la contraseña coincide (hasheada o texto plano como respaldo)
            $passwordValida = password_verify($password_input, $user['password']) || ($password_input === $user['password']);

            if ($passwordValida) {
                $_SESSION['id_usuario'] = $user['id_usuario'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['rol'] = strtolower(trim($user['rol']));

                // Redirección según el rol
                if ($_SESSION['rol'] === 'admin') {
                    header('Location: index_admin.php');
                } else {
                    header('Location: usuario_index.php');
                }
                exit;
            } else {
                $error = 'Contraseña incorrecta';
            }
        } else {
            $error = 'El usuario no existe (Escribiste: "' . htmlspecialchars($usuario_input) . '")';
        }
        $stmt->close();
    }
}
?>
    
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Universidad Anáhuac</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light p-3">

<div class="card card-comic p-4 text-center shadow" style="max-width: 400px; width: 100%;">
    
    <!-- Logo Institucional -->
    <div class="mb-3">
        <img src="logo.png" alt="Logo Anáhuac" style="max-height: 65px; width: auto; object-fit: contain;">
    </div>

    <h3 class="title-comic mb-3">ACCESO AL SISTEMA</h3>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border border-dark py-2 fw-bold mb-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3 text-start">
            <label class="form-label text-dark fw-bold"><i class="fa-solid fa-user me-1"></i> USUARIO</label>
            <input type="text" name="usuario" class="form-control border-dark fw-bold" placeholder="Ingresa tu usuario" required>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label text-dark fw-bold"><i class="fa-solid fa-lock me-1"></i> CONTRASEÑA</label>
            <input type="password" name="password" class="form-control border-dark fw-bold" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-comic btn-comic-yellow w-100 py-2 mt-2">
            <i class="fa-solid fa-right-to-bracket me-1"></i> ENTRAR
        </button>
    </form>
</div>

</body>
</html>
