<?php
require_once 'lib/common.php';
session_start();

// Verificar que el usuario esté logueado
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Recibir datos del formulario
$titulo = trim($_POST["titulo"] ?? '');
$subtitulo = trim($_POST["subtitulo"] ?? '');
$contenido = trim($_POST["contenido"] ?? '');
$tag = $_POST["tag"] ?? 'General';
$autor = $_SESSION['usuario']; // usar nombre de usuario como autor

// Validaciones básicas
if (empty($titulo) || empty($contenido)) {
    echo '<script>
    alert("El título y contenido son obligatorios");
    window.history.go(-1);
    </script>';
    exit;
}

if (strlen($titulo) < 5) {
    echo '<script>
    alert("El título debe tener al menos 5 caracteres");
    window.history.go(-1);
    </script>';
    exit;
}

if (strlen($contenido) < 50) {
    echo '<script>
    alert("El contenido debe tener al menos 50 caracteres");
    window.history.go(-1);
    </script>';
    exit;
}

// Calcular estadísticas
$palabras = str_word_count($contenido);
$tiempo_lectura = ceil($palabras / 200); // 200 palabras por minuto

// Handle file upload
$filePath = null;
if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $filename = $_FILES['media']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        $targetDir = 'data/blog_media/';
        $newFilename = uniqid() . '_' . basename($filename);
        $targetPath = $targetDir . $newFilename;
        
        if (move_uploaded_file($_FILES['media']['tmp_name'], $targetPath)) {
            $filePath = $targetPath;
        }
    }
}

try {
    $pdo = getPDO();
    
    // Insertar blog en la base de datos
    $stmt = $pdo->prepare("
        INSERT INTO post (title, subtitle, author_name, content, tag, file_path, created_at) 
        VALUES (:titulo, :subtitulo, :autor, :contenido, :tag, :file_path, CURRENT_TIMESTAMP)
    ");
    
    $result = $stmt->execute([
        ':titulo' => $titulo,
        ':subtitulo' => $subtitulo,
        ':autor' => $autor,
        ':contenido' => $contenido,
        ':tag' => $tag,
        ':file_path' => $filePath
    ]);

    if ($result) {
        // Award points for publishing a blog
        $userId = $_SESSION['id_usr'];
        $updatePoints = $pdo->prepare("UPDATE user SET user_contributions = user_contributions + 10 WHERE id_usr = :uid");
        $updatePoints->execute([':uid' => $userId]);

        // Log contribution
        $lastId = $pdo->lastInsertId();
        $logContrib = $pdo->prepare("INSERT INTO user_contributions (user_id, contribution_type, contribution_id, contribution_date) VALUES (:uid, 'blog', :bid, CURRENT_TIMESTAMP)");
        $logContrib->execute([':uid' => $userId, ':bid' => $lastId]);

        header("Location: Read.php?status=success");
        exit;
    } else {
        header("Location: Write.php?status=error&message=Error al publicar el blog");
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Error saving blog: " . $e->getMessage());
    echo '<script>
    alert("Error al publicar el blog: ' . htmlspecialchars($e->getMessage()) . '");
    window.history.go(-1);
    </script>';
}
?>