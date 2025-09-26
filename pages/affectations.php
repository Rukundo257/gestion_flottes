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

// Récupérer toutes les affectations
$affectations = [];
$error = '';
try {
    $stmt = $pdo->query("
        SELECT a.id_affectation, v.immatriculation, c.nom, c.prenom, a.date_debut, a.date_fin, a.role
        FROM affectation a
        JOIN vehicule v ON a.id_vehicule = v.id_vehicule
        JOIN conducteur c ON a.id_conducteur = c.id_conducteur
        ORDER BY a.id_affectation DESC
    ");
    $affectations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des affectations : " . $e->getMessage();
}

// Gestion POST (ajout / suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $id_vehicule = (int) $_POST['id_vehicule'];
        $id_conducteur = (int) $_POST['id_conducteur'];
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'] ?: null;
        $role = trim($_POST['role']);

        if ($id_vehicule && $id_conducteur && $date_debut && $role) {
            try {
                $stmt = $pdo->prepare("INSERT INTO affectation (id_vehicule, id_conducteur, date_debut, date_fin, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id_vehicule, $id_conducteur, $date_debut, $date_fin, $role]);
                header("Location: affectations.php");
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        } else {
            $error = "Tous les champs obligatoires doivent être remplis.";
        }
    } elseif (isset($_POST['delete'], $_POST['id_affectation'])) {
        $id = (int) $_POST['id_affectation'];
        try {
            $stmt = $pdo->prepare("DELETE FROM affectation WHERE id_affectation = ?");
            $stmt->execute([$id]);
            header("Location: affectations.php");
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
    <title>Affectations - Gestion Flottes</title>
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
    <?php renderSidebar('affectations'); ?>

    <div class="main-content-wrapper flex-grow-1">
        <?php renderTopBar(); ?>

        <div class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-container">
                <h2 class="mb-4">Gestion des Affectations</h2>
                <button class="btn btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addAffectationModal">
                    <i class="fas fa-plus me-2"></i> Ajouter une affectation
                </button>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Véhicule</th>
                            <th>Conducteur</th>
                            <th>Date début</th>
                            <th>Date fin</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$affectations): ?>
                            <tr><td colspan="7" class="text-center text-muted">Aucune affectation enregistrée.</td></tr>
                        <?php else: ?>
                            <?php foreach ($affectations as $a): ?>
                                <tr>
                                    <td><?= $a['id_affectation'] ?></td>
                                    <td><?= htmlspecialchars($a['immatriculation']) ?></td>
                                    <td><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></td>
                                    <td><?= htmlspecialchars($a['date_debut']) ?></td>
                                    <td><?= htmlspecialchars($a['date_fin'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($a['role']) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                            <input type="hidden" name="id_affectation" value="<?= $a['id_affectation'] ?>">
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
<div class="modal fade" id="addAffectationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter une affectation</h5>
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
                            <option value="<?= $c['id_conducteur'] ?>"><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="date" name="date_debut" class="form-control mb-2" required>
                    <input type="date" name="date_fin" class="form-control mb-2">
                    <input type="text" name="role" placeholder="Rôle" class="form-control mb-2" required>
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
