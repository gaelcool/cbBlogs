<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

$pdo = getPDO();

// Get admin info to determine if we show admin links
$adminInfo = obtenerAdminInfo($pdo, $_SESSION['id_usr']);

// Fetch active suggestions
$stmt = $pdo->prepare("
    SELECT s.*, u.nombre as author_name,
    (SELECT COUNT(*) FROM suggestion_supporters WHERE suggestion_id = s.id) as real_support_count
    FROM suggestions s
    JOIN user u ON s.author_id = u.id_usr
    WHERE s.status != 'declined'
    ORDER BY s.created_at DESC
");
$stmt->execute();
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Voz Cuenta - CbNoticias</title>
    <link rel="stylesheet" href="css/democracy.css">
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="resources.php">Recursos</a>
            <a href="democracy.php" class="active">Tu Voz</a>
            <a href="Account-info.php">Mi Cuenta</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="user-display">
            <span class="user-greeting">Hola,</span>
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>

    <div class="democracy-container">
        <div class="page-header">
            <h1> <div id="movingIcon" class="iconDemocracy"></div><span style="position:relative; z-index:2;">Tu Voz Cuenta</span></h1>
            <p>Participa en la mejora de nuestra escuela</p>
            
            <div class="action-buttons">
                <a href="submit_suggestion.php" class="btn btn-primary">💡 Nueva Sugerencia</a>
                <a href="submit_grievance.php" class="btn btn-warning">⚠️ Reportar Problema</a>
                
                <?php if ($adminInfo['es_admin']): ?>
                    <a href="reportes.php" class="btn" style="background: #6f42c1; color: white;">🛡️ Panel de Reportes</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="suggestions-grid">
            <?php if (empty($suggestions)): ?>
                <div class="no-content">
                    <h3>💡 No hay sugerencias activas</h3>
                    <p>¡Sé el primero en proponer una mejora!</p>
                </div>
            <?php else: ?>
                <?php foreach ($suggestions as $sug): ?>
                    <div class="suggestion-card status-<?php echo $sug['status']; ?>">
                        <div class="suggestion-header">
                            <span class="category-badge"><?php echo htmlEscape($sug['category']); ?></span>
                            <span class="status-badge <?php echo $sug['status']; ?>">
                                <?php 
                                    $statusMap = [
                                        'pending' => 'Pendiente',
                                        'under_review' => 'En Revisión',
                                        'in_progress' => 'En Progreso',
                                        'implemented' => 'Implementado',
                                        'declined' => 'Declinado'
                                    ];
                                    echo $statusMap[$sug['status']] ?? $sug['status'];
                                ?>
                            </span>
                        </div>
                        
                        <h3 class="suggestion-title"><?php echo htmlEscape($sug['title']); ?></h3>
                        
                        <div class="suggestion-meta">
                            <span>👤 <?php echo $sug['is_anonymous'] ? 'Estudiante Anónimo' : htmlEscape($sug['author_name']); ?></span>
                            <span>📅 <?php echo TraduceSQLfecha($sug['created_at']); ?></span>
                        </div>
                        
                        <div class="suggestion-body">
                            <?php echo nl2br(htmlEscape($sug['description'])); ?>
                        </div>
                        
                        <?php if ($sug['admin_response']): ?>
                        <div class="admin-response">
                            <strong>Respuesta Admin:</strong>
                            <p><?php echo htmlEscape($sug['admin_response']); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="suggestion-footer">
                            <div class="support-count">
                                ❤️ <?php echo $sug['real_support_count']; ?> apoyos
                            </div>
                            <!-- Future: Add support button functionality -->
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
