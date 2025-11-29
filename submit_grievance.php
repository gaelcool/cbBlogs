<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $severity = $_POST['severity'] ?? 'low';
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    
    if (empty($subject) || empty($description) || empty($category)) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        $pdo = getPDO();
        try {
            $stmt = $pdo->prepare("
                INSERT INTO problemasHH (
                    subject, description, category, severity, 
                    reporter_id, is_anonimo, status
                ) VALUES (
                    :subject, :desc, :cat, :sev, 
                    :uid, :anon, 'submitted'
                )
            ");
            
            $stmt->execute([
                ':subject' => $subject,
                ':desc' => $description,
                ':cat' => $category,
                ':sev' => $severity,
                ':uid' => $_SESSION['id_usr'],
                ':anon' => $is_anonymous
            ]);
            
            // Do NOT track public contribution for grievances (privacy)
            
            header('Location: democracy.php?status=grievance_submitted');
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
    <title>Reportar Problema - CbNoticias</title>
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
            <h1>Reportar un Problema</h1>
            <p>Tu seguridad y bienestar son nuestra prioridad</p>
        </div>

        <div class="form-container" style="border-left: 5px solid #FFC107;">
            <div style="background: rgba(255, 193, 7, 0.1); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-size: 0.9rem;">
                <strong>🔒 Confidencialidad:</strong> Este reporte será enviado directamente a la administración. Si eliges la opción anónima, tu identidad no será revelada ni siquiera a los administradores, aunque esto puede limitar nuestra capacidad de investigar.
            </div>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlEscape($error); ?></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Asunto</label>
                    <input type="text" name="subject" class="form-control" required placeholder="Resumen del problema">
                </div>
                
                <div class="form-group" style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <label class="form-label">Categoría</label>
                        <select name="category" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <option value="harassment">Acoso / Bullying</option>
                            <option value="discrimination">Discriminación</option>
                            <option value="safety">Seguridad</option>
                            <option value="academic">Académico</option>
                            <option value="facility">Instalaciones</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">Severidad</label>
                        <select name="severity" class="form-control" required>
                            <option value="low">Baja</option>
                            <option value="medium">Media</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                    <label class="form-label">involucrados</label>
                    <select name="involucrados" class="form-control" required>
                            <option value="student">Estudiante/s</option>
                            <option value="mod">Moderardores</option>
                            <option value="School_Infrastructure">Instalaciones</option>
                    </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Descripción Detallada</label>
                    <textarea name="description" class="form-control" rows="6" required placeholder="Describe lo sucedido con el mayor detalle posible (fechas, lugares, personas involucradas)..."></textarea>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" name="is_anonymous" id="anon">
                    <label for="anon">Reportar anónimamente</label>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <a href="democracy.php" class="btn" style="flex: 1;">Cancelar</a>
                    <button type="submit" class="btn btn-warning" style="flex: 1;">Enviar Reporte</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
