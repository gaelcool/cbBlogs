<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

$pdo = getPDO();

// traer datos actuales del usuario
$usuario_data = getUserByUsername($pdo, $_SESSION['usuario']);

if (!$usuario_data) {
    die('Error: No se pudo cargar la información del usuario');
}

// manejar cuando se envía el formulario
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // agarrar datos del formulario
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $genero_lit_fav = !empty($_POST['genero_lit_fav']) ? trim($_POST['genero_lit_fav']) : null;
    
    // validación del lado del servidor
    $errores = [];
    
    // validar nombre (solo letras mayúsculas y espacios)
    if (!preg_match('/^[A-ZÁÉÍÓÚÑ\s]+$/u', $nombre)) {
        $errores[] = "El nombre solo debe contener letras mayúsculas y espacios";
    }
    
    // validar email
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido";
    }
    
    // revisar si el email ya está siendo usado por otro usuario
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = :email AND usuario != :usuario");
    $stmt->execute([':email' => $correo, ':usuario' => $_SESSION['usuario']]);
    if ($stmt->fetchColumn() > 0) {
        $errores[] = "El correo electrónico ya está siendo usado por otro usuario";
    }
    
    if (empty($errores)) {
        // actualizar info del usuario
        try {
            $success = updateUserInfo($pdo, $_SESSION['usuario'], $nombre, $correo, $genero_lit_fav);
            
            if ($success) {
                // actualizar variables de sesión
                $_SESSION['nombre'] = $nombre;
                $_SESSION['email'] = $correo;
                $_SESSION['genero_lit_fav'] = $genero_lit_fav;
                
                // refrescar datos del usuario
                $usuario_data = getUserByUsername($pdo, $_SESSION['usuario']);
                
                $mensaje = "Tu información se actualizó exitosamente";
            } else {
                $error = "❌ Error al actualizar los datos. Por favor intenta de nuevo.";
            }
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    } else {
        $error = "❌ " . implode("<br>", $errores);
    }
}

?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Cuenta - CbNoticias</title>
    <link rel="stylesheet" href="css/updateAcc.css">
 
    
</head>

<body class="ey">
       <nav class="nav">
        <div class="logo">
             CbNoticias
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="Write.php">Escribir</a>
            <a href="Account-info.php">Mi Cuenta</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="userstop">
            <?php echo htmlEscape($_SESSION['nombre']); ?>
        </div>
    </nav>

  <div class="rightsmallwarn">
    <div class="topo">
      <h3>⚠️ Aviso Importante:</h3>
</div>
    <div class="bottomo">
      <p>Por favor, asegúrate de que tu información esté actualizada para mejorar tu experiencia en CbNoticias. Mantener tus datos correctos nos ayuda a brindarte un mejor servicio y soporte.</p>
</div>
  </div>
<div class="update-container">

    <?php if ($mensaje): ?>
        <div class="alert alert-success">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
        <form method="post">
            <h2>Actualizar Mi Información</h2>
            
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" 
                       value="<?php echo htmlEscape($usuario_data['usuario']); ?>" 
                       class="readonly-info" readonly>
                <small>El nombre de usuario no se puede cambiar</small>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre Completo *</label>
                <input type="text" id="nombre" name="nombre" 
                       value="<?php echo htmlEscape($usuario_data['nombre']); ?>" 
                       pattern="^[A-ZÁÉÍÓÚÑ\s]+$" 
                       title="Solo mayúsculas y espacios"
                       required>
                <small>Solo letras mayúsculas y espacios</small>
            </div>

            <div class="form-group">
                <label for="correo">Correo Electrónico *</label>
                <input type="email" id="correo" name="correo" 
                       value="<?php echo htmlEscape($usuario_data['email']); ?>" 
                       pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                       required>
                <small>Formato: ejemplo@correo.com</small>
            </div>

            <div class="form-group">
                <label for="genero_lit_fav">Género Literario Favorito</label>
                <select id="genero_lit_fav" name="genero_lit_fav">
                    <option value="">Seleccionar...</option>
                    <option value="Ficción" <?php echo ($usuario_data['genero_lit_fav'] == 'Ficción') ? 'selected' : ''; ?>>Ficción</option>
                    <option value="No Ficción" <?php echo ($usuario_data['genero_lit_fav'] == 'No Ficción') ? 'selected' : ''; ?>>No Ficción</option>
                    <option value="Ciencia Ficción" <?php echo ($usuario_data['genero_lit_fav'] == 'Ciencia Ficción') ? 'selected' : ''; ?>>Ciencia Ficción</option>
                    <option value="Fantasía" <?php echo ($usuario_data['genero_lit_fav'] == 'Fantasía') ? 'selected' : ''; ?>>Fantasía</option>
                    <option value="Misterio" <?php echo ($usuario_data['genero_lit_fav'] == 'Misterio') ? 'selected' : ''; ?>>Misterio</option>
                    <option value="Romance" <?php echo ($usuario_data['genero_lit_fav'] == 'Romance') ? 'selected' : ''; ?>>Romance</option>
                    <option value="Terror" <?php echo ($usuario_data['genero_lit_fav'] == 'Terror') ? 'selected' : ''; ?>>Terror</option>
                    <option value="Biografía" <?php echo ($usuario_data['genero_lit_fav'] == 'Biografía') ? 'selected' : ''; ?>>Biografía</option>
                    <option value="Historia" <?php echo ($usuario_data['genero_lit_fav'] == 'Historia') ? 'selected' : ''; ?>>Historia</option>
                    <option value="Tecnología" <?php echo ($usuario_data['genero_lit_fav'] == 'Tecnología') ? 'selected' : ''; ?>>Tecnología</option>
                    <option value="Otro" <?php echo ($usuario_data['genero_lit_fav'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-success"> Guardar Cambios</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='Account-info.php'"> Cancelar</button>
            </div>
        </form>
   

</body>
</html>