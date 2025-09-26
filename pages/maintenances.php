<?php
define('BASE_URL', '/gestion_flottes/');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

try {
    $pdo = require_once __DIR__ . '/../config/database.php';
} catch (Exception $e) {
    die("Échec de la connexion à la base de données : " . $e->getMessage());
}

// Récupérer toutes les maintenances
$maintenances = [];
$error = '';
try {
    $stmt = $pdo->query("
        SELECT m.id_maintenance, v.immatriculation, m.description, m.date_maintenance, m.cout
        FROM maintenance m
        JOIN vehicule v ON m.id_vehicule = v.id_vehicule
        ORDER BY m.id_maintenance DESC
    ");
    $maintenances = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des maintenances : " . $e->getMessage();
}

// Gestion POST (ajout / suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $id_vehicule = (int) $_POST['id_vehicule'];
        $description = trim($_POST['description']);
        $date_maintenance = $_POST['date_maintenance'];
        $cout = (float) $_POST['cout'];

        if ($id_vehicule && $description && $date_maintenance && $cout >= 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO maintenance (id_vehicule, description, date_maintenance, cout) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id_vehicule, $description, $date_maintenance, $cout]);
                header("Location: maintenances.php");
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        } else {
            $error = "Tous les champs sont obligatoires.";
        }
    } elseif (isset($_POST['delete'], $_POST['id_maintenance'])) {
        $id = (int) $_POST['id_maintenance'];
        try {
            $stmt = $pdo->prepare("DELETE FROM maintenance WHERE id_maintenance = ?");
            $stmt->execute([$id]);
            header("Location: maintenances.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

// Inclure sidebar et topbar
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';

// Récupérer les véhicules pour le formulaire
$vehicules = $pdo->query("SELECT id_vehicule, immatriculation FROM vehicule ORDER BY immatriculation")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenances - Gestion Flottes</title>
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
    <?php renderSidebar('maintenances'); ?>
    <div class="main-content-wrapper flex-grow-1">
        <?php renderTopBar(); ?>
        <div class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-container">
                <h2 class="mb-4">Gestion des Maintenances</h2>
                <button class="btn btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                    <i class="fas fa-plus me-2"></i> Ajouter une maintenance
                </button>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Véhicule</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Coût (€)</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$maintenances): ?>
                            <tr><td colspan="6" class="text-center text-muted">Aucune maintenance enregistrée.</td></tr>
                        <?php else: ?>
                            <?php foreach ($maintenances as $m): ?>
                                <tr>
                                    <td><?= $m['id_maintenance'] ?></td>
                                    <td><?= htmlspecialchars($m['immatriculation']) ?></td>
                                    <td><?= htmlspecialchars($m['description']) ?></td>
                                    <td><?= htmlspecialchars($m['date_maintenance']) ?></td>
                                    <td><?= number_format($m['cout'], 2, ',', ' ') ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                            <input type="hidden" name="id_maintenance" value="<?= $m['id_maintenance'] ?>">
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
<div class="modal fade" id="addMaintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une maintenance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select name="id_vehicule" class="form-select mb-2" required>
                        <option value="">Sélectionner un véhicule</option>
                        <?php foreach ($vehicules as $v): ?>
                            <option value="<?= $v['id_vehicule'] ?>"><?= htmlspecialchars($v['immatriculation']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="description" placeholder="Description" class="form-control mb-2" required>
                    <input type="date" name="date_maintenance" class="form-control mb-2" required>
                    <input type="number" name="cout" placeholder="Coût (€)" class="form-control mb-2" step="0.01" required>
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
