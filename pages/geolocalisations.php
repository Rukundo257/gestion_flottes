<?php
define('BASE_URL', '/gestion_flottes/');
session_start();

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

// Récupérer toutes les géolocalisations
$geos = [];
$error = '';
try {
    $stmt = $pdo->query("
        SELECT g.id_geo, t.id_trajet, v.immatriculation, c.nom, c.prenom, g.latitude, g.longitude, g.vitesse, g.direction, g.date_heure
        FROM geolocalisation g
        JOIN trajet t ON g.id_trajet = t.id_trajet
        JOIN vehicule v ON t.id_vehicule = v.id_vehicule
        JOIN conducteur c ON t.id_conducteur = c.id_conducteur
        ORDER BY g.id_geo DESC
    ");
    $geos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des géolocalisations : " . $e->getMessage();
}

// Gestion POST (ajout / suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $id_trajet = (int) $_POST['id_trajet'];
        $latitude = (float) $_POST['latitude'];
        $longitude = (float) $_POST['longitude'];
        $vitesse = (float) $_POST['vitesse'];
        $direction = trim($_POST['direction']);
        $date_heure = $_POST['date_heure'];

        if ($id_trajet && $latitude && $longitude && $vitesse >= 0 && $direction && $date_heure) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO geolocalisation (id_trajet, latitude, longitude, vitesse, direction, date_heure)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$id_trajet, $latitude, $longitude, $vitesse, $direction, $date_heure]);
                header("Location: geolocalisation.php");
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        } else {
            $error = "Tous les champs sont obligatoires et doivent être valides.";
        }
    } elseif (isset($_POST['delete'], $_POST['id_geo'])) {
        $id = (int) $_POST['id_geo'];
        try {
            $stmt = $pdo->prepare("DELETE FROM geolocalisation WHERE id_geo = ?");
            $stmt->execute([$id]);
            header("Location: geolocalisation.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

// Inclure sidebar et topbar
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';

// Récupérer trajets pour le formulaire
$trajets = $pdo->query("
    SELECT t.id_trajet, v.immatriculation, c.nom, c.prenom
    FROM trajet t
    JOIN vehicule v ON t.id_vehicule = v.id_vehicule
    JOIN conducteur c ON t.id_conducteur = c.id_conducteur
    ORDER BY t.id_trajet DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Géolocalisation - Gestion Flottes</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    .main-content { padding: 2rem; animation: fadeInUp 0.6s ease-out; }
    .table-container { background: rgba(255,255,255,0.95); padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .btn-custom { background: #27ae60; color: white; border-radius: 6px; transition: 0.3s; }
    .btn-custom:hover { background: #1e8449; }
    .btn-danger-custom { background: #e74c3c; color: white; border-radius: 6px; transition: 0.3s; }
    .btn-danger-custom:hover { background: #c0392b; }
    .table th, .table td { vertical-align: middle; }
    @keyframes fadeInUp { from { opacity:0; transform: translateY(20px);} to {opacity:1; transform:translateY(0);} }
</style>
</head>
<body>
<div class="main-wrapper d-flex">
    <?php renderSidebar('geolocalisation'); ?>
    <div class="main-content-wrapper flex-grow-1">
        <?php renderTopBar(); ?>
        <div class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="table-container">
                <h2 class="mb-4">Géolocalisation des Trajets</h2>
                <button class="btn btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addGeoModal">
                    <i class="fas fa-plus me-2"></i> Ajouter un point GPS
                </button>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Trajet</th>
                            <th>Véhicule</th>
                            <th>Conducteur</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Vitesse (km/h)</th>
                            <th>Direction</th>
                            <th>Date/heure</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$geos): ?>
                            <tr><td colspan="10" class="text-center text-muted">Aucun point GPS enregistré.</td></tr>
                        <?php else: ?>
                            <?php foreach ($geos as $g): ?>
                                <tr>
                                    <td><?= $g['id_geo'] ?></td>
                                    <td><?= htmlspecialchars($g['id_trajet']) ?></td>
                                    <td><?= htmlspecialchars($g['immatriculation']) ?></td>
                                    <td><?= htmlspecialchars($g['nom'].' '.$g['prenom']) ?></td>
                                    <td><?= number_format($g['latitude'], 6) ?></td>
                                    <td><?= number_format($g['longitude'], 6) ?></td>
                                    <td><?= number_format($g['vitesse'], 2, ',', ' ') ?></td>
                                    <td><?= htmlspecialchars($g['direction']) ?></td>
                                    <td><?= htmlspecialchars($g['date_heure']) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                            <input type="hidden" name="id_geo" value="<?= $g['id_geo'] ?>">
                                            <button type="submit" name="delete" class="btn btn-danger-custom btn-sm">
                                                <i class="fas fa-trash me-1"></i> Supprimer
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="addGeoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un point GPS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select name="id_trajet" class="form-select mb-2" required>
                        <option value="">Sélectionner un trajet</option>
                        <?php foreach ($trajets as $t): ?>
                            <option value="<?= $t['id_trajet'] ?>">ID <?= $t['id_trajet'] ?> - <?= htmlspecialchars($t['immatriculation'].' ('.$t['nom'].' '.$t['prenom'].')') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="latitude" placeholder="Latitude" class="form-control mb-2" step="0.000001" required>
                    <input type="number" name="longitude" placeholder="Longitude" class="form-control mb-2" step="0.000001" required>
                    <input type="number" name="vitesse" placeholder="Vitesse (km/h)" class="form-control mb-2" step="0.01" required>
                    <input type="text" name="direction" placeholder="Direction" class="form-control mb-2" required>
                    <input type="datetime-local" name="date_heure" class="form-control mb-2" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add" class="btn btn-custom">Ajouter</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
