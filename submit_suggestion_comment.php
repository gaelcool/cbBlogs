<?php
require_once 'lib/common.php';
session_start();

header('Content-Type: application/json');

// Require admin authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión.']);
    exit;
}

if (!isAdmin(1)) {
    echo json_encode(['success' => false, 'message' => 'Solo los administradores pueden comentar.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$suggestionId = filter_input(INPUT_POST, 'suggestion_id', FILTER_VALIDATE_INT);
$commentText = trim($_POST['comment_text'] ?? '');
$userId = $_SESSION['id_usr'];

if (!$suggestionId) {
    echo json_encode(['success' => false, 'message' => 'ID de sugerencia inválido.']);
    exit;
}

if (empty($commentText)) {
    echo json_encode(['success' => false, 'message' => 'El comentario no puede estar vacío.']);
    exit;
}

try {
    $pdo = getPDO();

    // Verify suggestion exists
    $stmt = $pdo->prepare("SELECT id FROM suggestions WHERE id = :sid");
    $stmt->execute([':sid' => $suggestionId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Sugerencia no encontrada.']);
        exit;
    }

    // Insert comment
    $stmt = $pdo->prepare("
        INSERT INTO suggestion_comments (suggestion_id, user_id, comment_text)
        VALUES (:sid, :uid, :text)
    ");
    $stmt->execute([
        ':sid' => $suggestionId,
        ':uid' => $userId,
        ':text' => $commentText
    ]);

    // Get the newly created comment with user info
    $commentId = $pdo->lastInsertId();
    $stmt = $pdo->prepare("
        SELECT sc.*, u.nombre as commenter_name
        FROM suggestion_comments sc
        JOIN user u ON sc.user_id = u.id_usr
        WHERE sc.id = :cid
    ");
    $stmt->execute([':cid' => $commentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Comentario publicado correctamente.',
        'comment' => [
            'id' => $comment['id'],
            'commenter_name' => $comment['commenter_name'],
            'comment_text' => $comment['comment_text'],
            'created_at' => TraduceSQLfecha($comment['created_at'])
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error posting suggestion comment: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de base de datos.']);
}
?>