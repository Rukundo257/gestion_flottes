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

// Récupérer tous les trajets
$trajets = [];
$error = '';
try {
    $stmt = $pdo->query("
        SELECT t.id_trajet, v.immatriculation, c.nom, c.prenom, t.point_depart, t.point_arrivee, t.date_heure_debut, t.date_heure_fin, t.distance_km, t.description
        FROM trajet t
        JOIN vehicule v ON t.id_vehicule = v.id_vehicule
        JOIN conducteur c ON t.id_conducteur = c.id_conducteur
        ORDER BY t.id_trajet DESC
    ");
    $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des trajets : " . $e->getMessage();
}

// Gestion POST (ajout / suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $id_vehicule = (int) $_POST['id_vehicule'];
        $id_conducteur = (int) $_POST['id_conducteur'];
        $date_debut = $_POST['date_heure_debut'];
        $date_fin = $_POST['date_heure_fin'] ?: null;
        $point_depart = trim($_POST['point_depart']);
        $point_arrivee = trim($_POST['point_arrivee']);
        $distance_km = (float) $_POST['distance_km'];
        $description = trim($_POST['description']);

        if ($id_vehicule && $id_conducteur && $date_debut && $point_depart && $point_arrivee && $distance_km >= 0) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO trajet (id_vehicule, id_conducteur, date_heure_debut, date_heure_fin, point_depart, point_arrivee, distance_km, description)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$id_vehicule, $id_conducteur, $date_debut, $date_fin, $point_depart, $point_arrivee, $distance_km, $description]);
                header("Location: trajets.php");
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        } else {
            $error = "Tous les champs obligatoires doivent être remplis.";
        }
    } elseif (isset($_POST['delete'], $_POST['id_trajet'])) {
        $id = (int) $_POST['id_trajet'];
        try {
            $stmt = $pdo->prepare("DELETE FROM trajet WHERE id_trajet = ?");
            $stmt->execute([$id]);
            header("Location: trajets.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

// Inclure sidebar et topbar
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';

// Récupérer véhicules et conducteurs pour le formulaire
$vehicules = $pdo->query("SELECT id_vehicule, immatriculation FROM vehicule ORDER BY immatriculation")->fetchAll(PDO::FETCH_ASSOC);
$conducteurs = $pdo->query("SELECT id_conducteur, nom, prenom FROM conducteur ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Trajets - Gestion Flottes</title>
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
    <?php renderSidebar('trajets'); ?>
    <div class="main-content-wrapper flex-grow-1">
        <?php renderTopBar(); ?>
        <div class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="table-container">
                <h2 class="mb-4">Gestion des Trajets</h2>
                <button class="btn btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addTrajetModal">
                    <i class="fas fa-plus me-2"></i> Ajouter un trajet
                </button>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Véhicule</th>
                            <th>Conducteur</th>
                            <th>Départ</th>
                            <th>Arrivée</th>
                            <th>Date/heure début</th>
                            <th>Date/heure fin</th>
                            <th>Distance (km)</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$trajets): ?>
                            <tr><td colspan="10" class="text-center text-muted">Aucun trajet enregistré.</td></tr>
                        <?php else: ?>
                            <?php foreach ($trajets as $t): ?>
                                <tr>
                                    <td><?= $t['id_trajet'] ?></td>
                                    <td><?= htmlspecialchars($t['immatriculation']) ?></td>
                                    <td><?= htmlspecialchars($t['nom'].' '.$t['prenom']) ?></td>
                                    <td><?= htmlspecialchars($t['point_depart']) ?></td>
                                    <td><?= htmlspecialchars($t['point_arrivee']) ?></td>
                                    <td><?= htmlspecialchars($t['date_heure_debut']) ?></td>
                                    <td><?= htmlspecialchars($t['date_heure_fin'] ?? '-') ?></td>
                                    <td><?= number_format($t['distance_km'], 2, ',', ' ') ?></td>
                                    <td><?= htmlspecialchars($t['description']) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                            <input type="hidden" name="id_trajet" value="<?= $t['id_trajet'] ?>">
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
<div class="modal fade" id="addTrajetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un trajet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select name="id_vehicule" class="form-select mb-2" required>
                        <option value="">Sélectionner un véhicule</option>
                        <?php foreach ($vehicules as $v): ?>
                            <option value="<?= $v['id_vehicule'] ?>"><?= htmlspecialchars($v['immatriculation']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="id_conducteur" class="form-select mb-2" required>
                        <option value="">Sélectionner un conducteur</option>
                        <?php foreach ($conducteurs as $c): ?>
                            <option value="<?= $c['id_conducteur'] ?>"><?= htmlspecialchars($c['nom'].' '.$c['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="datetime-local" name="date_heure_debut" class="form-control mb-2" required>
                    <input type="datetime-local" name="date_heure_fin" class="form-control mb-2">
                    <input type="text" name="point_depart" placeholder="Point de départ" class="form-control mb-2" required>
                    <input type="text" name="point_arrivee" placeholder="Point d'arrivée" class="form-control mb-2" required>
                    <input type="number" name="distance_km" placeholder="Distance (km)" class="form-control mb-2" step="0.01" required>
                    <textarea name="description" placeholder="Description" class="form-control mb-2"></textarea>
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
