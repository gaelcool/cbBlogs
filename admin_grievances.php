<?php
require_once 'lib/common.php';
session_start();

// Require admin/moderator access (nivel >= 1)
requiereAdmin(1);

$pdo = getPDO();
$message = '';
$adminInfo = obtenerAdminInfo($pdo, $_SESSION['id_usr']);

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];
    
    if ($action === 'update_status') {
        $status = $_POST['status'];
        $resolution = trim($_POST['resolution'] ?? '');
        
        // Update problemaHH
        $stmt = $pdo->prepare("
            UPDATE problemasHH 
            SET status = :status, 
                resumen_resolutorio = :res,
                admin_asignado = :uid,
                asignado_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute([
            ':status' => $status,
            ':res' => $resolution,
            ':uid' => $adminInfo['info']['id_admin'], // Use admin ID, not user ID
            ':id' => $id
        ]);
        
        // Log action
        registrarAccionProblemaHH(
            $pdo, 
            $id, 
            $adminInfo['info']['id_admin'], 
            'cambio_estado', 
            "Estado actualizado a: $status. Notas: " . substr($resolution, 0, 50) . "..."
        );
        
        $message = 'Reporte actualizado correctamente.';
    }
}

// Fetch all grievances
$stmt = $pdo->query("
    SELECT p.*, u.nombre as reporter_name, u.usuario as reporter_username
    FROM problemasHH p
    LEFT JOIN user u ON p.reporter_id = u.id_usr
    ORDER BY p.submitted_at DESC
");
$grievances = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reportes</title>
    <link rel="stylesheet" href="css/democracy.css">
    <style>
        .grievance-card {
            background: white;
            border: 1px solid #ddd;
            border-left: 5px solid #dc3545;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .severity-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            color: white;
        }
        .severity-low { background: #28a745; }
        .severity-medium { background: #ffc107; color: black; }
        .severity-high { background: #fd7e14; }
        .severity-urgent { background: #dc3545; }
        
        .anonymous-badge {
            background: #6c757d;
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2>CbNoticias Admin</h2>
        </div>
        <div class="nav-links">
            <a href="admin_dashboard.php">Panel</a>
            <a href="admin_suggestions.php">Sugerencias</a>
            <a href="admin_grievances.php" class="active">Reportes</a>
            <a href="logout.php">Salir</a>
        </div>
    </nav>

    <div class="democracy-container">
        <div class="page-header">
            <h1>Gestión de Reportes (Confidencial)</h1>
            <span class="admin-badge">Admin Nivel: <?php echo $adminInfo['nivel']; ?></span>
        </div>

        <?php if ($message): ?>
            <div class="success-msg" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <?php echo htmlEscape($message); ?>
            </div>
        <?php endif; ?>

        <div class="suggestions-grid">
            <?php foreach ($grievances as $g): ?>
                <div class="grievance-card">
                    <div class="suggestion-header">
                        <span class="category-badge"><?php echo htmlEscape($g['category']); ?></span>
                        <span class="severity-badge severity-<?php echo strtolower($g['severity']); ?>">
                            <?php echo strtoupper($g['severity']); ?>
                        </span>
                    </div>
                    
                    <h3 class="suggestion-title">
                        <?php 
                        if ($g['is_anonimo']) {
                            echo $g['subject'] ?: '[Reporte Anónimo]';
                        } else {
                            echo htmlEscape($g['subject']); 
                        }
                        ?>
                    </h3>
                    
                    <div class="suggestion-meta">
                        <span>👤 
                            <?php 
                            if ($g['is_anonimo']) {
                                echo '<span class="anonymous-badge">ANÓNIMO</span>';
                            } else {
                                echo htmlEscape($g['reporter_name']);
                            }
                            ?>
                        </span>
                        <span>📅 <?php echo TraduceSQLfecha($g['submitted_at']); ?></span>
                        <span>ID: #<?php echo $g['id']; ?></span>
                    </div>
                    
                    <div class="suggestion-body">
                        <?php echo nl2br(htmlEscape($g['description'])); ?>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo $g['id']; ?>">
                            <input type="hidden" name="action" value="update_status">
                            
                            <label><strong>Estado:</strong></label>
                            <select name="status" style="padding: 5px; border-radius: 4px;">
                                <option value="submitted" <?php echo $g['status'] == 'submitted' ? 'selected' : ''; ?>>Recibido</option>
                                <option value="acknowledged" <?php echo $g['status'] == 'acknowledged' ? 'selected' : ''; ?>>Reconocido</option>
                                <option value="investigating" <?php echo $g['status'] == 'investigating' ? 'selected' : ''; ?>>Investigando</option>
                                <option value="resolved" <?php echo $g['status'] == 'resolved' ? 'selected' : ''; ?>>Resuelto</option>
                                <option value="closed" <?php echo $g['status'] == 'closed' ? 'selected' : ''; ?>>Cerrado</option>
                            </select>
                            
                            <div style="margin-top: 0.5rem;">
                                <label><strong>Resolución / Notas:</strong></label>
                                <textarea name="resolution" style="width: 100%; padding: 5px;" rows="2"><?php echo htmlEscape($g['resumen_resolutorio']); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem; font-size: 0.9rem; padding: 5px 15px;">Actualizar Reporte</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
