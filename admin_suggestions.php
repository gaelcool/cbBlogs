<?php
require_once 'lib/common.php';
session_start();
requiereAdmin(1);

$pdo = getPDO();
$message = '';

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $status = $_POST['status'];
        $response = trim($_POST['response'] ?? '');
        
        $stmt = $pdo->prepare("
            UPDATE suggestions 
            SET status = :status, 
                admin_response = :resp,
                responded_by = :uid,
                responded_at = CURRENT_TIMESTAMP,
                last_updated = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute([
            ':status' => $status,
            ':resp' => $response,
            ':uid' => $_SESSION['id_usr'],
            ':id' => $id
        ]);
        
        // If implemented, add to implemented_changes
        if ($status === 'implemented') {
            // Check if already exists
            $check = $pdo->prepare("SELECT COUNT(*) FROM implemented_changes WHERE suggestion_id = :id");
            $check->execute([':id' => $id]);
            if ($check->fetchColumn() == 0) {
                // Get suggestion details
                $sug = $pdo->prepare("SELECT title, description FROM suggestions WHERE id = :id");
                $sug->execute([':id' => $id]);
                $sugData = $sug->fetch(PDO::FETCH_ASSOC);
                
                $imp = $pdo->prepare("
                    INSERT INTO implemented_changes (suggestion_id, title, description, implemented_by)
                    VALUES (:id, :title, :desc, :uid)
                ");
                $imp->execute([
                    ':id' => $id,
                    ':title' => $sugData['title'],
                    ':desc' => $sugData['description'],
                    ':uid' => $_SESSION['id_usr']
                ]);
            }
        }
        
        $message = 'Sugerencia actualizada correctamente.';
    }
}

// Fetch all suggestions
$stmt = $pdo->query("
    SELECT s.*, u.nombre as author_name
    FROM suggestions s
    JOIN user u ON s.author_id = u.id_usr
    ORDER BY s.created_at DESC
");
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sugerencias</title>
    <link rel="stylesheet" href="css/democracy.css">
    <style>
        .admin-controls {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            border: 1px solid #ddd;
        }
        .status-select {
            padding: 5px;
            border-radius: 4px;
        }
        .response-box {
            width: 100%;
            margin-top: 0.5rem;
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias Admin</h2>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php">Panel</a>
            <a href="admin_suggestions.php" class="active">Sugerencias</a>
            <a href="admin_grievances.php">Reportes</a>
            <a href="logout.php">Salir</a>
        </div>
    </nav>

    <div class="democracy-container">
        <div class="page-header">
            <h1>Gestión de Sugerencias</h1>
        </div>

        <?php if ($message): ?>
            <div class="success-msg" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <?php echo htmlEscape($message); ?>
            </div>
        <?php endif; ?>

        <div class="suggestions-grid">
            <?php foreach ($suggestions as $sug): ?>
                <div class="suggestion-card status-<?php echo $sug['status']; ?>">
                    <div class="suggestion-header">
                        <span class="category-badge"><?php echo htmlEscape($sug['category']); ?></span>
                        <span class="status-badge <?php echo $sug['status']; ?>">
                            <?php echo $sug['status']; ?>
                        </span>
                    </div>
                    
                    <h3 class="suggestion-title"><?php echo htmlEscape($sug['title']); ?></h3>
                    <div class="suggestion-meta">
                        <span>👤 <?php echo htmlEscape($sug['author_name']); ?> <?php echo $sug['is_anonymous'] ? '(ANÓNIMO)' : ''; ?></span>
                        <span>📅 <?php echo TraduceSQLfecha($sug['created_at']); ?></span>
                    </div>
                    
                    <div class="suggestion-body">
                        <?php echo nl2br(htmlEscape($sug['description'])); ?>
                    </div>
                    
                    <div class="admin-controls">
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo $sug['id']; ?>">
                            <input type="hidden" name="action" value="update_status">
                            
                            <label><strong>Estado:</strong></label>
                            <select name="status" class="status-select">
                                <option value="pending" <?php echo $sug['status'] == 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="under_review" <?php echo $sug['status'] == 'under_review' ? 'selected' : ''; ?>>En Revisión</option>
                                <option value="in_progress" <?php echo $sug['status'] == 'in_progress' ? 'selected' : ''; ?>>En Progreso</option>
                                <option value="implemented" <?php echo $sug['status'] == 'implemented' ? 'selected' : ''; ?>>Implementado</option>
                                <option value="declined" <?php echo $sug['status'] == 'declined' ? 'selected' : ''; ?>>Declinado</option>
                            </select>
                            
                            <div style="margin-top: 0.5rem;">
                                <label><strong>Respuesta:</strong></label>
                                <textarea name="response" class="response-box" rows="2" placeholder="Escribe una respuesta..."><?php echo htmlEscape($sug['admin_response']); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem; font-size: 0.9rem; padding: 5px 15px;">Actualizar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
