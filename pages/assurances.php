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

// Récupérer toutes les assurances
$assurances = [];
$error = '';
try {
    $stmt = $pdo->query("
        SELECT s.id_assurance, v.immatriculation, s.compagnie, s.numero_police, s.date_debut, s.date_fin, s.cout
        FROM assurance s
        JOIN vehicule v ON s.id_vehicule = v.id_vehicule
        ORDER BY s.id_assurance DESC
    ");
    $assurances = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des assurances : " . $e->getMessage();
}

// Gestion POST (ajout / suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $id_vehicule = (int) $_POST['id_vehicule'];
        $compagnie = trim($_POST['compagnie']);
        $numero_police = trim($_POST['numero_police']);
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $cout = (float) $_POST['cout'];

        if ($id_vehicule && $compagnie && $numero_police && $date_debut && $date_fin && $cout >= 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO assurance (id_vehicule, compagnie, numero_police, date_debut, date_fin, cout) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id_vehicule, $compagnie, $numero_police, $date_debut, $date_fin, $cout]);
                header("Location: assurances.php");
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        } else {
            $error = "Tous les champs sont obligatoires.";
        }
    } elseif (isset($_POST['delete'], $_POST['id_assurance'])) {
        $id = (int) $_POST['id_assurance'];
        try {
            $stmt = $pdo->prepare("DELETE FROM assurance WHERE id_assurance = ?");
            $stmt->execute([$id]);
            header("Location: assurances.php");
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
    <title>Assurances - Gestion Flottes</title>
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
    <?php renderSidebar('assurances'); ?>

    <div class="main-content-wrapper flex-grow-1">
        <?php renderTopBar(); ?>

        <div class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-container">
                <h2 class="mb-4">Gestion des Assurances</h2>
                <button class="btn btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addAssuranceModal">
                    <i class="fas fa-plus me-2"></i> Ajouter une assurance
                </button>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Véhicule</th>
                            <th>Compagnie</th>
                            <th>Numéro police</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Coût (€)</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$assurances): ?>
                            <tr><td colspan="8" class="text-center text-muted">Aucune assurance enregistrée.</td></tr>
                        <?php else: ?>
                            <?php foreach ($assurances as $s): ?>
                                <tr>
                                    <td><?= $s['id_assurance'] ?></td>
                                    <td><?= htmlspecialchars($s['immatriculation']) ?></td>
                                    <td><?= htmlspecialchars($s['compagnie']) ?></td>
                                    <td><?= htmlspecialchars($s['numero_police']) ?></td>
                                    <td><?= htmlspecialchars($s['date_debut']) ?></td>
                                    <td><?= htmlspecialchars($s['date_fin']) ?></td>
                                    <td><?= number_format($s['cout'], 2, ',', ' ') ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                            <input type="hidden" name="id_assurance" value="<?= $s['id_assurance'] ?>">
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
<div class="modal fade" id="addAssuranceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une assurance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select name="id_vehicule" class="form-select mb-2" required>
                        <option value="">Sélectionner un véhicule</option>
                        <?php foreach ($vehicules as $v): ?>
                            <option value="<?= $v['id_vehicule'] ?>"><?= htmlspecialchars($v['immatriculation']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="compagnie" placeholder="Compagnie" class="form-control mb-2" required>
                    <input type="text" name="numero_police" placeholder="Numéro police" class="form-control mb-2" required>
                    <input type="date" name="date_debut" class="form-control mb-2" required>
                    <input type="date" name="date_fin" class="form-control mb-2" required>
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
