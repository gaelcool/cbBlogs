<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

// Get current user's style preferences
$pdo = getPDO();

// Fallback: If id_usr is missing from session but user is logged in, fetch it
if (!isset($_SESSION['id_usr']) && isset($_SESSION['usuario'])) {
    $stmtUser = $pdo->prepare("SELECT id_usr FROM user WHERE usuario = :usuario");
    $stmtUser->execute([':usuario' => $_SESSION['usuario']]);
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if ($userRow) {
        $_SESSION['id_usr'] = $userRow['id_usr'];
    } else {
        // Should not happen if logged in, but safety first
        header('Location: logout.php');
        exit();
    }
}

$user_id = $_SESSION['id_usr'] ?? 0; // Default to 0 or handle error if still missing
$stmt = $pdo->prepare("SELECT * FROM user_blog_style WHERE user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$currentStyle = $stmt->fetch(PDO::FETCH_ASSOC);

// Set defaults if no style exists
if (!$currentStyle) {
    $currentStyle = [
        'template_name' => 'frutiger_aero',
        'background_image' => '',
        'font_family' => 'Segoe UI, Arial, sans-serif',
        'title_size' => '2.5rem',
        'body_size' => '1.1rem',
        'text_decoration' => 'none'
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Blog Style - CbNoticias</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/editor.css">
    <!-- Preload template styles for preview -->
    <link rel="stylesheet" href="css/templates/frutiger_aero.css" id="template-style">
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2>CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="Read.php">← Back to Blogs</a>
            <span class="nav-title">🎨 Style Editor</span>
        </div>
        <div class="user-display">
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>

    <div class="editor-container">
        <!-- Left Panel: Controls -->
        <div class="editor-controls glass-container">
            <h3>Customize Your Style</h3>
            <p class="text-muted">These styles will apply to ALL your blog posts.</p>
            
            <form id="styleForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="templateSelect">Choose Template</label>
                    <select id="templateSelect" name="template_name" class="form-control">
                        <option value="frutiger_aero" <?php echo $currentStyle['template_name'] === 'frutiger_aero' ? 'selected' : ''; ?>>Frutiger Aero (Glassmorphism)</option>
                        <option value="pink_classic" <?php echo $currentStyle['template_name'] === 'pink_classic' ? 'selected' : ''; ?>>Pink Classic (Solid)</option>
                    </select>
                </div>

                <div class="style-section">
                    <h4>Background</h4>
                    <div class="form-group">
                        <label>Current Background</label>
                        <div id="currentBgDisplay" class="bg-preview-box">
                            <?php if (!empty($currentStyle['background_image'])): ?>
                                <img src="img/user_backgrounds/<?php echo htmlEscape($currentStyle['background_image']); ?>?t=<?php echo time(); ?>" alt="Current Background">
                                <span class="bg-name">Custom Image Uploaded</span>
                            <?php else: ?>
                                <span class="bg-name">Using Template Default</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="bgUpload">Upload New Background (Max 2MB)</label>
                        <input type="file" id="bgUpload" name="background_image" accept=".jpg,.jpeg,.png,.webp">
                        <small>Uploading a new image replaces the current one.</small>
                    </div>
                </div>

                <div class="style-section">
                    <h4>Typography</h4>
                    <div class="form-group">
                        <label for="fontFamily">Font Family</label>
                        <input type="text" id="fontFamily" name="font_family" value="<?php echo htmlEscape($currentStyle['font_family']); ?>" placeholder="e.g. Arial, sans-serif">
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="titleSize">Title Size</label>
                                <input type="text" id="titleSize" name="title_size" value="<?php echo htmlEscape($currentStyle['title_size']); ?>" placeholder="2.5rem">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="bodySize">Body Text Size</label>
                                <input type="text" id="bodySize" name="body_size" value="<?php echo htmlEscape($currentStyle['body_size']); ?>" placeholder="1.1rem">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="textDecoration">Text Decoration</label>
                        <select id="textDecoration" name="text_decoration">
                            <option value="none" <?php echo $currentStyle['text_decoration'] === 'none' ? 'selected' : ''; ?>>None</option>
                            <option value="underline" <?php echo $currentStyle['text_decoration'] === 'underline' ? 'selected' : ''; ?>>Underline</option>
                            <option value="overline" <?php echo $currentStyle['text_decoration'] === 'overline' ? 'selected' : ''; ?>>Overline</option>
                            <option value="line-through" <?php echo $currentStyle['text_decoration'] === 'line-through' ? 'selected' : ''; ?>>Line-through</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="resetBtn" class="btn btn-secondary">Reset to Defaults</button>
                    <button type="submit" id="saveBtn" class="btn btn-primary">Save Changes</button>
                </div>
                <div id="messageArea"></div>
            </form>
        </div>

        <!-- Right Panel: Live Preview -->
        <div class="preview-pane">
            <div class="preview-header">
                <span>Live Preview</span>
            </div>
            <div id="previewFrame" class="preview-content">
                <!-- Mock Blog Post -->
                <div class="post-container">
                    <article class="post-card">
                        <header class="post-header">
                            <h1 class="post-title">Sample Blog Title</h1>
                            <p class="post-subtitle">This is how your subtitle looks</p>
                            
                            <div class="post-meta">
                                <span class="post-author">👤 <?php echo htmlEscape($_SESSION['nombre']); ?></span>
                                <span class="post-date">📅 <?php echo date('d/m/Y'); ?></span>
                                <span class="post-tag">General</span>
                            </div>
                        </header>
                        
                        <div class="post-content">
                            <p>This is a preview of how your blog posts will appear to visitors. The styles you configure on the left are applied here in real-time.</p>
                            <p>You can customize the font, sizes, and background image to make your blog unique!</p>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </div>

    <script src="js/css_editor.js"></script>
</body>
</html>
