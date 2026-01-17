<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Récupérer les pharmacies de garde actuelles
$current_garde = $pdo->query("
    SELECT p.*, z.nom as zone_nom 
    FROM gardes g
    JOIN pharmacies p ON g.pharmacie_id = p.id
    LEFT JOIN zones z ON p.zone_id = z.id
    WHERE g.date_debut <= CURDATE() AND g.date_fin >= CURDATE()
    ORDER BY z.nom, p.nom
")->fetchAll();

// Récupérer les prochaines gardes (7 prochains jours)
$upcoming_garde = $pdo->query("
    SELECT p.*, g.date_debut, g.date_fin, z.nom as zone_nom 
    FROM gardes g
    JOIN pharmacies p ON g.pharmacie_id = p.id
    LEFT JOIN zones z ON p.zone_id = z.id
    WHERE g.date_debut BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY g.date_debut, z.nom, p.nom
")->fetchAll();

//require_once '/includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Pharmacies de garde aujourd'hui</h3>
            </div>
            <div class="card-body">
                <?php if (empty($current_garde)): ?>
                    <div class="alert alert-info">Aucune pharmacie de garde aujourd'hui</div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($current_garde as $pharmacie): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($pharmacie['nom']) ?></h5>
                                        <p class="card-text">
                                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($pharmacie['adresse']) ?><br>
                                            <i class="fas fa-phone"></i> <?= htmlspecialchars($pharmacie['telephone']) ?><br>
                                            <span class="badge bg-info"><?= htmlspecialchars($pharmacie['zone_nom'] ?? 'Non spécifiée') ?></span>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-light">
                                        <small class="text-muted">Garde aujourd'hui</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Prochaines gardes</h3>
            </div>
            <div class="card-body">
                <?php if (empty($upcoming_garde)): ?>
                    <div class="alert alert-info">Aucune garde programmée dans les 7 prochains jours</div>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($upcoming_garde as $pharmacie): ?>
                            <li class="list-group-item">
                                <strong><?= htmlspecialchars($pharmacie['nom']) ?></strong><br>
                                <small>
                                    <?= date('d/m/Y', strtotime($pharmacie['date_debut'])) ?>
                                    <?= $pharmacie['date_debut'] != $pharmacie['date_fin'] ? ' au ' . date('d/m/Y', strtotime($pharmacie['date_fin'])) : '' ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Recherche rapide</h3>
            </div>
            <div class="card-body">
                <form action="liste_pharmacies.php" method="GET">
                    <div class="mb-3">
                        <label for="search" class="form-label">Nom ou zone</label>
                        <input type="text" class="form-control" id="search" name="q" placeholder="Rechercher...">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Rechercher</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($current_garde)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title">Carte des pharmacies de garde</h3>
            </div>
            <div class="card-body">
                <div id="map" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialisation de la carte avec Leaflet.js
document.addEventListener('DOMContentLoaded', function() {
    // Créer la carte centrée sur une position par défaut
    const map = L.map('map').setView([14.45, -17.20], 20); // Coordonnées approximatives de Dakar
    
    // Ajouter la couche OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Ajouter des marqueurs pour chaque pharmacie
    <?php foreach ($current_garde as $pharmacie): ?>
        <?php if (!empty($pharmacie['latitude']) && !empty($pharmacie['longitude'])): ?>
            L.marker([<?= $pharmacie['latitude'] ?>, <?= $pharmacie['longitude'] ?>])
                .addTo(map)
                .bindPopup("<b><?= addslashes($pharmacie['nom']) ?></b><br><?= addslashes($pharmacie['adresse']) ?>");
        <?php endif; ?>
    <?php endforeach; ?>
});
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>