<?php
define('BASE_URL', '/gestion_flottes/');
session_start();
$pdo = require_once __DIR__ . '/../config/database.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!$pdo) {
        die("Échec de la connexion à la base de données.");
    }

    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateur (nom_utilisateur, mot_de_passe, role) VALUES (:username, :password, 'admin')");
            $stmt->execute([':username' => $username, ':password' => $hashed_password]);
            header("Location: login.php?success=Compte créé avec succès");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription - Gestion Flottes</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body {
    background: linear-gradient(135deg, #27ae60, #1e8449);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
}
.register-container {
    background-color: #fff;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    width: 100%;
    max-width: 400px;
    animation: fadeInUp 0.6s ease-out;
}
.register-container h2 {
    text-align: center;
    margin-bottom: 1.5rem;
    color: #27ae60;
}
.btn-primary {
    background-color: #27ae60;
    border: none;
    transition: 0.3s;
}
.btn-primary:hover {
    background-color: #1e8449;
}
a { color: #27ae60; }
a:hover { text-decoration: underline; }
@keyframes fadeInUp { from {opacity:0; transform: translateY(20px);} to {opacity:1; transform: translateY(0);} }
</style>
</head>
<body>
<div class="register-container">
    <h2>FENACOBU - Inscription Admin</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['success']); ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer mot de passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-user-plus me-1"></i> Créer compte</button>
    </form>
    <p class="mt-3 text-center"><a href="<?= BASE_URL ?>pages/login.php">Retour à la connexion</a></p>
</div>
<script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
