<?php
require_once 'lib/common.php';
session_start();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$pdo = getPDO();

// Fallback: If id_usr is missing from session but user is logged in, fetch it
if (!isset($_SESSION['id_usr']) && isset($_SESSION['usuario'])) {
    $stmtUser = $pdo->prepare("SELECT id_usr FROM user WHERE usuario = :usuario");
    $stmtUser->execute([':usuario' => $_SESSION['usuario']]);
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if ($userRow) {
        $_SESSION['id_usr'] = $userRow['id_usr'];
    } else {
        echo json_encode(['success' => false, 'message' => 'Session error: User ID not found']);
        exit;
    }
}

$user_id = $_SESSION['id_usr'];

// 1. Validate Inputs
$template = $_POST['template_name'] ?? 'frutiger_aero';
$font = $_POST['font_family'] ?? 'Segoe UI, Arial, sans-serif';
$title_size = $_POST['title_size'] ?? '2.5rem';
$body_size = $_POST['body_size'] ?? '1.1rem';
$decoration = $_POST['text_decoration'] ?? 'none';

// Whitelist templates
if (!in_array($template, ['frutiger_aero', 'pink_classic'])) {
    $template = 'frutiger_aero';
}

// Validate Font (Alphanumeric, spaces, commas, hyphens)
if (!preg_match('/^[a-zA-Z0-9\s,\-]+$/', $font)) {
    echo json_encode(['success' => false, 'message' => 'Invalid font family format']);
    exit;
}

// Validate Sizes (Number + unit)
if (!preg_match('/^\d+(\.\d+)?(px|rem|em|%)$/', $title_size)) {
    $title_size = '2.5rem';
}
if (!preg_match('/^\d+(\.\d+)?(px|rem|em|%)$/', $body_size)) {
    $body_size = '1.1rem';
}

// Validate Decoration
if (!in_array($decoration, ['none', 'underline', 'overline', 'line-through'])) {
    $decoration = 'none';
}

// 2. Handle Image Upload
$bg_filename = null;

// Check if user already has a background
$stmt = $pdo->prepare("SELECT background_image FROM user_blog_style WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);
if ($current) {
    $bg_filename = $current['background_image'];
}

if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['background_image'];
    
    // Validate size (2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large (Max 2MB)']);
        exit;
    }
    
    // Validate type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, WEBP allowed.']);
        exit;
    }
    
    // Determine extension
    $ext = '';
    switch ($mime) {
        case 'image/jpeg': $ext = 'jpg'; break;
        case 'image/png': $ext = 'png'; break;
        case 'image/webp': $ext = 'webp'; break;
    }
    
    // Create directory if not exists
    $uploadDir = getRootPath() . '/img/user_backgrounds/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate filename: user_bg_{id}.{ext}
    // Remove old file if exists and extension is different
    if ($bg_filename && file_exists($uploadDir . $bg_filename)) {
        unlink($uploadDir . $bg_filename);
    }
    
    $newFilename = 'user_bg_' . $user_id . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFilename)) {
        $bg_filename = $newFilename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded image']);
        exit;
    }
}

// 3. Save to Database
try {
    $stmt = $pdo->prepare("
        INSERT INTO user_blog_style 
        (user_id, template_name, background_image, font_family, title_size, body_size, text_decoration, updated_at)
        VALUES (:uid, :tpl, :bg, :font, :title, :body, :deco, CURRENT_TIMESTAMP)
        ON CONFLICT(user_id) DO UPDATE SET
            template_name = :tpl,
            background_image = :bg,
            font_family = :font,
            title_size = :title,
            body_size = :body,
            text_decoration = :deco,
            updated_at = CURRENT_TIMESTAMP
    ");
    
    $stmt->execute([
        ':uid' => $user_id,
        ':tpl' => $template,
        ':bg' => $bg_filename,
        ':font' => $font,
        ':title' => $title_size,
        ':body' => $body_size,
        ':deco' => $decoration
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Style saved successfully!',
        'background' => $bg_filename
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
