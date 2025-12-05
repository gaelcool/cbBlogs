<?php
require_once 'lib/common.php';
session_start();

// Ensure only admins (Level 3) can access the main dashboard
requiereAdmin(3);

$users = fetchAllusuarios();
$posts = fetchAllPosts();
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - CbBlogs</title>
    <link rel="stylesheet" href="css/read.css"> <!-- reutilizando read.css para el glassmorphism -->
    <style>
        body {
            background: url("img/hqliminal.jpg") no-repeat center center fixed;
            background-size: cover;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .admin-section {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--glass-shadow);
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .admin-title {
            color: var(--text);
            font-size: 2rem;
            margin: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        th {
            background: rgba(76, 184, 196, 0.2);
            color: var(--text);
            font-weight: 600;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .btn-delete {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .btn-delete:hover {
            background: #ff5252;
            transform: translateY(-1px);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .alert-error {
            background: rgba(248, 215, 218, 0.9);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-success {
            background: rgba(212, 237, 218, 0.9);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .dashboard-nav {
            margin-bottom: 2rem;
        }
        
        .dashboard-nav a {
            text-decoration: none;
            color: var(--primary);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .dashboard-nav a:hover {
            color: var(--accent);
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class='logo'>
            <h2> CbBlogs Admin</h2>
        </div>
        <div class="nav-links">
            <a href="LP.php">Inicio</a>
            <a href="Read.php">Leer Blogs</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
        <div class="user-display">
            <span class="user-greeting">Admin:</span>
            <span class="user-name"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
        </div>
    </nav>

    <div class="admin-container">
        <div class="dashboard-nav">
            <a href="LP.php">← Volver al sitio principal</a>
        </div>
        
        <div class="admin-header">
            <h1 class="admin-title">Panel de Administración</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlEscape($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo htmlEscape($success); ?>
            </div>
        <?php endif; ?>
        
        <!-- gestión de usuarios -->
        <div class="admin-section">
            <h2>Gestión de Usuarios</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Género Fav.</th>
                            <th>Contraseña</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlEscape($user['id_usr']); ?></td>
                                <td><?php echo htmlEscape($user['usuario']); ?></td>
                                <td><?php echo htmlEscape($user['nombre']); ?></td>
                                <td><?php echo htmlEscape($user['email']); ?></td>
                                <td><?php echo htmlEscape($user['genero_lit_fav']); ?></td>
                                <td>********</td> <!-- contraseña oculta -->
                                <td>
                                    <?php if ($user['id_usr'] > 3): // proteger a los admins ?>
                                        <form action="admin_actions.php" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id_usr" value="<?php echo htmlEscape($user['id_usr']); ?>">
                                            <button type="submit" class="btn-delete">Eliminar</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.9rem;">Protegido</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- gestión de blogs -->
        <div class="admin-section">
            <h2>Gestión de Blogs</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Fecha</th>
                            <th>Tag</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo htmlEscape($post['title']); ?></td>
                                <td><?php echo htmlEscape($post['author_name']); ?></td>
                                <td><?php echo TraduceSQLfecha($post['created_at']); ?></td>
                                <td><?php echo htmlEscape($post['tag']); ?></td>
                                <td>
                                    <form action="admin_actions.php" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este blog?');">
                                        <input type="hidden" name="action" value="delete_post">
                                        <!-- asumiendo que tenemos ID, si no pues tendríamos que usar title/date pero vamos a intentar con ID primero como se actualizó en common.php -->
                                        <input type="hidden" name="post_id" value="<?php echo htmlEscape($post['id']); ?>">
                                        <button type="submit" class="btn-delete">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
