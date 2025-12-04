<?php
require_once 'lib/common.php';
session_start();

$error = '';
$isAlreadyLoggedIn = isLoggedIn();

// Solo procesar el login si el usuario NO está ya logueado
if (!$isAlreadyLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
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
        <?php if ($isAlreadyLoggedIn): ?>
            <!-- Usuario ya tiene sesión iniciada - mostrar mensaje para cerrar sesión -->
            <div class="form" id="alreadyLoggedIn">
                <h2>¡Ya tienes una sesión activa!</h2>
                <div style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 152, 0, 0.2)); 
                            border: 1px solid rgba(255, 193, 7, 0.5); 
                            color: #fff; 
                            padding: 15px; 
                            border-radius: 12px; 
                            margin-bottom: 1.5rem;
                            text-align: center;">
                    <p style="margin: 0 0 10px 0; font-size: 1.1rem;">
                        👋 Hola, <strong><?php echo htmlEscape($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Usuario'); ?></strong>
                    </p>
                    <p style="margin: 0; opacity: 0.9;">
                        Ya has iniciado sesión. Para usar otra cuenta, primero cierra tu sesión actual.
                    </p>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="LP.php" style="display: block; text-align: center; padding: 12px 20px; 
                                            background: linear-gradient(135deg, var(--accent), var(--secondary)); 
                                            color: white; text-decoration: none; border-radius: 8px; 
                                            font-weight: 600; transition: transform 0.2s, box-shadow 0.2s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(78, 84, 200, 0.4)';"
                       onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                        🏠 Ir al Hub
                    </a>
                    <a href="logout.php" style="display: block; text-align: center; padding: 12px 20px; 
                                                background: rgba(255, 255, 255, 0.1); 
                                                border: 1px solid rgba(255, 255, 255, 0.2);
                                                color: white; text-decoration: none; border-radius: 8px; 
                                                font-weight: 500; transition: background 0.2s;"
                       onmouseover="this.style.background='rgba(239, 68, 68, 0.3)';"
                       onmouseout="this.style.background='rgba(255, 255, 255, 0.1)';">
                        🚪 Cerrar Sesión
                    </a>
                </div>
                
                <p style="margin-top: 1.5rem;"><a href="index.php">← Volver al inicio</a></p>
            </div>
        <?php else: ?>
            <!-- Usuario no tiene sesión - mostrar formulario de login -->
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
                        value="<?php echo isset($_POST['user']) ? htmlEscape($_POST['user']) : ''; ?>" required autofocus>
                </div>

                <div class="input-wrapper">
                    <input type="password" name="clave" placeholder="Contraseña" required>
                </div>

                <button type="submit" id="submitBtn">Iniciar Sesión</button>

                <p>¿No tienes cuenta? <a href="registrar.php">Regístrate aquí</a></p>

                <p><a href="index.php">← Volver al inicio</a></p>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>