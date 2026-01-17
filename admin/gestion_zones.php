<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$error = null;
$success = null;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    
    try {
        // Validation
        if (empty($nom)) {
            throw new Exception("Le nom de la zone est requis");
        }

        // Vérification si c'est une création ou une mise à jour
        if (!empty($_POST['id'])) {
            // Mise à jour existante
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("UPDATE zones SET nom = ? WHERE id = ?");
            $stmt->execute([$nom, $id]);
            $success = "Zone mise à jour avec succès";
        } else {
            // Nouvelle création
            $stmt = $pdo->prepare("INSERT INTO zones (nom) VALUES (?)");
            $stmt->execute([$nom]);
            $success = "Nouvelle zone créée avec succès";
        }
        
        // Redirection pour éviter le rechargement du formulaire
        header("Location: gestion_zones.php");
        exit;
        
    } catch (PDOException $e) {
        $error = "Erreur de base de données: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Traitement de la suppression
if (isset($_GET['delete'])) {
    try {
        $id = intval($_GET['delete']);
        $stmt = $pdo->prepare("DELETE FROM zones WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Zone supprimée avec succès";
        header("Location: gestion_zones.php");
        exit;
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Préparation de l'édition
$zone_to_edit = null;
if (isset($_GET['edit'])) {
    try {
        $id = intval($_GET['edit']);
        $stmt = $pdo->prepare("SELECT * FROM zones WHERE id = ?");
        $stmt->execute([$id]);
        $zone_to_edit = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Erreur lors de la récupération de la zone: " . $e->getMessage();
    }
}

// Récupération des zones existantes
try {
    $stmt = $pdo->query("SELECT * FROM zones ORDER BY nom");
    $zones = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des zones: " . $e->getMessage();
}
?>

<?php require_once __DIR__ . '/header-admin.php'; ?>

<div class="container mt-4">
    <h2>Gestion des Zones</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4><?= isset($zone_to_edit) ? 'Modifier' : 'Ajouter' ?> une Zone</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if (isset($zone_to_edit)): ?>
                            <input type="hidden" name="id" value="<?= $zone_to_edit['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la zone</label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?= isset($zone_to_edit) ? htmlspecialchars($zone_to_edit['nom']) : '' ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?= isset($zone_to_edit) ? 'Mettre à jour' : 'Ajouter' ?>
                        </button>
                        
                        <?php if (isset($zone_to_edit)): ?>
                            <a href="gestion_zones.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Liste des Zones</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($zones)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($zones as $zone): ?>
                                        <tr>
                                            <td><?= $zone['id'] ?></td>
                                            <td><?= htmlspecialchars($zone['nom']) ?></td>
                                            <td>
                                                <a href="?edit=<?= $zone['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                                                <a href="?delete=<?= $zone['id'] ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette zone?')">Supprimer</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucune zone enregistrée</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>