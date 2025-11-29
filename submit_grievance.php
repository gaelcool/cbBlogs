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
    $involves_student = isset($_POST['involves_student']) ? 1 : 0;
    $involves_mod = isset($_POST['involves_mod']) ? 1 : 0;
    $involves_infrastructure = isset($_POST['involves_infrastructure']) ? 1 : 0;
    
    if (empty($subject) || empty($description) || empty($category)) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        $pdo = getPDO();
        try {
            $stmt = $pdo->prepare("
                    INSERT INTO problemasHH (
                    subject, description, category, severity, 
                    reporter_id, is_anonimo, status, 
                    involves_student, involves_mod, involves_infrastructure
                ) VALUES (
                    :subject, :desc, :cat, :sev, 
                    :uid, :anon, 'submitted', 
                    :inv_student, :inv_mod, :inv_infra
                )
            ");
            
            $stmt->execute([
                ':subject' => $subject,
                ':desc' => $description,
                ':cat' => $category,
                ':sev' => $severity,
                ':uid' => $_SESSION['id_usr'],
                ':anon' => $is_anonymous,
                ':inv_student' => $involves_student,
                ':inv_mod' => $involves_mod,
                ':inv_infra' => $involves_infrastructure
            ]);
            
            // Anonimidad
            
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
<div id="confirmModal" class="modal-overlay">
    <div class="modal-content">
        <h3 class="modal-header"> Confirmar Envío de Reporte</h3>
        <div class="modal-body">
            <p><strong>Modo:</strong> <span id="anonMode"></span></p>
            <p><strong>Severidad:</strong> <span id="severityDisplay"></span></p>
            <p><strong>Categoría:</strong> <span id="categoryDisplay"></span></p>
            
            <div class="modal-warning">
                <strong>⚠️ Importante:</strong> Este reporte será revisado por la administración. Asegúrate de que toda la información sea precisa.
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="modal-btn modal-btn-cancel" onclick="closeModal()">
                Cancelar
            </button>
            <button type="button" class="modal-btn modal-btn-confirm" onclick="confirmSubmit()">
                Confirmar Envío
            </button>
        </div>
    </div>
</div>

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
            <div style="background: rgba(255, 193, 7, 0.1); padding: 1rem; border-radius: 8px; margin-bottom: 0; font-size: 0.9rem;">
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
                    <label class="form-label">Partes Involucradas (Selecciona las que apliquen)</label>
                    <div class="checkbox-group" style="margin-top: 0.5rem;">
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <div>
                                <input type="checkbox" name="involves_student" id="inv_student" value="1">
                                <label for="inv_student" style="font-weight: normal;">Estudiante/s</label>
                            </div>
                            <div>
                                <input type="checkbox" name="involves_mod" id="inv_mod" value="1">
                                <label for="inv_mod" style="font-weight: normal;">Moderadores</label>
                            </div>
                            <div>
                                <input type="checkbox" name="involves_infrastructure" id="inv_infra" value="1">
                                <label for="inv_infra" style="font-weight: normal;">Instalaciones</label>
                            </div>
                        </div>
                    </div>
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
    <script>
let actualSubmitButton = null;

document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get values directly from form
    const isAnon = document.getElementById('anon').checked;
    const severity = document.querySelector('[name="severity"]').value;
    const category = document.querySelector('[name="category"]').value;
    
    // Display values in modal
    document.getElementById('anonMode').textContent = isAnon ? '🔒 ANÓNIMO' : '📋 IDENTIFICADO';
    document.getElementById('severityDisplay').textContent = severity.toUpperCase();
    document.getElementById('categoryDisplay').textContent = category;
    
    // Show modal
    document.getElementById('confirmModal').classList.add('show');
});

function closeModal() {
    document.getElementById('confirmModal').classList.remove('show');
}

function confirmSubmit() {
    // Remove the event listener so form submits normally
    const form = document.querySelector('form');
    form.onsubmit = null;
    form.submit();
}
</script>
</body>
</html>
