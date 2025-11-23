<?php

function getRootPath(){
    return realpath(__DIR__ . '/..');
}

function getDatabasePath(){
    return getRootPath() . '/data/data.sqlite';
}

function getDsn(){
    return 'sqlite:' . getDatabasePath();
}

function getPDO()
{
    $pdo = new PDO(getDsn());
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Foreign key constraints need to be enabled manually in SQLite
    $result = $pdo->query('PRAGMA foreign_keys = ON');
    if ($result === false)
    {
        throw new Exception('Could not turn on foreign key constraints');
    }

    return $pdo;
}

function htmlEscape($html)
{
    return htmlspecialchars($html, ENT_HTML5, 'UTF-8');
}

function TraduceSQLfecha($sqlDate)
{
    if (empty($sqlDate)) {
        return 'Unknown date';
    }
    
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $sqlDate);
    
    if ($date === false) {
        $date = DateTime::createFromFormat('Y-m-d', $sqlDate);
    }
    
    if ($date === false) {
        return $sqlDate;
    }
    
    return $date->format('d/m/Y');
}


function intentaLogin(PDO $pdo, $usuario, $clave)
{
    $sql = "
        SELECT
            id_usr, usuario, nombre, email, clave, genero_lit_fav, fecha_registro, grade
        FROM
            user
        WHERE
            usuario = :usuario
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario' => $usuario]);
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Direct password comparison (plain text - matching your system)
    if ($user && $user['clave'] === $clave) {
        return $user;
    }
    
    return false;
}


function convertnewlines($content)
{
    $escaped = htmlEscape($content);
    
    // Normalize line endings to \n
    $escaped = str_replace(array("\r\n", "\r"), "\n", $escaped);
    
    // Split into paragraphs by double newlines
    $paragraphs = explode("\n\n", $escaped);
    
    $formatted = '';
    
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if (!empty($paragraph)) {
            // Convert single newlines within paragraph to <br>
            $paragraphWithBreaks = nl2br($paragraph);
            $formatted .= '<p>' . $paragraphWithBreaks . '</p>';
        }
    }
    
    return $formatted;
}

function login($usuario, $nombre, $genero_lit_fav = null, $fecha_registro = null, $grade = 1, $email = null, $id_usr = null)
{
    session_regenerate_id(true);
    $_SESSION['usuario'] = $usuario;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['genero_lit_fav'] = $genero_lit_fav ?? 'General';
    $_SESSION['fecha_registro'] = $fecha_registro;
    $_SESSION['grade'] = $grade ?? 1;
    $_SESSION['email'] = $email;
    $_SESSION['id_usr'] = $id_usr;
    $_SESSION['logged_in'] = true;
}

function isLoggedIn()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if current user is an admin (ID 0-3)
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['id_usr']) && $_SESSION['id_usr'] <= 3;
}

/**
 * Require login - redirect if not logged in
 */
function requiereLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Require admin - redirect if not admin
 */
function requiereAdmin() {
    requiereLogin();
    if (!isAdmin()) {
        header('Location: LP.php');
        exit();
    }
}


function userExists(PDO $pdo, $usuario)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE usuario = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Check if an email exists - FIXED :)
 */
function emailExists(PDO $pdo, $email)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = :email");
    $stmt->execute([':email' => $email]);
    return $stmt->fetchColumn() > 0;
}


function logout()
{
    session_unset();
    session_destroy();
}


//calcular edad de cuenta de usuario
function calcularDiasRegistrado($fecha_registro) {
    // Convert DB timestamp into a DateTime object
    $fecha_reg = new DateTime($fecha_registro);
    $hoy = new DateTime();
 //whyy no funciona :((((( 
    // Difference in days
    $dias = $hoy->diff($fecha_reg)->days;

    return $dias;
}


/**
 * Fetch TODOS los usuarios del db
 */
function fetchAllusuarios() {
    $pdo = getPDO();
    $stmt = $pdo->prepare('
        SELECT id_usr, usuario, nombre, email, genero_lit_fav, fecha_registro
        FROM user
        ORDER BY id_usr ASC
    ');

    if (!$stmt->execute()) {
        throw new Exception('Failed to fetch users from database');
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function fetchAllPosts() {
    $pdo = getPDO();
    $query = $pdo->query('
        SELECT id, title, subtitle, author_name, content, created_at, tag
        FROM post
        ORDER BY created_at DESC
    ');

    if ($query === false) {
        throw new Exception('Failed to fetch posts from database');
    }

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch all commentarios de la db
 */
function fetchAllComments() {  
    $pdo = getPDO();
    $query = $pdo->query('
        SELECT user_id_C, text, created_at, grade
        FROM comment
        ORDER BY created_at DESC
    ');

    if ($query === false) {
        throw new Exception('Failed to fetch comments from database');
    }

    return $query->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get user data by username
 */
function getUserByUsername(PDO $pdo, $usuario)
{
    $sql = "
        SELECT
            id_usr, usuario, nombre, email, clave, genero_lit_fav, fecha_registro, grade
        FROM
            user
        WHERE
            usuario = :usuario
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario' => $usuario]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update user information (excluding password and username)
 */
function updateUserInfo(PDO $pdo, $usuario, $nombre, $email, $genero_lit_fav)
{
    $sql = "
        UPDATE user
        SET nombre = :nombre,
            email = :email,
            genero_lit_fav = :genero_lit_fav
        WHERE usuario = :usuario
    ";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        'nombre' => $nombre,
        'email' => $email,
        'genero_lit_fav' => $genero_lit_fav,
        'usuario' => $usuario
    ]);
}

/**
 * Count total posts by a specific user
 */
function countUserPosts(PDO $pdo, $usuario)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM post WHERE author_name = :usuario");
    $stmt->execute([':usuario' => $usuario]);
    return (int)$stmt->fetchColumn();
}

/**
 * Delete a user by ID
 */
function deleteUser(PDO $pdo, $id_usr) {
    // Prevent deleting self or super admins if needed, but for now just delete
    $stmt = $pdo->prepare("DELETE FROM user WHERE id_usr = :id_usr");
    return $stmt->execute([':id_usr' => $id_usr]);
}

/**
 * Delete a post by ID
 */
function deletePost(PDO $pdo, $post_id) {
    $stmt = $pdo->prepare("DELETE FROM post WHERE id = :id");
    return $stmt->execute([':id' => $post_id]);
}

/**
 * Get a single post by ID
 */
function getPostById(PDO $pdo, $post_id) {
    $stmt = $pdo->prepare("
        SELECT id, title, subtitle, author_name, content, created_at, tag
        FROM post
        WHERE id = :id
    ");
    $stmt->execute([':id' => $post_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

?>