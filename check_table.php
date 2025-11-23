<?php
require_once 'lib/common.php';
$pdo = getPDO();
try {
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='user_blog_style'");
    if ($stmt->fetch()) {
        echo "Table exists";
    } else {
        echo "Table does not exist";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
