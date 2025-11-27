<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

// Fetch resources
$pdo = getPDO();
$stmt = $pdo->prepare("
    SELECT r.*, u.nombre as uploader_name 
    FROM study_resources r
    JOIN user u ON r.uploader_id = u.id_usr
    WHERE r.is_approved = 1 OR r.uploader_id = :uid
    ORDER BY r.uploaded_at DESC
");
$stmt->execute([':uid' => $_SESSION['id_usr']]);
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recursos de Estudio - CbNoticias</title>
    <link rel="stylesheet" href="css/resources.css">
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="resources.php" class="active">Recursos</a>
            <a href="democracy.php">Tu Voz</a>
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
            <h1> <div id="movingIcon" class="iconResource"></div><span style="position:relative; z-index:2;">Recursos de Estudio</span></h1>
            <p>Comparte y descarga material de estudio con tu comunidad</p>
            <a href="resource_upload.php" class="btn btn-primary" style="margin-top: 1rem;">Subir Recurso</a>
        </div>

        <div class="filter-section">
            <div class="filter-row">
                <input type="text" id="searchInput" class="search-input" placeholder="Buscar recursos...">
                <select id="subjectFilter" class="filter-select">
                    <option value="">Todas las materias</option>
                    <option value="Matemáticas">Matemáticas</option>
                    <option value="Ciencias">Ciencias</option>
                    <option value="Historia">Historia</option>
                    <option value="Lengua">Lengua</option>
                    <option value="Inglés">Inglés</option>
                    <option value="Programación">Programación</option>
                    <option value="Otro">Otro</option>
                </select>
                <select id="gradeFilter" class="filter-select">
                    <option value="">Todos los semestres</option>
                    <option value="1">1er Semestre</option>
                    <option value="2">2do Semestre</option>
                    <option value="3">3er Semestre</option>
                    <option value="4">4to Semestre</option>
                    <option value="5">5to Semestre</option>
                    <option value="6">6to Semestre</option>
                </select>
            </div>
        </div>

        <div id="resourcesContainer">
            <?php if (empty($resources)): ?>
                <div class="no-blogs">
                    <h3>📚 No hay recursos aún</h3>
                    <p>¡Sé el primero en compartir un recurso!</p>
                    <a href="resource_upload.php" class="btn">Subir Recurso</a>
                </div>
            <?php else: ?>
                <?php foreach ($resources as $resource): ?>
                    <div class="blog-card resource-card" 
                         data-subject="<?php echo htmlEscape($resource['subject']); ?>" 
                         data-grade="<?php echo htmlEscape($resource['grade']); ?>"
                         data-uploader="<?php echo htmlEscape($resource['uploader_name']); ?>">
                        
                        <div class="blog-header">
                            <div style="flex: 1;">
                                <h3 class="blog-title"><?php echo htmlEscape($resource['title']); ?></h3>
                                <p class="blog-subtitle"><?php echo htmlEscape($resource['subject']); ?> • <?php echo $resource['grade']; ?>° Semestre</p>
                            </div>
                            <span class="blog-tag"><?php echo strtoupper($resource['resource_type']); ?></span>
                        </div>
                        
                        <div class="blog-meta">
                            <span class="blog-author">👤 <?php echo htmlEscape($resource['uploader_name']); ?></span>
                            <span class="blog-date">📅 <?php echo TraduceSQLfecha($resource['uploaded_at']); ?></span>
                        </div>
                        
                        <div class="blog-preview">
                            <?php echo htmlEscape($resource['description']); ?>
                        </div>
                        
                        <div class="card-actions">
                            <?php if ($resource['resource_type'] === 'link'): ?>
                                <a href="<?php echo htmlEscape($resource['external_url']); ?>" target="_blank" class="read-more-btn">🔗 Abrir Enlace</a>
                            <?php elseif ($resource['resource_type'] === 'file'): ?>
                                <a href="<?php echo htmlEscape($resource['file_path']); ?>" download class="read-more-btn">⬇️ Descargar Archivo</a>
                            <?php else: ?>
                                <a href="#" class="read-more-btn">📄 Ver Contenido</a>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Filter logic similar to Read.php
        function filterResources() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const subjectFilter = document.getElementById('subjectFilter').value;
            const gradeFilter = document.getElementById('gradeFilter').value;
            const cards = document.querySelectorAll('.resource-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.blog-title').textContent.toLowerCase();
                const subject = card.getAttribute('data-subject');
                const grade = card.getAttribute('data-grade');
                
                const matchesSearch = title.includes(searchTerm);
                const matchesSubject = subjectFilter === '' || subject === subjectFilter;
                const matchesGrade = gradeFilter === '' || grade === gradeFilter;
                
                if (matchesSearch && matchesSubject && matchesGrade) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        document.getElementById('searchInput').addEventListener('input', filterResources);
        document.getElementById('subjectFilter').addEventListener('change', filterResources);
        document.getElementById('gradeFilter').addEventListener('change', filterResources);
    </script>
</body>
</html>
