<?php
require_once 'lib/common.php';

function installDatabase()
{
    $pdo = getPDO();
    $root = getRootPath();
    $database = getDatabasePath();

    $error = '';
    $counts = ['users' => 0, 'posts' => 0, 'comments' => 0];

    // Read the SQL file
    $sqlFile = $root . '/data/init.sql';
    if (!file_exists($sqlFile)) {
        return array($counts, "Error: No se encontró el archivo SQL en $sqlFile");
    }

    $sqlContent = file_get_contents($sqlFile);
    
    // Remove comments to avoid issues with splitting
    $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
    
    // Split into individual statements
    $queries = explode(';', $sqlContent);

    foreach ($queries as $sql) {
        $sql = trim($sql);
        if (empty($sql)) {
            continue;
        }

        try {
            $result = $pdo->exec($sql);
            if ($result === false) {
                $errorInfo = $pdo->errorInfo();
                $error = 'No se pudo ejecutar SQL: ' . print_r($errorInfo, true);
                break;
            }
        } catch (Exception $e) {
            $error = 'Error al ejecutar SQL: ' . $e->getMessage();
            break;
        }
    }
    
    // Count created data if no error
    if (!$error) {
        try {
            // Count users
            $stmt = $pdo->query("SELECT COUNT(*) FROM user");
            if ($stmt) {
                $counts['users'] = $stmt->fetchColumn();
            }
            
            // Count posts
            $stmt = $pdo->query("SELECT COUNT(*) FROM post");
            if ($stmt) {
                $counts['posts'] = $stmt->fetchColumn();
            }
            
            // Count comments
            $stmt = $pdo->query("SELECT COUNT(*) FROM comment");
            if ($stmt) {
                $counts['comments'] = $stmt->fetchColumn();
            }
        } catch (Exception $e) {
            $error = 'Error al contar datos: ' . $e->getMessage();
        }
    }
    
    return array($counts, $error);
}

session_start();

// Process installation when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    list($_SESSION['counts'], $_SESSION['error']) = installDatabase();
    
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Check if we just completed an installation
$attempted = false;
$counts = ['users' => 0, 'posts' => 0, 'comments' => 0];
$error = '';

if (isset($_SESSION['counts']) || isset($_SESSION['error']))
{
    $attempted = true;
    $counts = $_SESSION['counts'] ?? ['users' => 0, 'posts' => 0, 'comments' => 0];
    $error = $_SESSION['error'] ?? '';
    
    unset($_SESSION['counts']);
    unset($_SESSION['error']);
}

?>
<!DOCTYPE html>
<html lang="es"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - CbNoticias</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f0f8ff;
        }
        .box {
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1.5rem 0;
        }
        .stat-box {
            background: rgba(255, 255, 255, 0.5);
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        .stat-label {
            font-size: 0.9rem;
            color: #155724;
            margin-top: 0.5rem;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>📰 Instalador de Base de Datos - CbNoticias</h1>
    
    <?php if ($attempted): ?>
        <?php if ($error): ?>
            <div class="error box">
                <strong>Error:</strong> <?php echo htmlEscape($error) ?>
            </div>
        <?php else: ?>
            <div class="success box">
                <strong>¡Éxito!</strong> La base de datos fue creada correctamente.
                
                <div class="stats">
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $counts['users']; ?></div>
                        <div class="stat-label">Usuarios</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $counts['posts']; ?></div>
                        <div class="stat-label">Blogs</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-number"><?php echo $counts['comments']; ?></div>
                        <div class="stat-label">Comentarios</div>
                    </div>
                </div>
                
                <p>
                    <strong>Usuarios de prueba disponibles:</strong><br>
                    • <code>Admin</code> / contraseña: <code>admin123</code> (Admin Nivel 3)<br>
                    • <code>TestUser</code> / contraseña: <code>test123</code><br>
                    • <code>generico</code> / contraseña: <code>clave</code>
                </p>
                <p style="margin-top: 1.5rem;">
                    <a href="registrar.php"> Ir al registro</a> | 
                    <a href="index.php"> Ir al inicio</a> |
                    <a href="login.php"> Iniciar sesión</a>
                </p>
            </div>
        <?php endif ?>
    <?php else: ?>
        <div class="box" style="background: white; border: 2px solid #007bff;">
            <p>Este instalador creará la base de datos SQLite con usuarios, blogs y comentarios de prueba.</p>
            <p><strong>⚠️ ADVERTENCIA: Esto eliminará todos los datos existentes y creará tablas nuevas.</strong></p>
            <p>Este proceso es solo para desarrollo/testing.</p>
            <form method="post">
                <button type="submit" name="install">🚀 Instalar Base de Datos</button>
            </form>
        </div>
    <?php endif ?>
</body>
</html>
