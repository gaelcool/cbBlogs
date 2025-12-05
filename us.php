<?php
require_once 'lib/common.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();


    $usuario = trim($_POST['user']);
    $clave = $_POST['clave'];

    try {
        // intentar loguear
        $userData = intentaLogin($pdo, $usuario, $clave);

        if ($userData) {
            // login exitoso
            login($userData['user'], $userData['nombre'], $userData['genero_lit_fav']);

            header('Location: LP.php');
            exit();
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    } catch (Exception $e) {
        $error = 'Error en el sistema: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - CbNoticias</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;700&family=Fira+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/us.css">

</head>

<body>


    <div class="wrapper">
        <div class="top_right"> <button id="hamButton" class="hambtn">&#9776;</button></div>
        <header>

            <div class="logo">
                <h1>CbNoticias</h1>
            </div>

            <nav>
                <a href="index.php">Inicio</a>
                <a href="registrar.php">Registro</a>
            </nav>

            <form action="login.php" method="POST" class="login-form">
                <input type="text" name="user" placeholder="Usuario" required>
                <input type="password" name="clave" placeholder="Contraseña" required>
                <button type="submit" id="submitBtn">Entrar a tu comunidad</button>
            </form>

            <div class="smallUnder">
                <a href="logout.php" class="active">Cerrar sesión si tienes una abierta</a>
            </div>
        </header>

        <!-- menú interactivo del lado derecho -->
        <aside class="column">


            <h3 style="color: var(--accent); text-align: center; margin-bottom: 20px;"> Información Rápida</h3>

            <!-- item del menú 1: cómo funciona -->
            <div class="menu-item">
                <div class="menu-header" onclick="toggleMenu(this)">
                    <span>¿Cómo Funciona?</span>
                    <span class="menu-icon">▼</span>
                </div>
                <div class="menu-content">
                    <p>CbNoticias es presentado como un sitio de blogs, pero cuenta con varias secciones para que
                        crezcan pequeñas comunidades dentro del mismo, e incluso talleres.</p>
                    <ul>
                        <li>Regístrate con tu credencial estudiantil</li>
                        <li>Comparte contenido y sube de nivel</li>
                        <li>Participa en comunidades de tu interés</li>
                        <li>Mantente conectado con tu escuela</li>
                    </ul>
                </div>

            </div>

            <!-- item del menú 2: características -->
            <div class="menu-item">
                <div class="menu-header" onclick="toggleMenu(this)">
                    <span> Características</span>
                    <span class="menu-icon">▼</span>
                </div>
                <div class="menu-content">
                    <ul>
                        <li>Blogs y publicaciones estudiantiles</li>
                        <li>Sistema de grados automático</li>
                        <li>Comunidades y talleres</li>
                        <li>Acceso exclusivo para estudiantes</li>
                        <li>Moderación entre pares</li>
                        <li>Renovación anual de cuentas</li>
                    </ul>
                </div>
            </div>

            <!-- item del menú 3: seguridad -->
            <div class="menu-item">
                <div class="menu-header" onclick="toggleMenu(this)">
                    <span>Seguridad</span>
                    <span class="menu-icon">▼</span>
                </div>
                <div class="menu-content">
                    <ul>
                        <li>Acceso cerrado solo para estudiantes</li>
                        <li>Base de datos reiniciada cada 3 años</li>
                        <li>Sin mensajes privados 1-1</li>
                        <li>Todo el contenido es visible para la comunidad</li>
                        <li>Sistema de moderación estudiantil</li>
                    </ul>
                </div>
            </div>
            <div class="bottom_decoration"></div>
        </aside>



        <main class="container">
            <section class="intro">
                <h2>Sobre Nosotros</h2>
                <p>Cbnoticias busca fomentar comunicación estudiantil.</p>
                <div class='rightbot'><strong>Abierto las 24 horas del día, los 365 días del año.</strong></div>
            </section>

            <section class="localANDtrian">
                <div class="location">
                    <h3>Nuestra Ubicación:</h3>
                    <div class="map-container">
                        <img src="img/CroquisCbtisMainbuilding.jpg" alt="Mapa del CBTis 03">
                    </div>
                    <p class="address">
                        <strong>Dirección:</strong> Av. Principal 123, Tlaxcala, México<br>
                    </p>
                </div>
            </section>

            <section class="certifications">
                <h3>Iniciativas:</h3>
                <div class="cert-grid">
                    <div class="info-card glass-container">
                        <div class="cert-card">
                            <img src="img/cert1.gif" alt="Promover comunicación">
                            <h4>Política social</h4>
                            <p>Se busca fomentar y diseñar una comunidad no replicable. Incrementamos la compasión entre
                                el cuerpo estudiantil ofreciendo un lugar privado y seguro donde pueden comunicar ideas
                                y apoyarse mutuamente.</p>
                        </div>
                    </div>

                    <div class="info-card glass-container">
                        <div class="cert-card">
                            <img src="img/cert2.gif" alt="Educación emocional">
                            <h4>Desarrollo social dentro del CBTis</h4>
                            <p>Fortalecer el conocimiento mutuo entre estudiantes del CBTis 03, creando un espacio donde
                                ambos turnos y todos los grados puedan estar al corriente con las novedades de la
                                comunidad estudiantil.</p>
                        </div>
                    </div>

                    <div class="info-card glass-container">
                        <div class="cert-card">
                            <img src="img/yah4.gif" alt="Chisme sin lastimar">
                            <h4>Representación virtual de la comunidad construida por:</h4>
                            <p>Una plataforma estudiantil completa y segura</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <section class="finger-drawing">
            <h3>¡Dibuja algo!</h3>
            <p>Usa tu dedo o ratón para dejar tu marca.</p>
            <canvas id="drawingCanvas"></canvas>
        </section>

        <footer>
            <div class="ftback"></div>
            <div class="footer-content">

                <div class="footer-contact">
                    <div class="bottom_left">
                        <p>2025 CbNoticias© Suerte.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        function toggleMenu(header) {
            const menuItem = header.parentElement;
            const allMenuItems = document.querySelectorAll('.menu-item');

            // cerrar otros menús
            allMenuItems.forEach(item => {
                if (item !== menuItem && item.classList.contains('active')) {
                    item.classList.remove('active');
                }
            });

            // alternar menú actual
            menuItem.classList.toggle('active');
        }



        document.getElementById("hamButton").addEventListener("click", () => {
            document.querySelector(".column").classList.toggle("open")
            document.body.classList.toggle("menu-open");
        });

        // Finger Drawing Logic
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        // Set drawing style
        ctx.strokeStyle = '#FF69B4'; // Hot pink to match theme
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.lineWidth = 3;

        function draw(e) {
            if (!isDrawing) return;

            e.preventDefault(); // Prevent scrolling on touch

            let clientX, clientY;

            if (e.type.includes('touch')) {
                const touch = e.touches[0];
                clientX = touch.clientX;
                clientY = touch.clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }

            const rect = canvas.getBoundingClientRect();
            const x = clientX - rect.left;
            const y = clientY - rect.top;

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(x, y);
            ctx.stroke();

            [lastX, lastY] = [x, y];
        }

        function startDrawing(e) {
            isDrawing = true;

            let clientX, clientY;
            if (e.type.includes('touch')) {
                const touch = e.touches[0];
                clientX = touch.clientX;
                clientY = touch.clientY;
            } else {
                clientX = e.clientX;
                clientY = e.clientY;
            }

            const rect = canvas.getBoundingClientRect();
            [lastX, lastY] = [clientX - rect.left, clientY - rect.top];
        }

        function stopDrawing() {
            isDrawing = false;
        }

        // Event listeners
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, {
            passive: false
        });
        canvas.addEventListener('touchmove', draw, {
            passive: false
        });
        canvas.addEventListener('touchend', stopDrawing);

        // Scroll detection for fade-in
        const drawingSection = document.querySelector('.finger-drawing');

        window.addEventListener('scroll', () => {
            const scrollPercent = (window.scrollY + window.innerHeight) / document.documentElement.scrollHeight;

            if (scrollPercent > 0.7) {
                drawingSection.style.opacity = '1';
                drawingSection.style.transform = 'translateY(0)';
            }
        });
    </script>
</body>

</html>