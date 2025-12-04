<?php
require_once 'lib/common.php';
session_start();

$error = '';

if (isLoggedIn()) {
    header('Location: LP.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();

    $usuario = trim($_POST['user'] ?? '');
    $clave = $_POST['clave'] ?? '';

    try {

        $userData = intentaLogin($pdo, $usuario, $clave);

        if ($userData) {

            login(
                $userData['usuario'],
                $userData['nombre'],
                $userData['genero_lit_fav'],
                $userData['fecha_registro'],
                $userData['grade'],
                $userData['email'],
                $userData['id_usr']
            );

            header('Location: LP.php');
            exit();
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $error = 'Error en el sistema. Por favor intenta nuevamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;700&family=Fira+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
    <title>Iniciar Sesión - CbNoticias</title>
</head>

<body>
    <div class="glass-container">
        <form action="login.php" method="POST" class="form" id="loginForm">
            <h2>Iniciar Sesión</h2>

            <?php if ($error): ?>
                <div
                    style="background: var(--error); color: white; padding: 10px; border-radius: 8px; margin-bottom: 1rem;">
                    <?php echo htmlEscape($error); ?>
                </div>
            <?php endif; ?>
            <div class="input-wrapper">
                <input type="text" name="user" placeholder="Usuario"
                    value="<?php echo isset($_POST['user']) ? htmlEscape($_POST['user']) : ''; ?>" required autofocus> //Make this an if statement to tell the user to log out if they already have a session started other wise just show the placeholder
            </div>

            <div class="input-wrapper">
                <input type="password" name="clave" placeholder="Contraseña" required>
            </div>

            <button type="submit" id="submitBtn">Iniciar Sesión</button>

            <p>¿No tienes cuenta? <a href="registrar.php">Regístrate aquí</a></p>

            <p><a href="index.php">← Volver al inicio</a></p>
        </form>
    </div>
</body>

</html>