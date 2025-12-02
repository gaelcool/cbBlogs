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
    $blog['contenido'] = convertNewlinesToParagraphs($blog['content']);
    $blog['autor'] = $blog['author_name'];
    $blog['fecha_creacion'] = $blog['created_at'];
    $blog['id'] = md5($blog['title'] . $blog['created_at']); // 
}
unset($blog); // deshacer

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leer Blogs - CbNoticias</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .read-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            color: var(--accent, #FF6B9D);
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .page-header p {
            color: var(--text, #333);
            font-size: 1.2rem;
        }
        
        .blog-card {
            background: var(--white, #fff);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .blog-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .blog-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .blog-title {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text, #333);
            margin-bottom: 0.5rem;
        }
        
        .blog-subtitle {

            font-size: 1.5rem;
            color: var(--text, #666);
            opacity: 0.8;
            margin-bottom: 0.5rem;
        }
        
        .blog-meta {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .blog-author {
            color: var(--accent, #FF6B9D);
            font-weight: 500;
        }
        
        .blog-date {
            color: var(--text, #666);
            opacity: 0.7;
            font-size: 0.9rem;
        }
        
        .blog-tag {
            background: var(--primary, #4A90E2);
            color: var(--white, #fff);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        
        .blog-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .stat-badge {
            background: var(--background, #f5f5f5);
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            color: var(--text, #333);
        }
        
        .blog-preview {
            color: var(--text, #333);
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .blog-content {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--secondary, #ddd);
            line-height: 1.8;
            color: var(--text, #333);
        }
        
        .blog-content.show {
            display: block;
        }
        
        .expand-btn {
            background: var(--accent, #FF6B9D);
            color: var(--white, #fff);
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .expand-btn:hover {
            background: #E55A9B;
        }
        
        .no-blogs {
            text-align: center;
            padding: 3rem;
            color: var(--text, #666);
            opacity: 0.7;
        }
        
        .no-blogs h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .filter-section {
            background: var(--white, #fff);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .filter-row {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-select, .search-input {
            padding: 8px 12px;
            border: 2px solid var(--secondary, #ddd);
            border-radius: 6px;
            background: var(--white, #fff);
            font-size: 0.9rem;
        }
        
        .search-input {
            flex: 1;
            min-width: 200px;
        }
        
        .btn {
            display: inline-block;
            background: var(--accent, #FF6B9D);
            color: var(--white, #fff);
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #E55A9B;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div>
            <h2>📰 CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="Write.php">Escribir</a>
            <a href="Account-info.php">Mi Cuenta</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="read-container">
        <div class="page-header">
            <h1>📖 Explorar Blogs</h1>
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
                            $preview = substr($blog['contenido'], 0, 200);
                            if (strlen($blog['contenido']) > 200) {
                                $preview .= '...';
                            }
                            echo nl2br(htmlEscape($preview));
                            ?>
                        </div>
                        
                        <div class="blog-content" id="content-<?php echo htmlEscape($blog['id']); ?>">
                            <?php echo nl2br(htmlEscape($blog['contenido'])); ?>
                        </div>
                        
                        <button class="expand-btn" onclick="toggleContent('<?php echo htmlEscape($blog['id']); ?>')">
                            <span id="btn-text-<?php echo htmlEscape($blog['id']); ?>">Leer más</span>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
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