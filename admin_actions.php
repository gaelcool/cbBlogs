<?php
require_once 'lib/common.php';
session_start();

// Ensure only admins can access
requiereAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'delete_user') {
            $id_usr = $_POST['id_usr'] ?? null;
            
            if ($id_usr && $id_usr > 3) { // Double check protection
                if (deleteUser($pdo, $id_usr)) {
                    header('Location: admin_dashboard.php?success=Usuario eliminado correctamente');
                    exit();
                } else {
                    throw new Exception('No se pudo eliminar el usuario');
                }
            } else {
                throw new Exception('ID de usuario inválido o protegido');
            }
        } 
        elseif ($action === 'delete_post') {
            $post_id = $_POST['post_id'] ?? null;
            
            if ($post_id) {
                if (deletePost($pdo, $post_id)) {
                    header('Location: admin_dashboard.php?success=Blog eliminado correctamente');
                    exit();
                } else {
                    throw new Exception('No se pudo eliminar el blog');
                }
            } else {
                throw new Exception('ID de blog inválido');
            }
        }
        else {
            header('Location: admin_dashboard.php?error=Acción inválida');
            exit();
        }
    } catch (Exception $e) {
        error_log("Admin Action Error: " . $e->getMessage());
        header('Location: admin_dashboard.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    header('Location: admin_dashboard.php');
    exit();
}
?>
