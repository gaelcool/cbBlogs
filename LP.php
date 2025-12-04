<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit;
}

$pdo = getPDO();
$user_blogs  = countUserPosts($pdo, $_SESSION['usuario']);
$total_blogs = countTotalPosts($pdo);


$impactStats = getUserImpactStats($pdo, $_SESSION['id_usr']);

$progressPoints = getUserPoints($pdo, $_SESSION['id_usr']);
$progressPercent = min(100, $progressPoints); 

$progressOpacity = 0.1 + (min($progressPoints, 100) / 100) * 0.9;
$isUnlocked = $progressPoints >= 100;


$stmt = $pdo->query("
    SELECT ic.*, u.nombre as implementer_name 
    FROM implemented_changes ic
    JOIN user u ON ic.implemented_by = u.id_usr
    ORDER BY ic.implemented_at DESC
    LIMIT 3
");
$recentChanges = $stmt->fetchAll(PDO::FETCH_ASSOC);



$stmt = $pdo->query("
    SELECT id, title, author_name, created_at
    FROM post
    ORDER BY created_at DESC
    LIMIT 3
");
$trendingPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT COUNT(*) FROM suggestions WHERE status = 'pending'");
$pendingSuggestions = $stmt->fetchColumn();

$hour = (int)date('G');     // Get the current hour in 24‑hour format (0–23)


if ($hour < 12) {
    $greeting = "Buenos días";
} elseif ($hour < 18) {
    $greeting = "Buenas tardes";
} else {
    $greeting = "Buenas noches";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CbNoticias - Hub Central</title>
    <link rel="stylesheet" href="css/LP.css">
</head>
<body>
    
    <nav class="nav">
        <div class='logo'>
            <h2> CbBlogs</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php" class="active">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="resources.php">Recursos</a>
            <a href="democracy.php">Tu Voz</a>
            <a href="Account-info.php">Mi Cuenta</a>
            <?php if (isAdmin()): ?>
                <a href="admin_dashboard.php">Panel Admin</a>
            <?php endif; ?>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="user-display">
            <span class="user-greeting">Hola,</span>
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>

    <div class="landing-container">
        <div class="hero-section">
            <div class="hero-greeting">
                <h1><?php echo $greeting; ?>, <?php echo htmlEscape($_SESSION['nombre']); ?>!</h1>
                <p class="hero-subtitle">Bienvenido al Hub Central de CbNoticias</p>
            </div>
                <!-- Progress Button -->
            <?php if ($isUnlocked): ?>
                <a href="WriteWitMedia.php" class="btn-progress" style="opacity: <?php echo $progressOpacity; ?>; cursor: pointer;">
                    <span>🔓 Desbloqueado! (<?php echo $progressPoints; ?> puntos)</span>
                </a>
            <?php else: ?>
                <div class="btn-progress" style="opacity: <?php echo $progressOpacity; ?>; cursor: not-allowed;">
                    <span>🔒 <?php echo $progressPoints; ?>/100 puntos</span>
                </div>
            <?php endif; ?>

        </div>

        <!-- Hub Portals Grid -->
        <div class="hub-grid">
             <section class="top-lanes">
            <div class="portal-card portal-primary">
                <div class="portal-icon section-iconOG"></div>
                <h3 class="portal-title">Zona Creativa</h3>
                <p class="portal-description">
                    Comparte tus ideas, experiencias y conocimientos con la comunidad.
                </p>
                <div class="portal-stats">
                    <span class="stat-badge"><?php echo $user_blogs; ?> publicados</span>
                </div>
                <a href="Write.php" class="portal-btn btn btn-primary">Escribir Blog</a>
            </div>

            <div class="portal-card">
                <div class="portal-icon section-icon"></div>
                <h3 class="portal-title">Conecta</h3>
                <p class="portal-description">
                    Descubre artículos personales de tus compañeros.
                </p>
                <div class="portal-stats">
                    <span class="stat-badge"><?php echo $total_blogs; ?> publicados</span>
                </div>
                <a href="Read.php" class="portal-btn btn">Explorar Blogs</a>
            </div>
 </section>

 <div class="impact-section">
            <h2 class="section-heading">
                Tu Impacto en la Comunidad
            </h2>
            <div class="impact-stats">
                <div class="stat-card">
                    <span class="stat-number"><?php echo $total_blogs; ?></span>
                    <span class="stat-label">Blogs Escritos</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $impactStats['resources_shared']; ?></span>
                    <span class="stat-label">Recursos Compartidos</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $impactStats['helpful_votes']; ?></span>
                    <span class="stat-label">Votos Útiles</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?php echo $impactStats['suggestions_made']; ?></span>
                    <span class="stat-label">Sugerencias</span>
                </div>
            </div>
        </div>

            <section class="bottom-lanes">
            <div class="portal-card">
                <div class="portal-icon section-icon-study"></div>
                <h3 class="portal-title">Biblioteca</h3>
                <p class="portal-description">
                    Comparte y descarga material de estudio.
                </p>
                <div class="portal-stats">
                    <span class="stat-badge"><?php echo $impactStats['resources_shared']; ?> recursos</span>
                </div>
                <a href="resources.php" class="portal-btn btn">Ver Recursos</a>
            </div>

            <div class="portal-card portal-accent">
                <div class="portal-icon section-icon-tips"></div>
                <h3 class="portal-title">Consejo Estudiantil</h3>
                <p class="portal-description">
                    Propone mejoras, vota por ideas y reporta problemas.
                </p>
                <div class="portal-stats">
                    <span class="stat-badge stat-active"><?php echo $pendingSuggestions; ?> pendientes</span>
                    <span class="stat-badge"><?php echo $impactStats['suggestions_implemented']; ?> implementadas</span>
                </div>
                <a href="democracy.php" class="portal-btn btn btn-accent">Participar</a>
            </div>
        </div>
 </section>
        <?php if (!empty($trendingPosts)): ?>
        <div class="trending-section">
            <h2 class="section-heading">
                Publicaciones Populares
            </h2>
            <div class="trending-grid">
                <?php foreach ($trendingPosts as $post): ?>
                    <a href="Read.php?id=<?php echo $post['id']; ?>" class="trending-card">
                        <h4 class="trending-title"><?php echo htmlEscape($post['title']); ?></h4>
                        <div class="trending-meta">
                            <span class="trending-author">por <?php echo htmlEscape($post['author_name']); ?></span>
                            <span class="trending-date"><?php echo TraduceSQLfecha($post['created_at']); ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($recentChanges)): ?>
        <div class="changes-section">
            <h2 class="section-heading">
                Mejoras Recientes en la Escuela
            </h2>
            <div class="changes-grid">
                <?php foreach ($recentChanges as $change): ?>
                    <div class="change-card">
                        <h4 class="change-title"><?php echo htmlEscape($change['title']); ?></h4>
                        <p class="change-description"><?php echo htmlEscape($change['description']); ?></p>
                        <small class="change-date">
                            Implementado el <?php echo TraduceSQLfecha($change['implemented_at']); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
    
    <a href="us.php" class="help-button" title="Más Información">?</a>

    <footer class="footer">
        &copy; 2025 CbBlogs. Suerte  
        <div class="rotating-slogan">
            <p id="slogan-text">Comparte tus ideas</p>
        </div>
    </footer>

    <script>
        const slogans = [
            "Comparte tus ideas",
            "Lee historias increíbles",
            "Conecta con otros compañeros",
            "Mejora tu escuela",
            "Estudia mejor juntos"
        ];
        let currentIndex = 0;
        const sloganElement = document.getElementById('slogan-text');

        function rotateSlogan() {
            sloganElement.style.opacity = '0';

            setTimeout(() => {
                currentIndex = (currentIndex + 1) % slogans.length;
                sloganElement.textContent = slogans[currentIndex];
                sloganElement.style.opacity = '1';
            }, 500);
        }

        setInterval(rotateSlogan, 3000);
        
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.portal-card, .stat-card, .trending-card, .change-card');
            
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
    </script>
</body>
</html>