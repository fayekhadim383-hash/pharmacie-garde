<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
requireAdmin();

// Traitement de la suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM gardes WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Garde supprimée avec succès";
        header("Location: gestion_garde.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'pharmacie_id' => (int)$_POST['pharmacie_id'],
        'date_debut' => $_POST['date_debut'],
        'date_fin' => $_POST['date_fin']
    ];

    // Validation des dates
    if (strtotime($data['date_fin']) < strtotime($data['date_debut'])) {
        $_SESSION['error'] = "La date de fin doit être postérieure à la date de début";
        header("Location: gestion_garde.php");
        exit();
    }

    try {
        // Vérifier les conflits de dates
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM gardes 
            WHERE pharmacie_id = :pharmacie_id
            AND (
                (date_debut BETWEEN :date_debut AND :date_fin)
                OR (date_fin BETWEEN :date_debut AND :date_fin)
                OR (:date_debut BETWEEN date_debut AND date_fin)
                OR (:date_fin BETWEEN date_debut AND date_fin)
            )
            " . (isset($_POST['id']) ? "AND id != :id" : "")
        );

        $params = [
            ':pharmacie_id' => $data['pharmacie_id'],
            ':date_debut' => $data['date_debut'],
            ':date_fin' => $data['date_fin']
        ];

        if (isset($_POST['id'])) {
            $params[':id'] = (int)$_POST['id'];
        }

        $stmt->execute($params);
        $conflicts = $stmt->fetchColumn();

        if ($conflicts > 0) {
            $_SESSION['error'] = "Conflit de dates avec une autre garde pour cette pharmacie";
            header("Location: gestion_garde.php");
            exit();
        }

        if (isset($_POST['id'])) {
            // Mise à jour
            $data['id'] = (int)$_POST['id'];
            $stmt = $pdo->prepare("
                UPDATE gardes 
                SET pharmacie_id = :pharmacie_id, 
                    date_debut = :date_debut, 
                    date_fin = :date_fin 
                WHERE id = :id
            ");
            $message = "Garde mise à jour avec succès";
        } else {
            // Insertion
            $stmt = $pdo->prepare("
                INSERT INTO gardes (pharmacie_id, date_debut, date_fin) 
                VALUES (:pharmacie_id, :date_debut, :date_fin)
            ");
            $message = "Garde ajoutée avec succès";
        }

        $stmt->execute($data);
        $_SESSION['success'] = $message;
        header("Location: gestion_garde.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
        header("Location: gestion_garde.php");
        exit();
    }
}

// Récupérer toutes les gardes avec les infos des pharmacies
$stmt = $pdo->query("
    SELECT g.*, p.nom as pharmacie_nom, z.nom as zone_nom 
    FROM gardes g
    JOIN pharmacies p ON g.pharmacie_id = p.id
    LEFT JOIN zones z ON p.zone_id = z.id
    ORDER BY g.date_debut DESC
");
$gardes = $stmt->fetchAll();

// Récupérer toutes les pharmacies pour le formulaire
$pharmacies = $pdo->query("SELECT id, nom FROM pharmacies ORDER BY nom")->fetchAll();

// Récupérer la garde à éditer si ID présent
$gardeToEdit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM gardes WHERE id = ?");
    $stmt->execute([$id]);
    $gardeToEdit = $stmt->fetch();
}

require_once __DIR__ . '/header-admin.php';
?>

<div class="container-fluid">
    <h2 class="my-4">Gestion des Pharmacies de Garde</h2>

    <?= flash('success') ?>
    <?= flash('error') ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <?= $gardeToEdit ? 'Modifier une garde' : 'Ajouter une garde' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="gardeForm">
                        <?php if ($gardeToEdit): ?>
                            <input type="hidden" name="id" value="<?= $gardeToEdit['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="pharmacie_id" class="form-label">Pharmacie</label>
                            <select class="form-select" id="pharmacie_id" name="pharmacie_id" required>
                                <option value="">-- Sélectionnez une pharmacie --</option>
                                <?php foreach ($pharmacies as $pharmacie): ?>
                                    <option value="<?= $pharmacie['id'] ?>"
                                        <?= $gardeToEdit && $gardeToEdit['pharmacie_id'] == $pharmacie['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pharmacie['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                   value="<?= $gardeToEdit ? $gardeToEdit['date_debut'] : '' ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                   value="<?= $gardeToEdit ? $gardeToEdit['date_fin'] : '' ?>" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?= $gardeToEdit ? 'Mettre à jour' : 'Ajouter' ?>
                            </button>
                            <?php if ($gardeToEdit): ?>
                                <a href="gestion_garde.php" class="btn btn-secondary">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">Liste des Gardes Programméees</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Pharmacie</th>
                                    <th>Zone</th>
                                    <th>Date Début</th>
                                    <th>Date Fin</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gardes as $garde): ?>
                                    <tr class="<?= strtotime($garde['date_fin']) >= time() ? 'table-success' : '' ?>">
                                        <td><?= htmlspecialchars($garde['pharmacie_nom']) ?></td>
                                        <td><?= htmlspecialchars($garde['zone_nom'] ?? 'N/A') ?></td>
                                        <td><?= dateToFrench($garde['date_debut']) ?></td>
                                        <td><?= dateToFrench($garde['date_fin']) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="gestion_garde.php?edit=<?= $garde['id'] ?>" 
                                                   class="btn btn-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="gestion_garde.php?delete=<?= $garde['id'] ?>" 
                                                   class="btn btn-danger" title="Supprimer"
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette garde?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">Calendrier des Gardes</h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inclure FullCalendar CSS et JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: [
            <?php foreach ($gardes as $garde): ?>
            {
                title: '<?= addslashes($garde['pharmacie_nom']) ?>',
                start: '<?= $garde['date_debut'] ?>',
                end: '<?= date('Y-m-d', strtotime($garde['date_fin'] . ' +1 day')) ?>',
                backgroundColor: '<?= strtotime($garde['date_fin']) >= time() ? '#28a745' : '#6c757d' ?>',
                borderColor: '<?= strtotime($garde['date_fin']) >= time() ? '#218838' : '#5a6268' ?>',
                url: 'gestion_garde.php?edit=<?= $garde['id'] ?>'
            },
            <?php endforeach; ?>
        ],
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            window.location.href = info.event.url;
        }
    });
    calendar.render();
});
</script>

<?php require_once '../includes/footer.php'; ?>