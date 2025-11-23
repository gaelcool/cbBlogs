<?php
require_once 'lib/common.php';

echo "Starting installation...\n";

try {
    $pdo = getPDO();
    
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
    
    echo "Successfully installed custom CSS feature table!\n";
    
    // Verify it exists
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='user_blog_style'");
    if ($stmt->fetch()) {
        echo "Verification: Table exists.\n";
    } else {
        echo "Verification: Table NOT found after creation attempt.\n";
    }
    
} catch (Exception $e) {
    echo "Error installing feature: " . $e->getMessage() . "\n";
}
?>
