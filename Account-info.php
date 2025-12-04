<?php
require_once 'lib/common.php';
session_start();
requiereLogin();

// Calculate days registered con funcion
$dias = 0;
if (isset($_SESSION['fecha_registro'])) {
    $dias = calcularDiasRegistrado($_SESSION['fecha_registro']);
}

// Get total blogs count for this user
$pdo = getPDO();
$stmt = $pdo->prepare('SELECT COUNT(*) FROM post WHERE author_name = :usuario');
$stmt->execute([':usuario' => $_SESSION['usuario']]);
$totalblogs = $stmt->fetchColumn();

// Get user style for display
$stmt = $pdo->prepare("SELECT template_name FROM user_blog_style WHERE user_id = :uid");
$stmt->execute([':uid' => $_SESSION['id_usr']]);
$style = $stmt->fetch(PDO::FETCH_ASSOC);
$currentTemplate = $style ? ($style['template_name'] === 'pink_classic' ? 'Pink Classic' : 'Frutiger Aero') : 'Frutiger Aero';

// Fetch user contributions points
$stmt = $pdo->prepare("SELECT user_contributions FROM user WHERE id_usr = :uid");
$stmt->execute([':uid' => $_SESSION['id_usr']]);
$points = $stmt->fetchColumn() ?: 0;

// Define unlocks
$unlocks = [
    30 => ['name' => 'Template Desbloqueado', 'icon' => ''],
    60 => ['name' => 'Logo Dorado', 'icon' => '♕'],
    80 => ['name' => 'Ayudante', 'icon' => '♡'],
    100 => ['name' => 'Insignia', 'icon' => '✈'],
    150 => ['name' => 'Solicitar Mod', 'icon' => '𓆩♡𓆪']
];

// Calculate next unlock
$nextUnlockPoints = 150;
$nextUnlockName = "Max Level";
foreach ($unlocks as $p => $data) {
    if ($points < $p) {
        $nextUnlockPoints = $p;
        $nextUnlockName = $data['name'];
        break;
    }
}

$progressPercent = min(100, ($points / $nextUnlockPoints) * 100);
if ($points >= 150)
    $progressPercent = 100;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta - CbNoticias</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;700&family=Fira+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/account-inf.css">
    <style>
        .style-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .style-info h3 { margin: 0 0 0.5rem 0; color: var(--primary); }
        .style-info p { margin: 0; opacity: 0.8; }

        /* Dropdown toggle styles */
        .dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            user-select: none;
            padding: 0.5rem 0;
        }

        .dropdown-icon {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
            color: var(--primary);
        }

        .dropdown-icon.expanded {
            transform: rotate(180deg);
        }

        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out, opacity 0.3s ease-out;
            opacity: 0;
        }

        .dropdown-content.expanded {
            max-height: 1000px;
            opacity: 1;
            transition: max-height 0.5s ease-in, opacity 0.4s ease-in;
        }
    </style>
</head>

<body class="ey">

    <nav class="nav">
        <div class='logo'>
            <h2> CbNoticias</h2>
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

    <div class="account-container">
        <div class="account-header">
            <h1> Mi Cuenta</h1>
            <p>Gestiona tu información personal y estadísticas</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $totalblogs; ?></div>
                <div class="stat-label">Blogs Publicados</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $dias; ?></div>
                <div class="stat-label">Días Registrado</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $_SESSION['grade'] ?? 1; ?></div>
                <div class="stat-label">Nivel Escritor</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo TraduceSQLfecha($_SESSION['fecha_registro'] ?? ''); ?></div>
                <div class="stat-label">Fecha Registro</div>
            </div>
        </div>

        <!-- Points & Progress Section -->
        <div class="style-card"
            style="display:block; margin-bottom: 2rem; animation: fadeInUp 0.8s ease-out forwards; animation-delay: 0.6s; opacity: 0;">
            <div class="dropdown-header" onclick="toggleDropdown('pointsDropdown')">
                <h3 style="margin:0;"> Puntos de Contribución: <?php echo $points; ?></h3>
                <span class="dropdown-icon" id="pointsDropdownIcon">▼</span>
            </div>

            <div class="dropdown-content" id="pointsDropdown">
                <div style="display:flex; justify-content:space-between; align-items:center; margin:1rem 0;">
                    <span>Siguiente: <?php echo $nextUnlockName; ?> (<?php echo $nextUnlockPoints; ?> pts)</span>
                </div>

                <div
                    style="background:rgba(255,255,255,0.1); border-radius:10px; height:20px; width:100%; overflow:hidden;">
                    <div
                        style="background:var(--primary); height:100%; width:<?php echo $progressPercent; ?>%; transition: width 0.5s ease;">
                    </div>
                </div>

                <div style="margin-top:1.5rem; display:flex; gap:1rem; flex-wrap:wrap;">
                    <?php foreach ($unlocks as $reqPoints => $data): ?>
                        <div
                            style="background: <?php echo $points >= $reqPoints ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.2)'; ?>; 
                                    padding: 0.5rem 1rem; border-radius: 8px; 
                                    opacity: <?php echo $points >= $reqPoints ? '1' : '0.5'; ?>;
                                    border: 1px solid <?php echo $points >= $reqPoints ? 'var(--primary)' : 'transparent'; ?>;">
                            <?php echo $data['icon']; ?>     <?php echo $data['name']; ?>
                            <span style="font-size:0.8em; opacity:0.7;">(<?php echo $reqPoints; ?> pts)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="personal-info-row">
            <div class="info-card-personal">
                <h3> Información Personal</h3>
                <div class="info-item">
                    <span class="info-label">Usuario:</span>
                    <span class="info-value"><?php echo htmlEscape($_SESSION['usuario']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?php echo htmlEscape($_SESSION['nombre']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Correo:</span>
                    <span class="info-value"><?php echo htmlEscape($_SESSION['email'] ?? 'No especificado'); ?></span>
                </div>
            </div>

            <div class="info-card-general">
                <h3> Preferencias</h3>
                <div class="info-item">
                    <span class="info-label">Género Favorito:</span>
                    <span class="info-value"><?php echo htmlEscape($_SESSION['genero_lit_fav'] ?? 'General'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nivel:</span>
                    <span class="info-value">
                        <span class="grade-indicator grade-<?php echo $_SESSION['grade'] ?? 1; ?>">
                            Grado <?php echo $_SESSION['grade'] ?? 1; ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Usuario numero:</span>
                    <span class="info-value"><?php echo $_SESSION['id_usr']; ?></span>
                </div>
            </div>
        </div>

        <div class="style-card"
            style="display:block; animation: fadeInUp 0.8s ease-out forwards; animation-delay: 0.8s; opacity: 0;">
            <div class="dropdown-header" onclick="toggleDropdown('styleDropdown')">
                <h3 style="margin:0;"> Apariencia del Blog</h3>
                <span class="dropdown-icon" id="styleDropdownIcon">▼</span>
            </div>

            <div class="dropdown-content" id="styleDropdown">
                <div style="margin-top: 1rem;">
                    <p>Tema actual: <strong><?php echo $currentTemplate; ?></strong></p>
                    <a href="edit_blog_style.php" class="btn" style="margin-top: 1rem;">Personalizar Estilo</a>
                </div>
            </div>
        </div>

        <div class="edit-section">
            <h3> Editar Información</h3>
            <p style="margin-bottom: 1rem;">Actualiza tus datos personales y preferencias</p>
            <button class="btn" onclick="window.location.href='updateAcc.php'" id="editBtn">Editar Información</button>
        </div>
    </div>

    <footer class="footer">
        &copy; 2025 CbNoticias. Suerte
    </footer>

    <script>
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const icon = document.getElementById(dropdownId + 'Icon');

            if (dropdown.classList.contains('expanded')) {
                dropdown.classList.remove('expanded');
                icon.classList.remove('expanded');
            } else {
                dropdown.classList.add('expanded');
                icon.classList.add('expanded');
            }
        }
    </script>
</body>

</html>