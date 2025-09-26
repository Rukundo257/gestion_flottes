<?php
session_start();
$pdo = require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!$pdo) {
        die("Échec de la connexion à la base de données.");
    }

    try {
        $stmt = $pdo->prepare("SELECT id_utilisateur, nom_utilisateur, mot_de_passe, role FROM utilisateur WHERE nom_utilisateur = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['username'] = $user['nom_utilisateur'];
            $_SESSION['role'] = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $error = "Erreur serveur : " . $e->getMessage();
    }
}

if (isset($error)) {
    header("Location: login.php?error=" . urlencode($error));
    exit();
}
?>