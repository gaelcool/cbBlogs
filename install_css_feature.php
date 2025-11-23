<?php
require_once 'lib/common.php';
session_start();

// Only admins should probably run this, but for now we'll leave it open or check login
requiereLogin();

$pdo = getPDO();

try {
    // Create user_blog_style table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_blog_style (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL UNIQUE,
            template_name TEXT DEFAULT 'frutiger_aero',
            background_image TEXT,
            font_family TEXT DEFAULT 'Segoe UI, Arial, sans-serif',
            title_size TEXT DEFAULT '2.5rem',
            body_size TEXT DEFAULT '1.1rem',
            text_decoration TEXT DEFAULT 'none',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id_usr) ON DELETE CASCADE
        )
    ");
    
    $message = "Successfully installed custom CSS feature table!";
    $status = "success";
    
} catch (Exception $e) {
    $message = "Error installing feature: " . $e->getMessage();
    $status = "error";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installing CSS Feature</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .card {
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Installation Status</h2>
        <p class="<?php echo $status; ?>"><?php echo htmlEscape($message); ?></p>
        
        <?php if ($status === 'success'): ?>
            <p>Redirecting to CSS Editor...</p>
            <script>
                setTimeout(function() {
                    window.location.href = 'edit_blog_style.php';
                }, 2000);
            </script>
            <a href="edit_blog_style.php" class="btn btn-primary">Go to Editor Now</a>
        <?php else: ?>
            <a href="LP.php" class="btn btn-secondary">Return Home</a>
        <?php endif; ?>
    </div>
</body>
</html>
