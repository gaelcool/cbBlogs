<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    if (empty($title) || empty($description) || empty($category)) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        $pdo = getPDO();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO suggestions (
                    title, description, category, author_id, is_anonymous, status
                ) VALUES (
                    :title, :desc, :cat, :uid, :anon, 'pending'
                )
            ");
            
            $stmt->execute([
                ':title' => $title,
                ':desc' => $description,
                ':cat' => $category,
                ':uid' => $_SESSION['id_usr'],
                ':anon' => $is_anonymous
            ]);
            
            // Track contribution
            $sugId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("
                INSERT INTO user_contributions (user_id, contribution_type, contribution_id)
                VALUES (:uid, 'suggestion', :sid)
            ");
            $stmt->execute([':uid' => $_SESSION['id_usr'], ':sid' => $sugId]);
            
            header('Location: democracy.php?status=success');
            exit;
        } catch (Exception $e) {
            $error = 'Error al enviar: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Sugerencia - CbNoticias</title>
    <link rel="stylesheet" href="css/democracy.css">
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="democracy.php">Tu Voz</a>
            <a href="Account-info.php">Mi Cuenta</a>
        </div>
    </nav>

    <div class="democracy-container">
        <div class="page-header">
            <h1>Nueva Sugerencia</h1>
            <p>Ayúdanos a mejorar nuestra comunidad escolar</p>
        </div>

        <div class="form-container">
            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlEscape($error); ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Título de la Sugerencia</label>
                    <input type="text" name="title" class="form-control" required placeholder="Ej: Más mesas en la biblioteca">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Categoría</label>
                    <select name="category" class="form-control" required>
                        <option value="">Seleccionar...</option>
                        <option value="feature">Nueva Funcionalidad</option>
                        <option value="content">Contenido</option>
                        <option value="community">Comunidad</option>
                        <option value="facility">Instalaciones</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Descripción Detallada</label>
                    <textarea name="description" class="form-control" rows="5" required placeholder="Explica tu idea y cómo beneficiaría a la escuela..."></textarea>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" name="is_anonymous" id="anon">
                    <label for="anon">Enviar anónimamente (tu nombre no será visible para otros estudiantes)</label>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <a href="democracy.php" class="btn" style="flex: 1;">Cancelar</a>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Enviar Sugerencia</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
