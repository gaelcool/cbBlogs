<?php
require_once 'lib/common.php';
session_start();
requiereLogin();


$blogs = fetchAllPosts();

// calcular conteo de palabras y tiempo de lectura para cada blog
foreach ($blogs as &$blog) {
    $wordCount = str_word_count(strip_tags($blog['content']));
    $blog['palabra_count'] = $wordCount;
    $blog['tiempo_lectura'] = max(1, ceil($wordCount / 200)); // asumiendo 200 palabras por minuto
    
    $blog['titulo'] = $blog['title'];
    $blog['subtitulo'] = $blog['subtitle'];
    $blog['contenido'] = convertnewlines($blog['content']);
    $blog['autor'] = $blog['author_name'];
    $blog['fecha_creacion'] = $blog['created_at'];
    // $blog['id'] is already set from fetchAllPosts
}
unset($blog); // deshacer

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leer Blogs - CbNoticias</title>
    <link rel="stylesheet" href="css/read.css">
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="Write.php">Escribir</a>
            <a href="Account-info.php">Mi Cuenta</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="user-display">
            <span class="user-greeting">Hola,</span>
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>

    <div class="read-container">
        <div class="page-header">
            <h1> <div id="movingIcon" class="iconOG"></div><span style="position:relative; z-index:2;">Explorar Blogs</span></h1>
            <p>Descubre artículos fascinantes de nuestra comunidad</p>
        </div>

        <div class="filter-section">
            <div class="filter-row">
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar en títulos y contenido...">
                <select id="tagFilter" class="filter-select">
                    <option value="">Todos los géneros</option>
                    <option value="Ficción">Ficción</option>
                    <option value="No Ficción">No Ficción</option>
                    <option value="Ciencia Ficción">Ciencia Ficción</option>
                    <option value="Romance">Romance</option>
                    <option value="Misterio">Misterio</option>
                    <option value="Fantasía">Fantasía</option>
                    <option value="Historia">Historia</option>
                    <option value="Biografía">Biografía</option>
                    <option value="Poesía">Poesía</option>
                    <option value="General">General</option>
                </select>
                <select id="sortFilter" class="filter-select">
                    <option value="newest">Más recientes</option>
                    <option value="oldest">Más antiguos</option>
                    <option value="longest">Más largos</option>
                    <option value="shortest">Más cortos</option>
                </select>
            </div>
        </div>

        <div id="blogsContainer">
            <?php if (empty($blogs)): ?>
                <div class="no-blogs">
                    <h3>🔍 No hay blogs aún</h3>
                    <p>¡Sé el primero en escribir un blog!</p>
                    <a href="Write.php" class="btn" style="margin-top: 1rem;">Escribir Blog</a>
                </div>
            <?php else: ?>
                <?php foreach ($blogs as $blog): ?>
                    <div class="blog-card" data-tag="<?php echo htmlEscape($blog['tag']); ?>" data-author="<?php echo htmlEscape($blog['autor']); ?>" data-date="<?php echo htmlEscape($blog['fecha_creacion']); ?>" data-words="<?php echo $blog['palabra_count']; ?>">
                        <div class="blog-header">
                            <div style="flex: 1;">
                                <h3 class="blog-title"><?php echo htmlEscape($blog['titulo']); ?></h3>
                                <?php if (!empty($blog['subtitulo'])): ?>
                                    <p class="blog-subtitle"><?php echo htmlEscape($blog['subtitulo']); ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="blog-tag"><?php echo htmlEscape($blog['tag']); ?></span>
                        </div>
                        
                        <div class="blog-meta">
                            <span class="blog-author">👤 <?php echo htmlEscape($blog['autor']); ?></span>
                            <span class="blog-date">📅 <?php echo TraduceSQLfecha($blog['fecha_creacion']); ?></span>
                        </div>
                        
                        <div class="blog-stats">
                            <span class="stat-badge">📊 <?php echo $blog['palabra_count']; ?> palabras</span>
                            <span class="stat-badge">⏱️ <?php echo $blog['tiempo_lectura']; ?> min lectura</span>
                        </div>
                        
                        <div class="blog-preview">
                            <?php 
                            $preview = substr($blog['content'], 0, 200);
                            if (strlen($blog['content']) > 200) {
                                $preview .= '...';
                            }
                            echo $preview;
                            ?>
                        </div>
                        
                        <div class="card-actions">
                            <a href="blog_post.php?id=<?php echo $blog['id']; ?>" class="read-more-btn">🔗 Ver página completa</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="toast" class="toast">
        <div class="toast-icon">&check;</div>
        <div class="toast-message">¡Blog publicado exitosamente!</div>
    </div>

    <script>
        window.addEventListener('load', () => {
            const icon = document.getElementById('movingIcon');
            const searchInput = document.getElementById('searchInput');
            
            if (icon && searchInput) {
                // usar requestAnimationFrame para asegurar que el layout está listo
                requestAnimationFrame(() => {
                    // Small delay to allow any final layout shifts
                    setTimeout(() => {
                        const iconRect = icon.getBoundingClientRect();
                        const inputRect = searchInput.getBoundingClientRect();
                        
                        const iconCenterX = iconRect.left + iconRect.width / 2;
                        const iconCenterY = iconRect.top + iconRect.height / 2;
                        
                        // Target position: right side of input, vertically centered
                        // inputRect.right - 30px padding
                        const targetX = inputRect.right - 30; 
                        const targetY = inputRect.top + inputRect.height / 2;
                        
                        const deltaX = targetX - iconCenterX;
                        const deltaY = targetY - iconCenterY;
                        
                        icon.style.setProperty('--tx', `${deltaX}px`);
                        icon.style.setProperty('--ty', `${deltaY}px`);
                        
                        // Force reflow
                        void icon.offsetWidth;
                        
                        icon.classList.add('animate-icon');
                    }, 150);
                });
            }

            // Check for success status
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('status') === 'success') {
                const toast = document.getElementById('toast');
                setTimeout(() => {
                    toast.classList.add('show');
                }, 500);
                
                // Hide after 4 seconds
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 4500);
                
                // Clean URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        function toggleContent(blogId) {
            const content = document.getElementById('content-' + blogId);
            const btnText = document.getElementById('btn-text-' + blogId);
            
            if (content.classList.contains('show')) {
                content.classList.remove('show');
                btnText.textContent = 'Leer más';
            } else {
                content.classList.add('show');
                btnText.textContent = 'Leer menos';
            }
        }
        
        // filtros y funcionalidad de búsqueda
        function filterBlogs() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const tagFilter = document.getElementById('tagFilter').value;
            const sortFilter = document.getElementById('sortFilter').value;
            const blogCards = Array.from(document.querySelectorAll('.blog-card'));
            
            let visibleBlogs = [];
            
            blogCards.forEach(card => {
                const title = card.querySelector('.blog-title').textContent.toLowerCase();
                const preview = card.querySelector('.blog-preview').textContent.toLowerCase();
                const tag = card.getAttribute('data-tag');
                const author = card.getAttribute('data-author').toLowerCase();
                
                const matchesSearch = searchTerm === '' || 
                    title.includes(searchTerm) || 
                    preview.includes(searchTerm) ||
                    author.includes(searchTerm);
                
                const matchesTag = tagFilter === '' || tag === tagFilter;
                
                if (matchesSearch && matchesTag) {
                    card.style.display = 'block';
                    visibleBlogs.push(card);
                } else {
                    card.style.display = 'none';
                }
            });
            
            // ordenar blogs
            if (sortFilter === 'newest') {
                visibleBlogs.sort((a, b) => {
                    const dateA = new Date(a.getAttribute('data-date'));
                    const dateB = new Date(b.getAttribute('data-date'));
                    return dateB - dateA;
                });
            } else if (sortFilter === 'oldest') {
                visibleBlogs.sort((a, b) => {
                    const dateA = new Date(a.getAttribute('data-date'));
                    const dateB = new Date(b.getAttribute('data-date'));
                    return dateA - dateB;
                });
            } else if (sortFilter === 'longest') {
                visibleBlogs.sort((a, b) => {
                    const wordsA = parseInt(a.getAttribute('data-words'));
                    const wordsB = parseInt(b.getAttribute('data-words'));
                    return wordsB - wordsA;
                });
            } else if (sortFilter === 'shortest') {
                visibleBlogs.sort((a, b) => {
                    const wordsA = parseInt(a.getAttribute('data-words'));
                    const wordsB = parseInt(b.getAttribute('data-words'));
                    return wordsA - wordsB;
                });
            }
            
            // reordenar en el DOM
            const container = document.getElementById('blogsContainer');
            visibleBlogs.forEach(card => {
                container.appendChild(card);
            });
        }
        
        // agregar event listeners
        document.getElementById('searchInput').addEventListener('input', filterBlogs);
        document.getElementById('tagFilter').addEventListener('change', filterBlogs);
        document.getElementById('sortFilter').addEventListener('change', filterBlogs);
        
        // inicializar
        document.addEventListener('DOMContentLoaded', function() {
            filterBlogs();
        });
    </script>
</body>
</html>