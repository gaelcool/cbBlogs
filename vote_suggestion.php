<?php
require_once 'lib/common.php';
session_start();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para votar.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$suggestionId = filter_input(INPUT_POST, 'suggestion_id', FILTER_VALIDATE_INT);
$userId = $_SESSION['id_usr'];

if (!$suggestionId) {
    echo json_encode(['success' => false, 'message' => 'ID de sugerencia inválido.']);
    exit;
}

try {
    $pdo = getPDO();
    
    // 1. Check if already voted
    $stmt = $pdo->prepare("SELECT id FROM suggestion_supporters WHERE suggestion_id = :sid AND user_id = :uid");
    $stmt->execute([':sid' => $suggestionId, ':uid' => $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya has votado por esta sugerencia.']);
        exit;
    }

    // 2. Insert vote
    $stmt = $pdo->prepare("INSERT INTO suggestion_supporters (suggestion_id, user_id) VALUES (:sid, :uid)");
    $stmt->execute([':sid' => $suggestionId, ':uid' => $userId]);

    // 3. Update support count in suggestions table (optional, but good for performance if we had a column, 
    // but schema says we calculate it. Wait, schema has support_count in suggestions table too? 
    // Let's check init.sql. Yes: support_count INTEGER DEFAULT 0)
    // Let's update that column for easier querying.
    $stmt = $pdo->prepare("UPDATE suggestions SET support_count = support_count + 1 WHERE id = :sid");
    $stmt->execute([':sid' => $suggestionId]);

    // 4. Check total support count to award points
    $stmt = $pdo->prepare("SELECT support_count, author_id FROM suggestions WHERE id = :sid");
    $stmt->execute([':sid' => $suggestionId]);
    $suggestion = $stmt->fetch(PDO::FETCH_ASSOC);

    $newCount = $suggestion['support_count'];
    $authorId = $suggestion['author_id'];
    $pointsAwarded = false;

    // Simplification: Award points only when count hits exactly 6
    if ($newCount == 6) {
        $stmt = $pdo->prepare("UPDATE user SET user_contributions = user_contributions + 10 WHERE id_usr = :uid");
        $stmt->execute([':uid' => $authorId]);
        
        // Log contribution (optional but good practice based on schema)
        $stmt = $pdo->prepare("INSERT INTO user_contributions (user_id, contribution_type, contribution_id, contribution_date) VALUES (:uid, 'suggestion_bonus', :sid, CURRENT_TIMESTAMP)");
        $stmt->execute([':uid' => $authorId, ':sid' => $suggestionId]);
        
        $pointsAwarded = true;
    }

    echo json_encode([
        'success' => true, 
        'message' => 'Voto registrado correctamente.',
        'new_count' => $newCount,
        'points_awarded' => $pointsAwarded
    ]);

} catch (PDOException $e) {
    error_log("Error voting: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
}
?>
