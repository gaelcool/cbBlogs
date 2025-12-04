<?php
require_once 'lib/common.php';
$pdo = getPDO();
$stmt = $pdo->query("SELECT id, title, author_name, created_at, content FROM post ORDER BY created_at DESC LIMIT 1");
$featuredPost = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CbBlogs - Tu Foro Estudiantil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;700&family=Fira+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/indexstyles.css">
</head>

<body>
    <header>
        <div class="nav-container">
            <div class="logo"> CbBlogs</div>
            <nav class="nav-links">
                <a href="login.php">Iniciar Sesión</a>
                <a href="registrar.php">Registro</a>
            </nav>

        </div>
    </header>

    <main>
        <section class="hero">
            <div class="cert-card">
                <div class="hero-content glass-container">
                    <h1> Bienvenido a CbBlogs</h1>
                    <div class="section-iconOG"></div>
                    <p class="subtitle">Tu Foro Estudiantil del CBTis 03</p>
                    <p class="hero-description">
                        Tu voz importa. CbBlogs es la plataforma exclusiva para estudiantes del CBTis 03 donde puedes
                        compartir ideas,
                        leer experiencias y conectar con tu comunidad. Un espacio seguro, moderado por estudiantes, para
                        unir a ambos turnos y todos los grados.
                    </p>
                </div>
            </div>
        </section>

        <?php if ($featuredPost): ?>
            <section class="featured-post-section">
                <div class="glass-container" style="padding: 2.5rem; margin: 2rem 0;">
                    <h2 style="font-size: 1.8rem; color: var(--accent); margin-bottom: 1.5rem; text-align: center;">
                        Blog Destacado
                    </h2>
                    <div class="featured-post">
                        <h3 style="font-size: 1.5rem; color: var(--text); margin-bottom: 0.75rem;">
                            <?php echo htmlspecialchars($featuredPost['title']); ?>
                        </h3>
                        <p style="color: var(--text-light); margin-bottom: 1rem; font-size: 0.9rem;">
                            Por <?php echo htmlspecialchars($featuredPost['author_name']); ?>
                            - <?php echo date('d/m/Y', strtotime($featuredPost['created_at'])); ?>
                        </p>
                        <p style="color: var(--text); line-height: 1.6; margin-bottom: 1.5rem;">
                            <?php
                            $preview = mb_substr(strip_tags($featuredPost['content']), 0, 200);
                            echo htmlspecialchars($preview) . '...';
                            ?>
                        </p>
                        <p style="text-align: center; color: var(--text-light);">
                            <a href="registrar.php" style="color: var(--accent); font-weight: 600;">Regístrate</a>
                            para leer más historias como esta
                        </p>
                    </div>
                    <div class="cta-buttons">
                        <a href="login.php" class="btn btn-secondary">Ya tengo cuenta</a>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="why-join glass-container" style="margin-bottom: 40px; padding: 2.5rem;">
            <h2 class="why-join-title">¿Por qué unirte?</h2>
            <div class="benefits-grid"
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div class="benefit-item" style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">:O</div>
                    <h3 style="margin-bottom: 0.5rem;">Expresa tus Ideas</h3>
                    <p>Publica blogs sobre temas que te apasionan y recibe feedback de la comunidad.</p>
                </div>
                <div class="benefit-item" style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">❤️</div>
                    <h3 style="margin-bottom: 0.5rem;">Conecta</h3>
                    <p>Conoce estudiantes de otros semestres y especialidades con intereses similares.</p>
                </div>
                <div class="benefit-item" style="text-align: center;">
                    <div style="font-size: 2.5rem; margin-bottom: 1rem;">:3</div>
                    <h3 style="margin-bottom: 0.5rem;">Participa</h3>
                    <p>Vota en encuestas y propuestas para mejorar nuestra escuela.</p>
                </div>
            </div>
        </section>

        <div class="info-grid">
            <div class="info-card glass-container">
                <h2>Seguridad y Privacidad</h2>
                <ul class="info-list" style="font-weight: bold;">
                    <li>Acceso cerrado solo para estudiantes</li>
                    <li>Base de datos reiniciada cada 3 años</li>
                    <li>Sin mensajes privados 1-1</li>
                    <li>Todo el contenido es visible para la comunidad</li>
                    <li>Sistema de moderación estudiantil</li>
                </ul>
            </div>

            <div class="info-card glass-container">
                <h2>Sobre la Institución</h2>
                <p><strong>CBTis 03 - Tlaxcala</strong></p>
                <p>
                    Centro de Bachillerato Tecnológico industrial y de servicios, formando estudiantes
                    con excelencia académica y valores. CbBlogs es una iniciativa estudiantil para
                    fortalecer la comunicación dentro de nuestra comunidad educativa.
                </p>
            </div>

            <div class="info-card glass-container">
                <h2>Equipo de Desarrollo</h2>
                <p>
                    Creado por estudiantes del CBTis 03 con el objetivo de mejorar la comunicación
                    y convivencia estudiantil.
                </p>
                <ul class="info-list">
                    <li>Plataforma desarrollada por y para estudiantes</li>
                    <li>Sistema de moderación comunitario</li>
                    <li>Enfoque en seguridad y privacidad</li>
                </ul>
            </div>



        </div>
    </main>


    <a href="us.php" class="help-button" title="Más Información">?</a>

    <footer>
        <div class="footer-content">
            <h3> CbBlogs</h3>
            <p>Tu foro estudiantil del CBTis 03</p>
            <p style="margin-top: .6rem; font-size: 0.9rem;">
                &copy; 2025 CbBlogs - Suerte, Tlaxcala
            </p>
        </div>
    </footer>

    <script>
        // scroll suave para links internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // motion blur cuando haces scroll
        let scrollTimeout;
        window.addEventListener('scroll', function () {
            document.body.classList.add('scrolling');

            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function () {
                document.body.classList.remove('scrolling');
            }, 150);
        });


        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.info-card, .featured-post-section').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease-out';
            observer.observe(card);
        });
    </script>
</body>

</html>