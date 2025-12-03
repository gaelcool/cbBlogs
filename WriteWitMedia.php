<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

$pdo = getPDO();
$userPoints = getUserPoints($pdo, $_SESSION['id_usr']);

// Redirigir si no tiene 100 puntos
if ($userPoints < 100) {
    header("Location: LP.php");
    exit;
}
// Obtener info del usuario
$genero_fav = $_SESSION['genero_lit_fav'] ?? 'General';
$userContributions = $_SESSION['user_contributions'] ?? 0;
$user_id = $_SESSION['id_usr'];
// obtener la imagen default del post
$currentImg = "img/blog_media/default.jpg";

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escribir Blog - CbNoticias</title>
    <link rel="stylesheet" href="css/write.css">
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-fishies"></div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="Write.php" class="active">Escribir</a>
            <a href="edit_blog_style.php">🎨 Estilo</a>
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
 <div class=glasscontainer>
    <div class="write-container">
        <div class="write-form">
            <h1> Escribir Nuevo Blog</h1> <div class="iconOG"></div>
            
            <form action="save-blog.php" method="POST" id="blogForm" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="titulo">Título del Blog</label>
                        <input type="text" name="titulo" id="titulo" placeholder="Escribe un título atractivo" maxlength="200" required>
                        <div class="char-counter" id="tituloCounter">0/200</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tag">Género/Tag</label>
                        <select name="tag" id="tag">
                            <option value="<?php echo htmlEscape($genero_fav); ?>"><?php echo htmlEscape($genero_fav); ?></option>
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
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="subtitulo">Subtítulo (opcional)</label>
                    <input type="text" name="subtitulo" id="subtitulo" placeholder="Un subtítulo descriptivo" maxlength="300">
                    <div class="char-counter" id="subtituloCounter">0/300</div>
                </div>
                <div class="style-section">
                    <h4>Fondo</h4>

                    <div class="form-group">
                        <label for="bgUpload">Subir Nuevo Fondo (Máx 2MB)</label>
                        <input type="file" id="bgUpload" name="file_path" accept=".jpg,.jpeg,.png,.webp">
                        <small>Subir una nueva imagen, reemplaza la actual.</small>
                    </div>
                </div>

                 <div class="form-group">
                        <label>Fondo Actual</label>
                        <div id="currentBgDisplay" class="bg-preview-box">
                            <?php if (!empty($currentImg['file_path'])): ?>
                                <img src="img/blog_media/<?php echo htmlEscape($currentImg['file_path']); ?>" alt="Current Background">
                                <span class="bg-name">Imagen Personalizada Subida</span>
                            <?php else: ?>
                                <span class="bg-name">Usando nada</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <div class="form-group">
                        <label for="contenido">Contenido del Blog</label>
                        <textarea name="contenido" id="contenido" placeholder="Escribe tu blog aquí..." required></textarea>
                    </div>
                </div>
                
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="stat-value" id="wordCount">0</div>
                        <div class="stat-label">Palabras</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="readingTime">0</div>
                        <div class="stat-label">Min. Lectura</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="charCount">0</div>
                        <div class="stat-label">Caracteres</div>
                    </div>
                </div>
                </div>
                <div class="submit-section">
                    <a href="LP.php" class="btn back-btn">← Volver</a>
                    <button type="submit" class="btn submit-btn">📝 Publicar Blog</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <script>
        // contadores de caracteres
        function updateCounter(input, counter, max) {
            const length = input.value.length;
            counter.textContent = `${length}/${max}`;
            
            if (length > max * 0.9) {
                counter.style.color = 'var(--error)';
            } else {
                counter.style.color = 'var(--text)';
            }
        }
        
        // contar palabras y calcular tiempo de lectura
        function updateStats() {
            const content = document.getElementById('contenido').value;
            const words = content.trim().split(/\s+/).filter(word => word.length > 0);
            const wordCount = words.length;
            const charCount = content.length;
            const readingTime = Math.ceil(wordCount / 200); // 200 palabras por minuto
            
            document.getElementById('wordCount').textContent = wordCount;
            document.getElementById('readingTime').textContent = readingTime;
            document.getElementById('charCount').textContent = charCount;
        }
        
        // event listeners
        document.getElementById('titulo').addEventListener('input', function() {
            updateCounter(this, document.getElementById('tituloCounter'), 200);
        });
        
        document.getElementById('subtitulo').addEventListener('input', function() {
            updateCounter(this, document.getElementById('subtituloCounter'), 300);
        });
        
        document.getElementById('contenido').addEventListener('input', updateStats);

        
        // validación del formulario
        document.getElementById('blogForm').addEventListener('submit', function(e) {
            const titulo = document.getElementById('titulo').value.trim();
            const contenido = document.getElementById('contenido').value.trim();
            
            if (titulo.length < 5) {
                e.preventDefault();
                alert('El título debe tener al menos 5 caracteres');
                return;
            }
            
            if (contenido.length < 50) {
                e.preventDefault();
                alert('El contenido debe tener al menos 50 caracteres');
                return;
            }
        });
        
        // auto guardar borrador (usando sessionStorage en vez de localStorage)
        function saveDraft() {
            const draft = {
                titulo: document.getElementById('titulo').value,
                subtitulo: document.getElementById('subtitulo').value,
                contenido: document.getElementById('contenido').value,
                tag: document.getElementById('tag').value
            };
            sessionStorage.setItem('blogDraft', JSON.stringify(draft));
        }
        
        function loadDraft() {
            const draft = sessionStorage.getItem('blogDraft');
            if (draft) {
                const data = JSON.parse(draft);
                document.getElementById('titulo').value = data.titulo || '';
                document.getElementById('subtitulo').value = data.subtitulo || '';
                document.getElementById('contenido').value = data.contenido || '';
                document.getElementById('tag').value = data.tag || '<?php echo htmlEscape($genero_fav); ?>';
                
                updateCounter(document.getElementById('titulo'), document.getElementById('tituloCounter'), 200);
                updateCounter(document.getElementById('subtitulo'), document.getElementById('subtituloCounter'), 300);
                updateStats();
            }
        }
        
        // cargar borrador cuando carga la página
        document.addEventListener('DOMContentLoaded', loadDraft);
        
        // guardar borrador cada 30 segundos
        setInterval(saveDraft, 30000);
        
        // limpiar borrador cuando se manda exitosamente
        document.getElementById('blogForm').addEventListener('submit', function() {
            sessionStorage.removeItem('blogDraft');
        });
    </script>
</body>
</html>