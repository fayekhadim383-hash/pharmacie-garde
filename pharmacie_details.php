<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Vérifier si l'ID de la pharmacie est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: liste_pharmacies.php');
    exit;
}

$pharmacie_id = intval($_GET['id']);

try {
    // Récupérer les détails de la pharmacie
    $stmt = $pdo->prepare("SELECT p.*, z.nom AS zone_nom 
                          FROM pharmacies p 
                          LEFT JOIN zones z ON p.zone_id = z.id 
                          WHERE p.id = ?");
    $stmt->execute([$pharmacie_id]);
    $pharmacie = $stmt->fetch();

    if (!$pharmacie) {
        throw new Exception("Pharmacie non trouvée");
    }

    // Récupérer les périodes de garde
    $stmt_gardes = $pdo->prepare("SELECT g.* FROM gardes g WHERE g.pharmacie_id = ? ORDER BY g.date_debut");
    $stmt_gardes->execute([$pharmacie_id]);
    $gardes = $stmt_gardes->fetchAll();

} catch (Exception $e) {
    $error = "Erreur lors de la récupération des données: " . $e->getMessage();
}
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <a href="liste_pharmacies.php" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title">Détails de la pharmacie</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4><?= htmlspecialchars($pharmacie['nom']) ?></h4>
                                <p class="text-muted"><?= htmlspecialchars($pharmacie['zone_nom']) ?></p>
                                
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong><i class="fas fa-map-marker-alt"></i> Adresse:</strong> 
                                        <?= htmlspecialchars($pharmacie['adresse']) ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong><i class="fas fa-phone"></i> Téléphone:</strong> 
                                        <?= htmlspecialchars($pharmacie['telephone']) ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong><i class="fas fa-clock"></i> Horaires:</strong> 
                                        <?php echo "24h/24" ?>
                                    </li>
                                    <li class="list-group-item">
                                        <strong><i class="fas fa-info-circle"></i> Informations:</strong> 
                                        <?php echo "Vous pouvez recherchez ses infos dans un navigateur" ?>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title">Périodes de garde</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($gardes)): ?>
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th>Date début</th>
                                                            <th>Date fin</th>
                                                            <th>Statut</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($gardes as $garde): ?>
                                                            <tr>
                                                                <td><?= date('d/m/Y', strtotime($garde['date_debut'])) ?></td>
                                                                <td><?= date('d/m/Y', strtotime($garde['date_fin'])) ?></td>
                                                                <td>
                                                                    <?php 
                                                                    $today = date('Y-m-d');
                                                                    if ($today >= $garde['date_debut'] && $today <= $garde['date_fin']) {
                                                                        echo '<span class="badge bg-success">En cours</span>';
                                                                    } elseif ($today > $garde['date_fin']) {
                                                                        echo '<span class="badge bg-secondary">Terminée</span>';
                                                                    } else {
                                                                        echo '<span class="badge bg-warning text-dark">À venir</span>';
                                                                    }
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">Aucune période de garde enregistrée</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isAdmin()): ?>
                        <div class="card-footer">
                            <a href="admin/modifier_pharmacie.php?id=<?= $pharmacie_id ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="admin/supprimer_pharmacie.php?id=<?= $pharmacie_id ?>" class="btn btn-danger float-end" onclick="return confirm('Confirmer la suppression ?')">
                                <i class="fas fa-trash"></i> Supprimer
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>