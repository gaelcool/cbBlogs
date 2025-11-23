<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit;
}

$pdo = getPDO();
$total_blogs = countUserPosts($pdo, $_SESSION['usuario']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CbNoticias - Panel Principal</title>

    <link rel="stylesheet" href="css/LP.css">
</head>
<body>
    
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
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
        <div class="welcome-section">
            <div class="user-info">
                <h3>Blogs totales: <?php echo $total_blogs; ?></h3>
                <p>Género favorito:  <?php echo $_SESSION['genero_lit_fav']; ?></p>
            </div>
            <h1>Tu Plataforma Escolar de Colaboracion Escrita</h1>

        </div>

        <div class="sections-grid">
            <div class="section-card">
                <div class="section-iconOG"></div>
                <h3 class="section-title">Escribir Blog</h3>
                <p class="section-description">
                    Crea y publica tus propios artículos. Comparte tus ideas, experiencias y conocimientos con la comunidad.
                </p>
                <a href="Write.php" class="section-btn btn">Comenzar a Escribir</a>
            </div>

            <div class="section-card">
                <div class="section-icon"></div>
                <h3 class="section-title">Leer Blogs</h3>
                <p class="section-description">
                    Descubre artículos fascinantes de otros usuarios. Explora diferentes temas y géneros literarios.
                </p>
                <a href="Read.php" class="section-btn btn">Explorar Blogs</a>
            </div>

            </div>

        </div>

    </div>
    <a href="us.php" class="help-button" title="Más Información">?</a>


    <footer class="footer">
        &copy; 2025 CbNoticias. Suerte  <div class="rotating-slogan">
        <p id="slogan-text">Comparte tus ideas</p>
    </div></footer>

    <script>

        const slogans = [
            "Comparte tus ideas",
            "Lee historias increíbles",
            "Conecta con otros compañeros"
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
        // agregar efectos interactivos
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.section-card');

            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>