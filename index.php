<?php
define('BASE_URL', '/gestion_flottes/');
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/dashboard.php");
} else {
    header("Location: " . BASE_URL . "pages/login.php");
}
exit();
?>
