<?php
require_once 'lib/common.php';
session_start();
requiereLogin();
//SUper unfinished
// Get post ID from URL parameter 

$post_id = $_GET['id'] ?? null;

if (!$post_id || !is_numeric($post_id)) {
    $error = 'Invalid blog post ID';
    $post = null;
} else {
    $pdo = getPDO();
    $post = getPostById($pdo, $post_id);
    
    if (!$post) {
        $error = 'Blog post not found';
    } else {
        // Handle Comment Submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_text'])) {
            $commentText = trim($_POST['comment_text']);
            
            if (!empty($commentText)) {
                try {
                    // 1. Insert Comment
                    $stmt = $pdo->prepare("
                        INSERT INTO comment (post_id, user_id_C, grade, text)
                        VALUES (:pid, :uid, :grade, :text)
                    ");
                    $stmt->execute([
                        ':pid' => $post_id,
                        ':uid' => $_SESSION['usuario'], // user_id_C stores username based on schema
                        ':grade' => $_SESSION['grade'],
                        ':text' => $commentText
                    ]);
                    
                    // 2. Points System Logic
                    if (!isset($_SESSION['session_points'])) {
                        $_SESSION['session_points'] = 0;
                    }
                    
                    if ($_SESSION['session_points'] < 3) {
                        // Award 1 point
                        $stmt = $pdo->prepare("UPDATE user SET user_contributions = user_contributions + 1 WHERE id_usr = :uid");
                        $stmt->execute([':uid' => $_SESSION['id_usr']]);
                        
                        $_SESSION['session_points']++;
                    }
                    
                    // Redirect to avoid resubmission
                    header("Location: blog_post.php?id=$post_id&status=comment_added");
                    exit;
                } catch (Exception $e) {
                    $error = 'Error posting comment: ' . $e->getMessage();
                }
            }
        }
        

        $postImg = $pdo->prepare("SELECT file_path FROM post WHERE id = :pid");
        $postImg->execute([':pid' => $post_id]);
        $postImg = $postImg->fetch(PDO::FETCH_ASSOC);

        // Fetch Comments
        $stmt = $pdo->prepare("
            SELECT c.*, u.nombre as commenter_name 
            FROM comment c
            LEFT JOIN user u ON c.user_id_C = u.usuario
            WHERE c.post_id = :pid
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([':pid' => $post_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? htmlEscape($post['title']) . ' - CbNoticias' : 'Blog Not Found - CbNoticias'; ?></title>
    <link rel="stylesheet" href="css/read.css">
    <link rel="stylesheet" href="css/read.css">
    
    <?php
    // Fetch author's custom style
    $authorStyle = null;
    if ($post) {
        $authorUsername = $post['author_name'];
        $user = getUserByUsername($pdo, $authorUsername);
        if ($user) {
            $stmt = $pdo->prepare("SELECT * FROM user_blog_style WHERE user_id = :uid");
            $stmt->execute([':uid' => $user['id_usr']]);
            $authorStyle = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    // Set CSS Variables based on style or defaults
    $cssVars = [
        'fontFamily' => $authorStyle['font_family'] ?? 'Segoe UI, Arial, sans-serif',
        'titleSize' => $authorStyle['title_size'] ?? '2.5rem',
        'bodySize' => $authorStyle['body_size'] ?? '1.1rem',
        'textDecoration' => $authorStyle['text_decoration'] ?? 'none',
        'backgroundImage' => $authorStyle['background_image'] ?? null,
        'template' => $authorStyle['template_name'] ?? 'frutiger_aero'
    ];
    ?>

    <style>
        /* Base Post Container */
        .post-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Dynamic Custom Styles */
        .post-content {
            font-family: <?php echo htmlEscape($cssVars['fontFamily']); ?>;
            font-size: <?php echo htmlEscape($cssVars['bodySize']); ?>;
            text-decoration: <?php echo htmlEscape($cssVars['textDecoration']); ?>;
        }
        
        .post-title {
            font-size: <?php echo htmlEscape($cssVars['titleSize']); ?>;
        }

        /* Background Image Logic */
        <?php if ($cssVars['backgroundImage']): ?>
        body {
            background: url('img/user_backgrounds/<?php echo htmlEscape($cssVars['backgroundImage']); ?>') no-repeat center center fixed !important;
            background-size: cover !important;
        }
        <?php elseif ($cssVars['template'] === 'pink_classic'): ?>
        body {
            background: linear-gradient(135deg, #FFE5F0 0%, #FFB6D9 100%) fixed !important;
        }
        <?php endif; ?>

        /* Template Specific Styles */
        <?php if ($cssVars['template'] === 'pink_classic'): ?>
        .post-card {
            background: #FFD6E8;
            border: 2px solid #FFB6D9;
            border-radius: 12px;
            box-shadow: 5px 5px 0px rgba(255, 107, 157, 0.2);
            padding: 2rem;
            backdrop-filter: none;
        }
        .post-title, .post-author { color: #D63384; }
        .post-tag { background: #FF6B9D; border-radius: 4px; }
        <?php else: ?>
        /* Frutiger Aero (Default) */
        .post-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: var(--glass-shadow);
        }
        <?php endif; ?>
        
        /* Navigation and other static styles */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 2rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .back-link:hover {
            background: rgba(76, 184, 196, 0.1);
            transform: translateX(-5px);
        }
        
        .post-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid rgba(0,0,0,0.1);
        }
        
        .post-title {
            font-weight: bold;
            color: var(--text);
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }
        
        .post-subtitle {
            font-size: 1.5rem;
            color: var(--text);
            opacity: 0.8;
            margin-bottom: 1rem;
        }
        
        .post-meta {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .post-author {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .post-date {
            color: var(--text);
            opacity: 0.7;
        }
        
        .post-tag {
            background: var(--accent);
            color: white;
            padding: 6px 16px;
            border-radius: 12px;
            font-size: 0.9rem;
        }
        
        .post-content {
            line-height: 1.8;
            color: var(--text);
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .post-content p {
            margin-bottom: 1.5rem;
        }
        
        .error-message {
            text-align: center;
            padding: 3rem;
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: var(--glass-shadow);
        }
        
        .error-message h2 {
            color: var(--accent);
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .error-message p {
            color: var(--text);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2>CbNoticias</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="Write.php">Escribir</a>
            <a href="Account-info.php">Mi Cuenta</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="user-display">
            <span class="user-greeting">Hola,</span>
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>

    <div class="post-container">
       
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <h2>🔍 <?php echo htmlEscape($error); ?></h2>
                <p>The blog post you're looking for doesn't exist or has been removed.</p>
                <a href="Read.php" class="btn">Return to Blog List</a>
            </div>
        <?php else: ?>
            <article class="post-card">
                <header class="post-header">
                    <h1 class="post-title"><?php echo htmlEscape($post['title']); ?></h1>
                    <?php if (!empty($post['subtitle'])): ?>
                        <p class="post-subtitle"><?php echo htmlEscape($post['subtitle']); ?></p>
                    <?php endif; ?>
                    
                    <div class="post-meta">
                        <span class="post-author">👤 <?php echo htmlEscape($post['author_name']); ?></span>
                        <span class="post-date">📅 <?php echo TraduceSQLfecha($post['created_at']); ?></span>
                        <?php if (!empty($post['tag'])): ?>
                            <span class="post-tag"><?php echo htmlEscape($post['tag']); ?></span>
                        <?php endif; ?>
                    </div>
                </header>
                <div class="blog-image-container" style="display: flex; justify-content: center; margin: 1rem 0;">
                       <?php if (!empty($postImg['file_path'])): ?>
                                <img src="img/blog_media/<?php echo htmlEscape($postImg['file_path']); ?>" 
                                     alt="Blog Image" 
                                     style="width: 700px; height: 500px; object-fit: cover; border-radius: 8px;">
                            <?php endif; ?>
                            </div>
           
         
                
                <div class="post-content">
                    <?php echo convertnewlines($post['content']); ?>
                </div>
            </article>

            <!-- Comments Section -->
            <div class="comments-section" style="margin-top: 3rem;">
                <h2 style="color: var(--text); margin-bottom: 1.5rem; font-size: 1.8rem;">
                    Comentarios (<?php echo count($comments); ?>)
                </h2>

                <!-- Comment Form -->
                <div class="comment-form-card" style="background: var(--glass-bg); backdrop-filter: blur(16px); border: 1px solid var(--glass-border); border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem;">
                    <form method="post">
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text);">Deja un comentario</label>
                            <textarea name="comment_text" rows="3" style="width: 100%; padding: 1rem; border-radius: 8px; border: 1px solid rgba(0,0,0,0.1); background: rgba(255,255,255,0.5); font-family: inherit;" placeholder="Comparte tu opinión..." required></textarea>
                        </div>
                        <button type="submit" class="btn" style="background: var(--accent); color: white; border: none; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; font-weight: 600;">Publicar Comentario</button>
                        <span style="font-size: 0.8rem; margin-left: 1rem; opacity: 0.7;">(Ganarás 1 punto, máx 3 por sesión)</span>
                    </form>
                </div>
                <a href="Read.php" class="back-link">← Back to Blogs</a>
                <!-- Comments List -->
                <div class="comments-list">
                    <?php if (empty($comments)): ?>
                        <p style="text-align: center; opacity: 0.7; padding: 2rem;">Sé el primero en comentar.</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-card" style="background: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem;">
                                <div class="comment-header" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <div>
                                        <span style="font-weight: bold; color: var(--primary);"><?php echo htmlEscape($comment['user_id_C']); ?></span>
                                        <span style="font-size: 0.8rem; background: rgba(0,0,0,0.05); padding: 2px 6px; border-radius: 4px; margin-left: 0.5rem;">Grado <?php echo $comment['grade']; ?></span>
                                    </div>
                                    <span style="font-size: 0.8rem; opacity: 0.6;"><?php echo TraduceSQLfecha($comment['created_at']); ?></span>
                                </div>
                                <div class="comment-body" style="line-height: 1.5;">
                                    <?php echo nl2br(htmlEscape($comment['text'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
