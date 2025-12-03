<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

$pdo = getPDO();
$userPoints = getUserPoints($pdo, $_SESSION['id_usr']);

// Redirect if not unlocked
if ($userPoints < 100) {
    header("Location: LP.php");
    exit;
}

// Get user stats for display
$impactStats = getUserImpactStats($pdo, $_SESSION['id_usr']);
$total_blogs = countUserPosts($pdo, $_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Área Secreta Desbloqueada! - CbNoticias</title>
    <link rel="stylesheet" href="css/LP.css">
    <link rel="stylesheet" href="css/secret.css">
</head>
<body>
    
    <nav class="nav">
        <div class='logo'>
            <h2>CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
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
        <div class="secret-hero">
            <div class="celebration-emoji">🎉✨🏆</div>
            <h1 class="secret-title">¡Felicidades, <?php echo htmlEscape($_SESSION['nombre']); ?>!</h1>
            <p class="secret-subtitle">Has desbloqueado el área secreta con <?php echo $userPoints; ?> puntos de contribución</p>
        </div>

        <div class="secret-content">
            <div class="achievement-card">
                <h2>🌟 Logro Desbloqueado</h2>
                <h3 class="achievement-title">Contribuidor Elite</h3>
                <p class="achievement-description">
                    Has demostrado un compromiso excepcional con la comunidad CbNoticias. 
                    Tu dedicación y contribuciones han hecho de este un mejor lugar para todos.
                </p>
            </div>

            <div class="stats-showcase">
                <h2>📊 Tus Logros</h2>
                <div class="stats-grid">
                    <div class="stat-highlight">
                        <div class="stat-icon">✍️</div>
                        <div class="stat-value"><?php echo $total_blogs; ?></div>
                        <div class="stat-description">Blogs Publicados</div>
                    </div>
                    <div class="stat-highlight">
                        <div class="stat-icon">📚</div>
                        <div class="stat-value"><?php echo $impactStats['resources_shared']; ?></div>
                        <div class="stat-description">Recursos Compartidos</div>
                    </div>
                    <div class="stat-highlight">
                        <div class="stat-icon">👍</div>
                        <div class="stat-value"><?php echo $impactStats['helpful_votes']; ?></div>
                        <div class="stat-description">Votos Útiles Recibidos</div>
                    </div>
                    <div class="stat-highlight">
                        <div class="stat-icon">💡</div>
                        <div class="stat-value"><?php echo $impactStats['suggestions_made']; ?></div>
                        <div class="stat-description">Sugerencias Propuestas</div>
                    </div>
                </div>
            </div>

            <div class="secret-message">
                <h2>🎁 Mensaje Especial</h2>
                <div class="message-content">
                    <p>
                        Eres parte de una comunidad exclusiva de contribuidores que han alcanzado el nivel más alto de participación.
                    </p>
                    <p>
                        Tu voz importa, y tus contribuciones han ayudado a crear un espacio más rico y colaborativo para todos los estudiantes.
                    </p>
                    <p class="message-cta">
                        ¡Sigue así! Cada blog, recurso, comentario y sugerencia que compartes hace la diferencia. 🚀
                    </p>
                </div>
            </div>

            <div class="return-section">
                <a href="LP.php" class="btn btn-primary">Volver al Hub</a>
                <a href="Write.php" class="btn btn-accent">Escribir Nuevo Blog</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        &copy; 2025 CbNoticias. Suerte
    </footer>

    <script>
        // Celebration animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const hero = document.querySelector('.secret-hero');
            const cards = document.querySelectorAll('.achievement-card, .stats-showcase, .secret-message');
            
            hero.style.opacity = '0';
            hero.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                hero.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                hero.style.opacity = '1';
                hero.style.transform = 'scale(1)';
            }, 100);
            
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 400 + (index * 150));
            });
        });
    </script>
</body>
</html>
