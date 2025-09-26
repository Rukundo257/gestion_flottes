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

    try {
        $stmt = $pdo->prepare("SELECT id_utilisateur, nom_utilisateur, mot_de_passe, role FROM utilisateur WHERE nom_utilisateur = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['username'] = $user['nom_utilisateur'];
            $_SESSION['role'] = $user['role'];
            header("Location: " . BASE_URL . "pages/dashboard.php");
            exit();
        } else {
            $error = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } catch (PDOException $e) {
        $error = "Erreur serveur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion - Gestion Flottes</title>
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
    .login-container {
        background-color: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        width: 100%;
        max-width: 400px;
        animation: fadeInUp 0.6s ease-out;
    }
    .login-container h2 {
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
<div class="login-container">
    <h2>FENACOBU - Connexion</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
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
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt me-1"></i> Se connecter</button>
    </form>
    <p class="mt-3 text-center"><a href="<?= BASE_URL ?>pages/register.php">Créer un compte</a></p>
</div>
<script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
