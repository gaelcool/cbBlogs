<?php
require_once 'lib/common.php';

function installDatabase()
{
    $pdo = getPDO();
    $root = getRootPath();
    $database = getDatabasePath();

    $error = '';
    $count = 0;

    // Read the SQL file
    $sqlFile = $root . '/data/init.sql';
    if (!file_exists($sqlFile)) {
        return array(0, "Error: No se encontró el archivo SQL en $sqlFile");
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
                // Ignore "table already exists" errors if we want to be idempotent, 
                // but init.sql has DROP TABLE IF EXISTS, so we should report real errors.
                // However, let's just capture the error.
                $error = 'No se pudo ejecutar SQL: ' . print_r($errorInfo, true);
                break; // Stop on first error
            }
        } catch (Exception $e) {
            $error = 'Error al ejecutar SQL: ' . $e->getMessage();
            break;
        }
    }
    
    // Count created users if no error
    if (!$error) {
        try {
            $sql = "SELECT COUNT(*) AS c FROM user";
            $stmt = $pdo->query($sql);
            if ($stmt) {
                $count = $stmt->fetchColumn();
            }
        } catch (Exception $e) {
            $error = 'Error al contar usuarios: ' . $e->getMessage();
        }
    }
    
    return array($count, $error);
}

session_start();

// Process installation when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    list($_SESSION['count'], $_SESSION['error']) = installDatabase();
    
    // Redirect to same page to prevent resubmission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Check if we just completed an installation
$attempted = false;
$count = 0;
$error = '';

if (isset($_SESSION['count']) || isset($_SESSION['error']))
{
    $attempted = true;
    $count = $_SESSION['count'] ?? 0;
    $error = $_SESSION['error'] ?? '';
    
    // Clear session variables after displaying
    unset($_SESSION['count']);
    unset($_SESSION['error']);
}

?>
<!DOCTYPE html>
<html lang="en"> 
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
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
            color: white;
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
                <p>Se crearon <strong><?php echo $count ?></strong> usuarios de prueba.</p>
                <p>
                    Usuarios disponibles:<br>
                    • <code>Mechy</code> / password: <code>password</code><br>
                    • <code>Jimmy</code> / password: <code>password123</code>
                </p>
                <p>
                    <a href="registrar.php">Ir al registro</a> | 
                    <a href="index.html">Ir al inicio</a> |
                    <a href="login.php">Iniciar sesión</a>
                </p>
            </div>
        <?php endif ?>
    <?php else: ?>
        <div class="box" style="background: white; border: 2px solid #007bff;">
            <p>Este instalador creará la base de datos SQLite con usuarios, comentarios y blogs de prueba.</p>
            <p><strong>|⚠️ Esto es solo para desarrolladores.⚠️|</strong></p>
            <form method="post">
                <button type="submit" name="install">Instalar Base de Datos</button>
            </form>
        </div>
    <?php endif ?>
</body>
</html>