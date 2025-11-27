<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

// Calculate days registered con funcion
$dias = 0;
if (isset($_SESSION['fecha_registro'])) {
    $dias = calcularDiasRegistrado($_SESSION['fecha_registro']);
}

// Get total blogs count for this user
$pdo = getPDO();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM post WHERE author_name = :usuario');
$stmt->execute([':usuario' => $_SESSION['usuario']]);
$totalblogs = $stmt->fetchColumn();

// Get user style for display
$stmt = $pdo->prepare("SELECT template_name FROM user_blog_style WHERE user_id = :uid");
$stmt->execute([':uid' => $_SESSION['id_usr']]);
$style = $stmt->fetch(PDO::FETCH_ASSOC);
$currentTemplate = $style ? ($style['template_name'] === 'pink_classic' ? 'Pink Classic' : 'Frutiger Aero') : 'Frutiger Aero';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - CbNoticias</title>
    <link rel="stylesheet" href="css/account-inf.css">
    <style>
        .style-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .style-info h3 { margin: 0 0 0.5rem 0; color: var(--primary); }
        .style-info p { margin: 0; opacity: 0.8; }
    </style>
</head>

<body class="ey">
     
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="Write.php">Escribir</a>
            <a href="Account-info.php">Mi Cuenta</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="user-display">
            <span class="user-greeting">Hola,</span>
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>

    <div class="account-container">
        <div class="account-header">
            <h1> Mi Cuenta</h1>
            <p>Gestiona tu información personal y estadísticas</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalblogs; ?></div>
                <div class="stat-label">Blogs Publicados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $dias; ?></div>
                <div class="stat-label">Días Registrado</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $_SESSION['grade'] ?? 1; ?></div>
                <div class="stat-label">Nivel Escritor</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo TraduceSQLfecha($_SESSION['fecha_registro'] ?? ''); ?></div>
                <div class="stat-label">Fecha Registro</div>
            </div>
        </div>

        <div class="personal-info-row">
            <div class="info-card-personal">
                <h3> Información Personal</h3>
                <div class="info-item">
                    <span class="info-label">Usuario:</span>
                    <span class="info-value"><?php echo htmlEscape($_SESSION['usuario']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Correo:</span>
                    <span class="info-value"><?php echo htmlEscape($_SESSION['email'] ?? 'No especificado'); ?></span>
                </div>
            </div>

            <div class="info-card-general">
                <h3> Preferencias</h3>
                <div class="info-item">
                    <span class="info-label">Género Favorito:</span>
                    <span class="info-value"><?php echo htmlEscape($_SESSION['genero_lit_fav'] ?? 'General'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nivel:</span>
                    <span class="info-value">
                        <span class="grade-indicator grade-<?php echo $_SESSION['grade'] ?? 1; ?>">
                            Nivel <?php echo $_SESSION['grade'] ?? 1; ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <div class="style-card">
            <div class="style-info">
                <h3>🎨 Apariencia del Blog</h3>
                <p>Tema actual: <strong><?php echo $currentTemplate; ?></strong></p>
            </div>
            <a href="edit_blog_style.php" class="btn">Personalizar Estilo</a>
        </div>

        <div class="edit-section">
            <h3>✏️ Editar Información</h3>
            <p style="margin-bottom: 1rem;">Actualiza tus datos personales y preferencias</p>
            <button class="btn" onclick="window.location.href='updateAcc.php'" id="editBtn">Editar Información</button>
        </div>
    </div>
    
    <footer class="footer">    
        &copy; 2025 CbNoticias. Suerte 
    </footer>
</body>
</html>