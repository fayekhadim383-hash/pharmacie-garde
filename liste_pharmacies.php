<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once 'includes/functions.php';

// Récupérer le paramètre de recherche
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$zone_filter = isset($_GET['zone']) ? (int)$_GET['zone'] : 0;

// Construction de la requête avec PDO pour éviter les injections SQL
$sql = "SELECT p.*, z.nom as zone_nom FROM pharmacies p LEFT JOIN zones z ON p.zone_id = z.id";
$conditions = [];
$params = [];

// Ajout des conditions de recherche
if (!empty($search)) {
    $conditions[] = "(p.nom LIKE :search OR p.adresse LIKE :search OR p.telephone LIKE :search OR z.nom LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($zone_filter > 0) {
    $conditions[] = "p.zone_id = :zone_id";
    $params[':zone_id'] = $zone_filter;
}

// Combinaison des conditions
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY p.nom ASC";

// Préparation et exécution de la requête
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$pharmacies = $stmt->fetchAll();

// Récupérer toutes les zones pour le filtre
$zones = $pdo->query("SELECT * FROM zones ORDER BY nom ASC")->fetchAll();

//require_once '/includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2>Liste des pharmacies</h2>
        
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="q" class="form-control" placeholder="Rechercher par nom, adresse, téléphone..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="zone" class="form-select">
                            <option value="0">Toutes les zones</option>
                            <?php foreach ($zones as $zone): ?>
                                <option value="<?= $zone['id'] ?>" <?= $zone_filter == $zone['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($zone['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?php if (empty($pharmacies)): ?>
            <div class="alert alert-info">Aucune pharmacie trouvée</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nom</th>
                            <th>Adresse</th>
                            <th>Téléphone</th>
                            <th>Zone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pharmacies as $pharmacie): ?>
                        <tr>
                            <td><?= htmlspecialchars($pharmacie['nom']) ?></td>
                            <td><?= htmlspecialchars($pharmacie['adresse']) ?></td>
                            <td><?= htmlspecialchars($pharmacie['telephone']) ?></td>
                            <td>
                                <?php if ($pharmacie['zone_nom']): ?>
                                    <span class="badge bg-info"><?= htmlspecialchars($pharmacie['zone_nom']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Non spécifiée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="pharmacie_details.php?id=<?= $pharmacie['id'] ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                                <?php if (isAdmin()): ?>
                                    <a href="admin/ajouter_pharmacie.php?id=<?= $pharmacie['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>