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

// Récupérer toutes les entrées carburant
$carburants = [];
$error = '';
try {
    $stmt = $pdo->query("
        SELECT c.id_carburant, v.immatriculation, c.date_plein, c.quantite, c.cout_total
        FROM carburant c
        JOIN vehicule v ON c.id_vehicule = v.id_vehicule
        ORDER BY c.id_carburant DESC
    ");
    $carburants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération du carburant : " . $e->getMessage();
}

// Gestion POST (ajout / suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $id_vehicule = (int) $_POST['id_vehicule'];
        $date_plein = $_POST['date_plein'];
        $quantite = (float) $_POST['quantite'];
        $cout_total = (float) $_POST['cout_total'];

        if ($id_vehicule && $date_plein && $quantite >= 0 && $cout_total >= 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO carburant (id_vehicule, date_plein, quantite, cout_total) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id_vehicule, $date_plein, $quantite, $cout_total]);
                header("Location: carburant.php");
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        } else {
            $error = "Tous les champs sont obligatoires.";
        }
    } elseif (isset($_POST['delete'], $_POST['id_carburant'])) {
        $id = (int) $_POST['id_carburant'];
        try {
            $stmt = $pdo->prepare("DELETE FROM carburant WHERE id_carburant = ?");
            $stmt->execute([$id]);
            header("Location: carburant.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

// Inclure sidebar et topbar
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';

// Récupérer véhicules pour le formulaire
$vehicules = $pdo->query("SELECT id_vehicule, immatriculation FROM vehicule ORDER BY immatriculation")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carburant - Gestion Flottes</title>
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
    <?php renderSidebar('carburant'); ?>
    <div class="main-content-wrapper flex-grow-1">
        <?php renderTopBar(); ?>
        <div class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-container">
                <h2 class="mb-4">Gestion du Carburant</h2>
                <button class="btn btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addCarburantModal">
                    <i class="fas fa-plus me-2"></i> Ajouter une entrée carburant
                </button>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Véhicule</th>
                            <th>Date</th>
                            <th>Quantité (L)</th>
                            <th>Coût (€)</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$carburants): ?>
                            <tr><td colspan="6" class="text-center text-muted">Aucune entrée carburant enregistrée.</td></tr>
                        <?php else: ?>
                            <?php foreach ($carburants as $c): ?>
                                <tr>
                                    <td><?= $c['id_carburant'] ?></td>
                                    <td><?= htmlspecialchars($c['immatriculation']) ?></td>
                                    <td><?= htmlspecialchars($c['date_plein']) ?></td>
                                    <td><?= number_format($c['quantite'], 2, ',', ' ') ?></td>
                                    <td><?= number_format($c['cout_total'], 2, ',', ' ') ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                            <input type="hidden" name="id_carburant" value="<?= $c['id_carburant'] ?>">
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
<div class="modal fade" id="addCarburantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une entrée carburant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select name="id_vehicule" class="form-select mb-2" required>
                        <option value="">Sélectionner un véhicule</option>
                        <?php foreach ($vehicules as $v): ?>
                            <option value="<?= $v['id_vehicule'] ?>"><?= htmlspecialchars($v['immatriculation']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="date" name="date_plein" class="form-control mb-2" required>
                    <input type="number" name="quantite" placeholder="Quantité (L)" class="form-control mb-2" step="0.01" required>
                    <input type="number" name="cout_total" placeholder="Coût total (€)" class="form-control mb-2" step="0.01" required>
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
