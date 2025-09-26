<?php
define('BASE_URL', '/gestion_flottes/');
session_start();

// Redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

// Connexion PDO
try {
    $pdo = require_once __DIR__ . '/../config/database.php';
} catch (Exception $e) {
    die("Échec de la connexion à la base de données : " . $e->getMessage());
}

// Récupérer les statistiques
$stats = [];
try {
    $tables = ['vehicule', 'conducteur', 'affectation', 'assurance', 'maintenance', 'carburant', 'trajet', 'geolocalisation', 'utilisateur'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $stats[$table] = $stmt->fetchColumn() ?? 0;
    }
    $total_km = $pdo->query("SELECT SUM(kilometrage) FROM vehicule")->fetchColumn() ?? 0;
    $total_maintenance = $pdo->query("SELECT SUM(cout) FROM maintenance")->fetchColumn() ?? 0;
    $total_carburant = $pdo->query("SELECT SUM(cout_total) FROM carburant")->fetchColumn() ?? 0;
} catch (PDOException $e) {
    $error = "Erreur lors du chargement des statistiques : " . $e->getMessage();
}

$pageTitle = 'Dashboard - Gestion Flottes';

// --- Fonctions Sidebar & Topbar ---
function renderSidebar($activePage = '') {
    ?>
    <nav class="sidebar">
        <div class="logo">FENACOBU Flottes</div>
        <ul class="sidebar-nav">
            <li><a href="<?= BASE_URL ?>pages/dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>"><i class="fas fa-home"></i> Tableau de Bord</a></li>
            <li><a href="<?= BASE_URL ?>pages/vehicules.php" class="<?= $activePage === 'vehicules' ? 'active' : '' ?>"><i class="fas fa-car"></i> Véhicules</a></li>
            <li><a href="<?= BASE_URL ?>pages/conducteurs.php" class="<?= $activePage === 'conducteurs' ? 'active' : '' ?>"><i class="fas fa-user-tie"></i> Conducteurs</a></li>
            <li><a href="<?= BASE_URL ?>pages/affectations.php" class="<?= $activePage === 'affectations' ? 'active' : '' ?>"><i class="fas fa-exchange-alt"></i> Affectations</a></li>
            <li><a href="<?= BASE_URL ?>pages/assurances.php" class="<?= $activePage === 'assurances' ? 'active' : '' ?>"><i class="fas fa-shield-alt"></i> Assurances</a></li>
            <li><a href="<?= BASE_URL ?>pages/maintenances.php" class="<?= $activePage === 'maintenances' ? 'active' : '' ?>"><i class="fas fa-tools"></i> Maintenances</a></li>
            <li><a href="<?= BASE_URL ?>pages/carburants.php" class="<?= $activePage === 'carburants' ? 'active' : '' ?>"><i class="fas fa-gas-pump"></i> Carburant</a></li>
            <li><a href="<?= BASE_URL ?>pages/trajets.php" class="<?= $activePage === 'trajets' ? 'active' : '' ?>"><i class="fas fa-route"></i> Trajets</a></li>
            <li><a href="<?= BASE_URL ?>pages/geolocalisations.php" class="<?= $activePage === 'geolocalisations' ? 'active' : '' ?>"><i class="fas fa-map-marker-alt"></i> Géolocalisation</a></li>
            <li><a href="<?= BASE_URL ?>pages/utilisateurs.php" class="<?= $activePage === 'utilisateurs' ? 'active' : '' ?>"><i class="fas fa-users"></i> Utilisateurs</a></li>
            <li><a href="<?= BASE_URL ?>pages/logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </nav>
    <?php
}

function renderTopBar() {
    $username = $_SESSION['username'] ?? 'Utilisateur';
    $role = $_SESSION['role'] ?? 'Non défini';
    ?>
    <div class="top-bar">
        <div class="greeting">Bonjour, <strong><?= htmlspecialchars($username) ?></strong> <small>(Rôle : <?= htmlspecialchars($role) ?>)</small></div>
        <div class="user-info">
            <span><?= date('d M Y H:i') ?></span>
            <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
        </div>
    </div>
    <?php
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
</head>
<body>
<div class="main-wrapper d-flex">
    <!-- Sidebar -->
    <?php renderSidebar('dashboard'); ?>

    <!-- Main content area -->
    <div class="main-content-wrapper flex-grow-1">
        <!-- Topbar -->
        <?php renderTopBar(); ?>

        <!-- Main Content -->
        <div class="main-content">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Véhicules</h3>
                    <div class="stat-value"><?= $stats['vehicule'] ?? 0 ?></div>
                    <div class="stat-sub">Total km: <?= number_format($total_km ?? 0, 0) ?> km</div>
                    <a href="<?= BASE_URL ?>pages/vehicules.php" class="action-btn">Gérer</a>
                </div>
                <div class="stat-card" style="border-left-color: var(--accent-green);">
                    <h3>Conducteurs</h3>
                    <div class="stat-value"><?= $stats['conducteur'] ?? 0 ?></div>
                    <div class="stat-sub">Actifs</div>
                    <a href="<?= BASE_URL ?>pages/conducteurs.php" class="action-btn">Gérer</a>
                </div>
                <div class="stat-card" style="border-left-color: var(--accent-yellow);">
                    <h3>Affectations</h3>
                    <div class="stat-value"><?= $stats['affectation'] ?? 0 ?></div>
                    <div class="stat-sub">En cours</div>
                    <a href="<?= BASE_URL ?>pages/affectations.php" class="action-btn">Gérer</a>
                </div>
                <div class="stat-card" style="border-left-color: var(--accent-red);">
                    <h3>Assurances</h3>
                    <div class="stat-value"><?= $stats['assurance'] ?? 0 ?></div>
                    <div class="stat-sub">Valides</div>
                    <a href="<?= BASE_URL ?>pages/assurances.php" class="action-btn">Gérer</a>
                </div>
                <div class="stat-card" style="border-left-color: var(--accent-green);">
                    <h3>Maintenances</h3>
                    <div class="stat-value"><?= $stats['maintenance'] ?? 0 ?></div>
                    <div class="stat-sub">Coût total: <?= number_format($total_maintenance ?? 0, 2) ?> €</div>
                    <a href="<?= BASE_URL ?>pages/maintenances.php" class="action-btn">Gérer</a>
                </div>
                <div class="stat-card" style="border-left-color: #9b59b6;">
                    <h3>Carburant</h3>
                    <div class="stat-value"><?= $stats['carburant'] ?? 0 ?></div>
                    <div class="stat-sub">Coût total: <?= number_format($total_carburant ?? 0, 2) ?> €</div>
                    <a href="<?= BASE_URL ?>pages/carburants.php" class="action-btn">Gérer</a>
                </div>
            </div>

            <!-- Metrics Section -->
            <div class="metrics-section">
                <div class="metrics-card">
                    <h3>Performances Clés</h3>
                    <div class="metric-item">
                        <span class="metric-label">Véhicules Disponibles</span>
                        <span class="metric-value"><?= $pdo->query("SELECT COUNT(*) FROM vehicule WHERE statut = 'disponible'")->fetchColumn() ?? 0 ?> / <?= $stats['vehicule'] ?? 0 ?></span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Trajets Moyens par Mois</span>
                        <span class="metric-value"><?= round(($stats['trajet'] ?? 0) / 12, 1) ?></span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Coût Maintenance / Véhicule</span>
                        <span class="metric-value"><?= $stats['vehicule'] ? round(($total_maintenance ?? 0) / $stats['vehicule'], 2) : 0 ?> €</span>
                    </div>
                </div>
                <div class="metrics-card">
                    <h3>Activité Récente</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem; font-size: 0.9rem;"><i class="fas fa-car" style="color: var(--accent-green); margin-right: 0.5rem;"></i> Nouveau véhicule ajouté</li>
                        <li style="margin-bottom: 0.5rem; font-size: 0.9rem;"><i class="fas fa-route" style="color: var(--accent-yellow); margin-right: 0.5rem;"></i> Trajet complété</li>
                        <li style="margin-bottom: 0.5rem; font-size: 0.9rem;"><i class="fas fa-tools" style="color: var(--accent-red); margin-right: 0.5rem;"></i> Maintenance planifiée</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/scripts.js"></script>
</body>
</html>
