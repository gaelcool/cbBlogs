<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $subject = $_POST['subject'] ?? '';
    $grade = $_POST['grade'] ?? '';
    $type = $_POST['type'] ?? 'link';
    $url = trim($_POST['url'] ?? '');
    
    if (empty($title) || empty($subject) || empty($grade)) {
        $error = 'Por favor completa todos los campos obligatorios.';
    } else {
        $pdo = getPDO();
        $filePath = null;
        
        // Handle file upload
        if ($type === 'file' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ensureResourcesDirectory();
            $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
            $targetPath = $uploadDir . $fileName;
            
            // Validate file type (basic)
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'txt', 'ppt', 'pptx', 'jpg', 'png'];
            
            if (!in_array($ext, $allowed)) {
                $error = 'Tipo de archivo no permitido.';
            } elseif (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $filePath = 'data/study_resources/' . $fileName;
            } else {
                $error = 'Error al subir el archivo.';
            }
        } elseif ($type === 'link' && empty($url)) {
            $error = 'Por favor ingresa el enlace.';
        }
        
        if (!$error) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO study_resources (
                        title, description, subject, grade, resource_type, 
                        text_content, file_path, external_url, uploader_id, is_approved
                    ) VALUES (
                        :title, :desc, :subject, :grade, :type, 
                        NULL, :file, :url, :uid, 1
                    )
                ");
                
                $stmt->execute([
                    ':title' => $title,
                    ':desc' => $description,
                    ':subject' => $subject,
                    ':grade' => $grade,
                    ':type' => $type,
                    ':file' => $filePath,
                    ':url' => $url,
                    ':uid' => $_SESSION['id_usr']
                ]);
                
                // Track contribution
                $resId = $pdo->lastInsertId();
                $stmt = $pdo->prepare("
                    INSERT INTO user_contributions (user_id, contribution_type, contribution_id)
                    VALUES (:uid, 'resource', :rid)
                ");
                $stmt->execute([':uid' => $_SESSION['id_usr'], ':rid' => $resId]);
                
                header('Location: resources.php?status=success');
                exit;
            } catch (Exception $e) {
                $error = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Recurso - CbNoticias</title>
    <link rel="stylesheet" href="css/resources.css">
    <style>
        .upload-form {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: var(--glass-shadow);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text);
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .error-msg {
            color: #dc3545;
            background: #f8d7da;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }
        
        .type-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .type-option {
            flex: 1;
            padding: 10px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .type-option.active {
            border-color: var(--primary);
            background: rgba(76, 184, 196, 0.1);
            color: var(--primary);
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="resources.php">Recursos</a>
            <a href="Account-info.php">Mi Cuenta</a>
        </div>
    </nav>

    <div class="read-container">
        <div class="page-header">
            <h1>Subir Nuevo Recurso</h1>
            <p>Comparte tus conocimientos con la comunidad</p>
        </div>

        <div class="upload-form">
            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlEscape($error); ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">Título del Recurso</label>
                    <input type="text" name="title" class="form-control" required placeholder="Ej: Guía de Álgebra - Parcial 1">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Breve descripción del contenido..."></textarea>
                </div>
                
                <div class="form-group" style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <label class="form-label">Materia</label>
                        <select name="subject" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <option value="Matemáticas">Matemáticas</option>
                            <option value="Ciencias">Ciencias</option>
                            <option value="Historia">Historia</option>
                            <option value="Lengua">Lengua</option>
                            <option value="Inglés">Inglés</option>
                            <option value="Programación">Programación</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label class="form-label">Semestre</label>
                        <select name="grade" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <option value="1">1er Semestre</option>
                            <option value="2">2do Semestre</option>
                            <option value="3">3er Semestre</option>
                            <option value="4">4to Semestre</option>
                            <option value="5">5to Semestre</option>
                            <option value="6">6to Semestre</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Tipo de Recurso</label>
                    <div class="type-selector">
                        <div class="type-option active" onclick="selectType('link')">🔗 Enlace</div>
                        <div class="type-option" onclick="selectType('file')">📁 Archivo</div>
                    </div>
                    <input type="hidden" name="type" id="typeInput" value="link">
                    
                    <div id="linkInput">
                        <input type="url" name="url" class="form-control" placeholder="https://...">
                    </div>
                    
                    <div id="fileInput" style="display: none;">
                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx,.jpg,.png">
                        <small style="color: var(--text-light);">Máx 5MB. PDF, Word, Imágenes.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Publicar Recurso</button>
            </form>
        </div>
    </div>

    <script>
        function selectType(type) {
            document.getElementById('typeInput').value = type;
            document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
            
            if (type === 'link') {
                document.querySelectorAll('.type-option')[0].classList.add('active');
                document.getElementById('linkInput').style.display = 'block';
                document.getElementById('fileInput').style.display = 'none';
            } else {
                document.querySelectorAll('.type-option')[1].classList.add('active');
                document.getElementById('linkInput').style.display = 'none';
                document.getElementById('fileInput').style.display = 'block';
            }
        }
    </script>
</body>
</html>
