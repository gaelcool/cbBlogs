<?php
// Database migration script: Add suggestion_comments table
require_once 'lib/common.php';

try {
    $pdo = getPDO();

    // Create suggestion_comments table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS suggestion_comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            suggestion_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            comment_text TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_deleted BOOLEAN DEFAULT 0,
            FOREIGN KEY (suggestion_id) REFERENCES suggestions(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id_usr) ON DELETE CASCADE
        )
    ");

    // Create index
    $pdo->exec("
        CREATE INDEX IF NOT EXISTS idx_suggestion_comments_suggestion 
        ON suggestion_comments(suggestion_id)
    ");

    echo "✓ Database migration completed successfully!\n";
    echo "✓ Table 'suggestion_comments' created\n";
    echo "✓ Index 'idx_suggestion_comments_suggestion' created\n";

} catch (PDOException $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>