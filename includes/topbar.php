<?php
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
