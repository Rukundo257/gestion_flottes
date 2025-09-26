<?php
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
renderSidebar(); // affichage direct
?>
