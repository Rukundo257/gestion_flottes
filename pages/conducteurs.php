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

// Récupérer tous les conducteurs
$drivers = [];
$error = '';
try {
    $stmt = $pdo->query("SELECT * FROM conducteur ORDER BY id_conducteur DESC");
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des conducteurs : " . $e->getMessage();
}

// Gestion POST (ajout / suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);
        $telephone = trim($_POST['telephone']);
        $email = trim($_POST['email']);
        $statut = $_POST['statut'];

        if ($nom && $prenom && $telephone) {
            try {
                $stmt = $pdo->prepare("INSERT INTO conducteur (nom, prenom, telephone, email, statut) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $telephone, $email, $statut]);
                header("Location: conducteurs.php");
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        } else {
            $error = "Tous les champs obligatoires doivent être remplis.";
        }
    } elseif (isset($_POST['delete'], $_POST['id_conducteur'])) {
        $id = (int) $_POST['id_conducteur'];
        try {
            $stmt = $pdo->prepare("DELETE FROM conducteur WHERE id_conducteur = ?");
            $stmt->execute([$id]);
            header("Location: conducteurs.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la suppression : " . $e->getMessage();
        }
    }
}

// Inclure sidebar et topbar
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/topbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conducteurs - Gestion Flottes</title>
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
        .badge-status { padding: 0.35em 0.65em; border-radius: 12px; font-size: 0.85rem; }
        .table th, .table td { vertical-align: middle; }
        @keyframes fadeInUp { from { opacity:0; transform: translateY(20px);} to {opacity:1; transform:translateY(0);} }
    </style>
</head>
<body>
<div class="main-wrapper d-flex">
    <?php renderSidebar('conducteurs'); ?>

    <div class="main-content-wrapper flex-grow-1">
        <?php renderTopBar(); ?>

        <div class="main-content">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-container">
                <h2 class="mb-4">Gestion des Conducteurs</h2>
                <button class="btn btn-custom mb-3" data-bs-toggle="modal" data-bs-target="#addDriverModal">
                    <i class="fas fa-plus me-2"></i> Ajouter un conducteur
                </button>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!$drivers): ?>
                            <tr><td colspan="7" class="text-center text-muted">Aucun conducteur enregistré.</td></tr>
                        <?php else: ?>
                            <?php foreach ($drivers as $d): ?>
                                <tr>
                                    <td><?= $d['id_conducteur'] ?></td>
                                    <td><?= htmlspecialchars($d['nom']) ?></td>
                                    <td><?= htmlspecialchars($d['prenom']) ?></td>
                                    <td><?= htmlspecialchars($d['telephone']) ?></td>
                                    <td><?= htmlspecialchars($d['email']) ?></td>
                                    <td>
                                        <span class="badge-status bg-<?= $d['statut']==='actif'?'success':'secondary' ?>">
                                            <?= htmlspecialchars(ucfirst($d['statut'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                            <input type="hidden" name="id_conducteur" value="<?= $d['id_conducteur'] ?>">
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
<div class="modal fade" id="addDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un conducteur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="nom" placeholder="Nom" class="form-control mb-2" required>
                    <input type="text" name="prenom" placeholder="Prénom" class="form-control mb-2" required>
                    <input type="text" name="telephone" placeholder="Téléphone" class="form-control mb-2" required>
                    <input type="email" name="email" placeholder="Email" class="form-control mb-2">
                    <select name="statut" class="form-select mb-2" required>
                        <option value="actif" selected>Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
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
