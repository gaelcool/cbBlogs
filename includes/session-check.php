<?php
// Session Check Script - Include this at the top of protected pages
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit;
}
?>
