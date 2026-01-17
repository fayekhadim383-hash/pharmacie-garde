<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

// Supprimer une pharmacie
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM pharmacies WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['message'] = "Pharmacie supprimée avec succès";
        header("Location: gestion_pharmacies.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Récupérer toutes les pharmacies avec leurs zones
$stmt = $pdo->query("
    SELECT p.*, z.nom as zone_nom 
    FROM pharmacies p 
    LEFT JOIN zones z ON p.zone_id = z.id
    ORDER BY p.nom
");
$pharmacies = $stmt->fetchAll();

require_once __DIR__ . '/header-admin.php';
?>

<h2>Gestion des Pharmacies</h2>
<a href="ajouter_pharmacie.php" class="btn btn-success mb-3">Ajouter une pharmacie</a>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>ID</th>
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
            <td><?= $pharmacie['id'] ?></td>
            <td><?= htmlspecialchars($pharmacie['nom']) ?></td>
            <td><?= htmlspecialchars($pharmacie['adresse']) ?></td>
            <td><?= htmlspecialchars($pharmacie['telephone']) ?></td>
            <td><?= $pharmacie['zone_nom'] ?? 'Non spécifiée' ?></td>
            <td>
                <a href="ajouter_pharmacie.php?id=<?= $pharmacie['id'] ?>" class="btn btn-sm btn-primary">Modifier</a>
                <a href="gestion_pharmacies.php?delete=<?= $pharmacie['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr?')">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>