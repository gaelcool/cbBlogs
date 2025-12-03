<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

// Get current user's style preferences
$pdo = getPDO();

// Fallback: If id_usr is missing from session but user is logged in, fetch it
if (!isset($_SESSION['id_usr']) && isset($_SESSION['usuario'])) {
    $stmtUser = $pdo->prepare("SELECT id_usr FROM user WHERE usuario = :usuario");
    $stmtUser->execute([':usuario' => $_SESSION['usuario']]);
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if ($userRow) {
        $_SESSION['id_usr'] = $userRow['id_usr'];
    } else {
        // Should not happen if logged in, but safety first
        header('Location: logout.php');
        exit();
    }
}

$user_id = $_SESSION['id_usr'] ?? 0; // Default to 0 or handle error if still missing
$stmt = $pdo->prepare("SELECT * FROM user_blog_style WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$currentStyle = $stmt->fetch(PDO::FETCH_ASSOC);

// Set defaults if no style exists
if (!$currentStyle) {
    $currentStyle = [
        'template_name' => 'frutiger_aero',
        'background_image' => '',
        'font_family' => 'Segoe UI, Arial, sans-serif',
        'title_size' => '2.5rem',
        'body_size' => '1.1rem',
        'text_decoration' => 'none'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizar Estilo del Blog - CbNoticias</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/editor.css">
    <!-- Precargar estilos de plantilla para vista previa -->
    <link rel="stylesheet" href="css/templates/frutiger_aero.css" id="template-style">
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2>CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="Read.php">← Volver a Blogs</a>
            <span class="nav-title">🎨 Editor de Estilos</span>
        </div>
        <div class="user-display">
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>

    <div class="editor-container">
        <!-- Panel Izquierdo: Controles -->
        <div class="editor-controls glass-container">
            <h3>Personaliza Tu Estilo</h3>
            <p class="text-muted">Estos estilos se aplicarán a TODAS tus publicaciones de blog.</p>
            
            <form id="styleForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="templateSelect">Elegir Plantilla</label>
                    <select id="templateSelect" name="template_name" class="form-control">
                        <option value="frutiger_aero" <?php echo $currentStyle['template_name'] === 'frutiger_aero' ? 'selected' : ''; ?>>Frutiger Aero (Glassmorfismo)</option>
                        <option value="pink_classic" <?php echo $currentStyle['template_name'] === 'pink_classic' ? 'selected' : ''; ?>>Rosa Clásico (Sólido)</option>
                    </select>
                </div>

                <div class="style-section">
                    <h4>Fondo</h4>
                    <div class="form-group">
                        <label>Fondo Actual</label>
                        <div id="currentBgDisplay" class="bg-preview-box">
                            <?php if (!empty($currentStyle['background_image'])): ?>
                                <img src="img/user_backgrounds/<?php echo htmlEscape($currentStyle['background_image']); ?>?t=<?php echo time(); ?>" alt="Current Background">
                                <span class="bg-name">Imagen Personalizada Subida</span>
                            <?php else: ?>
                                <span class="bg-name">Usando Plantilla Predeterminada</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bgUpload">Subir Nuevo Fondo (Máx 2MB)</label>
                        <input type="file" id="bgUpload" name="background_image" accept=".jpg,.jpeg,.png,.webp">
                        <small>Subir una nueva imagen reemplaza la actual.</small>
                    </div>
                </div>

                <div class="style-section">
                    <h4>Tipografía</h4>
                    <div class="form-group">
                        <label for="fontFamily">Familia de Fuente</label>
                        <input type="text" id="fontFamily" name="font_family" value="<?php echo htmlEscape($currentStyle['font_family']); ?>" placeholder="ej. Arial, sans-serif">
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="titleSize">Tamaño del Título</label>
                                <input type="text" id="titleSize" name="title_size" value="<?php echo htmlEscape($currentStyle['title_size']); ?>" placeholder="2.5rem">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="bodySize">Tamaño del Texto</label>
                                <input type="text" id="bodySize" name="body_size" value="<?php echo htmlEscape($currentStyle['body_size']); ?>" placeholder="1.1rem">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="textDecoration">Decoración de Texto</label>
                        <select id="textDecoration" name="text_decoration">
                            <option value="none" <?php echo $currentStyle['text_decoration'] === 'none' ? 'selected' : ''; ?>>Ninguna</option>
                            <option value="underline" <?php echo $currentStyle['text_decoration'] === 'underline' ? 'selected' : ''; ?>>Subrayado</option>
                            <option value="overline" <?php echo $currentStyle['text_decoration'] === 'overline' ? 'selected' : ''; ?>>Línea Superior</option>
                            <option value="line-through" <?php echo $currentStyle['text_decoration'] === 'line-through' ? 'selected' : ''; ?>>Tachado</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="resetBtn" class="btn btn-secondary">Restablecer Valores</button>
                    <button type="submit" id="saveBtn" class="btn btn-primary">Guardar Cambios</button>
                </div>
                <div id="messageArea"></div>
            </form>
        </div>

        <!-- Panel Derecho: Vista Previa en Vivo -->
        <div class="preview-pane">
            <div class="preview-header">
                <span>Vista Previa en Vivo</span>
            </div>
            <div id="previewFrame" class="preview-content">
                <!-- Publicación de Muestra -->
                <div class="post-container">
                    <article class="post-card">
                        <header class="post-header">
                            <h1 class="post-title">Título de Blog de Ejemplo</h1>
                            <p class="post-subtitle">Así se ve tu subtítulo</p>
                            
                            <div class="post-meta">
                                <span class="post-author">👤 <?php echo htmlEscape($_SESSION['nombre']); ?></span>
                                <span class="post-date">📅 <?php echo date('d/m/Y'); ?></span>
                                <span class="post-tag">General</span>
                            </div>
                        </header>
                        
                        <div class="post-content">
                            <p>Esta es una vista previa de cómo aparecerán tus publicaciones de blog a los visitantes. Los estilos que configures a la izquierda se aplican aquí en tiempo real.</p>
                            <p>¡Puedes personalizar la fuente, tamaños e imagen de fondo para hacer tu blog único!</p>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>

    <script src="js/css_editor.js"></script>
</body>
</html>
