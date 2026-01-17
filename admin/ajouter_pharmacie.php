<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$pharmacie = ['id' => null, 'nom' => '', 'adresse' => '', 'telephone' => '', 'zone_id' => null, 'latitude' => '', 'longitude' => ''];
$isEdit = false;

// Récupérer les zones pour le select
$zones = $pdo->query("SELECT * FROM zones ORDER BY nom")->fetchAll();

// Si on édite une pharmacie
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM pharmacies WHERE id = ?");
    $stmt->execute([$id]);
    $pharmacie = $stmt->fetch();
    $isEdit = true;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nom' => sanitize($_POST['nom']),
        'adresse' => sanitize($_POST['adresse']),
        'telephone' => sanitize($_POST['telephone']),
        'zone_id' => !empty($_POST['zone_id']) ? (int)$_POST['zone_id'] : null,
        'latitude' => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
        'longitude' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null
    ];

    try {
        if ($isEdit) {
            $data['id'] = (int)$_POST['id'];
            $stmt = $pdo->prepare("
                UPDATE pharmacies 
                SET nom = :nom, adresse = :adresse, telephone = :telephone, 
                    zone_id = :zone_id, latitude = :latitude, longitude = :longitude 
                WHERE id = :id
            ");
            $message = "Pharmacie mise à jour avec succès";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO pharmacies (nom, adresse, telephone, zone_id, latitude, longitude) 
                VALUES (:nom, :adresse, :telephone, :zone_id, :latitude, :longitude)
            ");
            $message = "Pharmacie ajoutée avec succès";
        }

        $stmt->execute($data);
        $_SESSION['message'] = $message;
        header("Location: gestion_pharmacies.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
}

require_once __DIR__ . '/header-admin.php';

?>

<h2><?= $isEdit ? 'Modifier' : 'Ajouter' ?> une pharmacie</h2>

<form method="POST">
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= $pharmacie['id'] ?>">
    <?php endif; ?>
    
    <div class="mb-3">
        <label for="nom" class="form-label">Nom</label>
        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($pharmacie['nom']) ?>" required>
    </div>
    
    <div class="mb-3">
        <label for="adresse" class="form-label">Adresse</label>
        <textarea class="form-control" id="adresse" name="adresse" rows="3" required><?= htmlspecialchars($pharmacie['adresse']) ?></textarea>
    </div>
    
    <div class="mb-3">
        <label for="telephone" class="form-label">Téléphone</label>
        <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($pharmacie['telephone']) ?>" required>
    </div>
    
    <div class="mb-3">
        <label for="zone_id" class="form-label">Zone</label>
        <select class="form-select" id="zone_id" name="zone_id">
            <option value="">-- Sélectionnez une zone --</option>
            <?php foreach ($zones as $zone): ?>
                <option value="<?= $zone['id'] ?>" <?= $pharmacie['zone_id'] == $zone['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($zone['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="number" step="0.000001" class="form-control" id="latitude" name="latitude" value="<?= $pharmacie['latitude'] ?>">
        </div>
        <div class="col-md-6">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="number" step="0.000001" class="form-control" id="longitude" name="longitude" value="<?= $pharmacie['longitude'] ?>">
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Mettre à jour' : 'Ajouter' ?></button>
    <a href="gestion_pharmacies.php" class="btn btn-secondary">Annuler</a>
</form>

<?php require_once '../includes/footer.php'; ?>