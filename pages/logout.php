<?php
session_start();
session_destroy();
define('BASE_URL', '/gestion_flottes/');
header("Location: " . BASE_URL . "pages/login.php");
exit();
?>