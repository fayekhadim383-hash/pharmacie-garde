<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/header-admin.php';

// Fonction spéciale pour les liens dans l'admin
/*function admin_url($path = '') {
    return 'admin/' . ltrim($path, '/');
}*/
?>


<h2>Tableau de bord Administrateur</h2>
<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Pharmacies</h5>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM pharmacies");
                $count = $stmt->fetchColumn();
                ?>
                <p class="card-text display-4"><?= $count ?></p>
                <a href="gestion_pharmacies.php" class="text-white">Gérer les pharmacies</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Zones</h5>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM zones");
                $count = $stmt->fetchColumn();
                ?>
                <p class="card-text display-4"><?= $count ?></p>
                <a href="gestion_zones.php" class="text-white">Gérer les zones</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Garde</h5>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM gardes WHERE date_fin >= CURDATE()");
                $count = $stmt->fetchColumn();
                ?>
                <p class="card-text display-4"><?= $count ?></p>
                <a href="gestion_garde.php" class="text-white">Gérer les gardes</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>