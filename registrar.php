<?php
require_once 'lib/common.php';
session_start();

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();
    
    $nombre = trim($_POST["nombre"] ?? '');
    $email = trim($_POST["correo"] ?? '');
    $usuario = trim($_POST["usuario"] ?? '');
    $clave = $_POST["clave"] ?? '';
    $telefono = trim($_POST["telefono"] ?? '');
    $genero_lit_fav = $_POST["genero_lit_fav"] ?? 'General';
   
    try {
        // validación
        if (empty($nombre) || empty($email) || empty($usuario) || empty($clave)) {
            $error = "Todos los campos obligatorios deben ser completados.";
        }
        elseif (strlen($usuario) < 3 || strlen($usuario) > 20) {
            $error = "El usuario debe tener entre 3 y 20 caracteres.";
        }
        elseif (strlen($clave) < 6) {
            $error = "La contraseña debe tener al menos 6 caracteres.";
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo electrónico no es válido.";
        }
        elseif (userExists($pdo, $usuario)) {
            $error = "El usuario ya existe. Por favor elige otro.";
        }
        elseif (emailExists($pdo, $email)) {
            $error = "El correo ya está registrado.";
        }
        else {
            // insertar nuevo usuario
            $stmt = $pdo->prepare("
                INSERT INTO user (usuario, nombre, email, clave, fecha_registro, grade, genero_lit_fav) 
                VALUES (:usuario, :nombre, :email, :clave, CURRENT_TIMESTAMP, 1, :genero_lit_fav)
            ");

            $result = $stmt->execute([
                ':usuario' => $usuario,
                ':nombre' => $nombre,
                ':email' => $email,
                ':clave' => $clave,
                ':genero_lit_fav' => $genero_lit_fav
            ]);
            
            if ($result) {
                $success = true;
                
                // agarrar los datos del usuario recién creado
                $newUserData = intentaLogin($pdo, $usuario, $clave);
                
                if ($newUserData) {
                    // auto login después de registrarse
                    login(
                        $newUserData['usuario'],
                        $newUserData['nombre'],
                        $newUserData['genero_lit_fav'],
                        $newUserData['fecha_registro'],
                        $newUserData['grade'],
                        $newUserData['email'],
                        $newUserData['id_usr']
                    );
                    header("refresh:1;url=LP.php");
                }
            } else {
                $error = "Error al crear la cuenta. Intenta nuevamente.";
            }
        }
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        $error = "Error al registrarse: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/registrar.css">
  <title>Registro - CbNoticias</title>
  <style>
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      font-weight: 500;
    }
    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 2px solid #f5c6cb;
    }
    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 2px solid #c3e6cb;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="glass-container">
    <form action="registrar.php" method="post" class="form" id="registerForm">
      <h2> Crear Cuenta</h2>
      
      <?php if ($error): ?>
        <div class="alert alert-error">
          <?php echo htmlEscape($error); ?>
        </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
        <div class="alert alert-success">
          ✅ ¡Cuenta creada exitosamente! Redirigiendo...
        </div>
      <?php endif; ?>
      
      <div class="input-wrapper">
        <input type="text" name="nombre" id="nombre" placeholder="Nombre completo" 
               value="<?php echo isset($_POST['nombre']) ? htmlEscape($_POST['nombre']) : ''; ?>" 
               required>
        <div class="validation-box" id="nombreMsg"></div>
      </div>
      
      <div class="input-wrapper">
        <input type="email" name="correo" id="correo" placeholder="Correo electrónico" 
               value="<?php echo isset($_POST['correo']) ? htmlEscape($_POST['correo']) : ''; ?>" 
               required>
        <div class="validation-box" id="correoMsg"></div>
      </div>
      
      <div class="input-wrapper">
        <input type="text" name="usuario" id="usuario" placeholder="Usuario (3-20 caracteres)" 
               value="<?php echo isset($_POST['usuario']) ? htmlEscape($_POST['usuario']) : ''; ?>" 
               required>
        <div class="validation-box" id="usuarioMsg"></div>
      </div>
      
      <div class="input-wrapper">
        <input type="password" name="clave" id="clave" placeholder="Contraseña (mín. 6 caracteres)" required>
        <div class="validation-box" id="claveMsg"></div>
      </div>
      
      <div class="input-wrapper">
        <input type="tel" name="telefono" id="telefono" placeholder="Teléfono (opcional)" 
               value="<?php echo isset($_POST['telefono']) ? htmlEscape($_POST['telefono']) : ''; ?>">
        <div class="validation-box" id="telefonoMsg"></div>
      </div>
      
      <div class="input-wrapper">
        <select name="genero_lit_fav" id="genero_lit_fav">
          <option value="General">Selecciona tu género literario favorito (opcional)</option>
          <option value="Ficción" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'Ficción') ? 'selected' : ''; ?>>Ficción</option>
          <option value="No Ficción" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'No Ficción') ? 'selected' : ''; ?>>No Ficción</option>
          <option value="Ciencia Ficción" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'Ciencia Ficción') ? 'selected' : ''; ?>>Ciencia Ficción</option>
          <option value="Romance" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'Romance') ? 'selected' : ''; ?>>Romance</option>
          <option value="Misterio" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'Misterio') ? 'selected' : ''; ?>>Misterio</option>
          <option value="Fantasía" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'Fantasía') ? 'selected' : ''; ?>>Fantasía</option>
          <option value="Horror" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'Horror') ? 'selected' : ''; ?>>Horror</option>
          <option value="Biografía" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'Biografía') ? 'selected' : ''; ?>>Biografía</option>
          <option value="Poesía" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'Poesía') ? 'selected' : ''; ?>>Poesía</option>
          <option value="General" <?php echo (isset($_POST['genero_lit_fav']) && $_POST['genero_lit_fav'] === 'General') ? 'selected' : ''; ?>>General</option>
        </select>
      </div>
      
      <button type="submit" id="submitBtn">Registrarse</button>
      <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
      <p><a href="index.php">← Volver al inicio</a></p>
    </form>
    </div>
  </div>
  <script src="js/script.js"></script>
</body>
</html>