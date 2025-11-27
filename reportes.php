<?php
session_start();
require_once 'lib/common.php';

// Require admin/moderator access (nivel >= 1) - asegura que solo admins puedan acceder a los reportes
requiereAdmin(1);

$pdo = getPDO();

// Consulta simple: muestra todos los reportes del más nuevo al más antiguo
$sql = "
    SELECT 
        p.id,
        p.subject,
        p.description,
        p.category,
        p.severity,
        p.status,
        p.is_anonimo,
        p.reporter_id,
        p.submitted_at,
        p.admin_asignado,
        u.usuario as reporter_username,
        u.nombre as reporter_name
    FROM problemasHH p
    LEFT JOIN user u ON p.reporter_id = u.id_usr
    ORDER BY p.submitted_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get current admin info
$adminInfo = obtenerAdminInfo($pdo, $_SESSION['id_usr']);
?>
<!DOCTYPE html>
<html lang="es">
<head> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Problemas - cbBlogs</title>
    <link rel="stylesheet" href="css/reportstyle.css">
</head>
     
<body>
    <!-- Standard Navigation -->
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="resources.php">Recursos</a>
            <a href="democracy.php">Tu Voz</a>
            <a href="Account-info.php">Mi Cuenta</a>
            <?php if (isAdmin()): ?>
                <a href="admin_dashboard.php" class="active">Panel Admin</a>
            <?php endif; ?>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="user-display">
            <span class="user-greeting">Hola,</span>
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>
    
    <div class="container"> 
        <div class="page-header">
            <h1>Reportes de Problemas HH</h1>
            <p>
                Mostrando todos los reportes del más reciente al más antiguo
            </p>
        </div>
        
        <div class="reportes-grid">
            <?php if (empty($reportes)): ?>
                <div class="no-reportes">
                    <p>No hay reportes disponibles.</p>
                </div>
            <?php else: ?>
                <?php foreach ($reportes as $reporte): ?>
                    <div class="reporte-card">
                        <div class="reporte-header">
                            <div class="reporte-title">
                                <strong>
                                    <?php 
                                    if ($reporte['is_anonimo']) {
                                        echo $reporte['subject'] ?: '[Reporte Anónimo]';
                                    } else {
                                        echo htmlEscape($reporte['subject'] ?: '[Sin asunto]');
                                    }
                                    ?>
                                </strong>
                                <span class="badge severity-<?php echo strtolower($reporte['severity']); ?>">
                                    <?php echo htmlspecialchars($reporte['severity']); ?>
                                </span>
                            </div>
                            <div class="badges">
                                <span class="badge status-<?php echo strtolower($reporte['status']); ?>">
                                    <?php echo htmlspecialchars($reporte['status']); ?>
                                </span>
                                <?php if ($reporte['is_anonimo']): ?>
                                    <span class="badge anonymous-badge">Anónimo</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="reporte-description">
                            <?php 
                            $description = $reporte['description'];
                            $preview = mb_substr($description, 0, 150);
                            if (mb_strlen($description) > 150) {
                                $preview .= '...';
                            }
                            echo htmlEscape($preview);
                            ?>
                        </div>
                        
                        <div class="reporte-meta">
                            <div class="meta-item">
                                <strong>ID:</strong> #<?php echo $reporte['id']; ?>
                            </div>
                            <div class="meta-item">
                                <strong>Categoría:</strong> <?php echo htmlEscape($reporte['category']); ?>
                            </div>
                            <div class="meta-item">
                                <strong>Reportero:</strong>
                                <?php 
                                // Anonimidad REAL!: Oculta el nombre del reportero si is_anonimo = 1
                                if ($reporte['is_anonimo']) {
                                    echo '<span class="anonymous-badge">ANÓNIMO</span>';
                                } else {
                                    echo htmlEscape($reporte['reporter_name']);
                                }
                                ?>
                            </div>
                            <div class="meta-item">
                                <strong>Fecha:</strong> <?php echo TraduceSQLfecha($reporte['submitted_at']); ?>
                            </div>
                        </div>
                        
                        <div class="reporte-footer">
                            <div class="meta-item">
                                <?php if ($reporte['admin_asignado']): ?>
                                    <strong>Asignado a admin ID:</strong> <?php echo $reporte['admin_asignado']; ?>
                                <?php else: ?>
                                    <em>Sin asignar</em>
                                <?php endif; ?>
                            </div>
                            <a href="admin_grievances.php?id=<?php echo $reporte['id']; ?>" class="view-button">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
