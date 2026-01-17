<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';
require_once 'includes/functions.php';

// Récupérer les pharmacies de garde actuelles
$stmt = $pdo->query("
    SELECT p.*, g.date_debut, g.date_fin, z.nom as zone_nom 
    FROM gardes g
    JOIN pharmacies p ON g.pharmacie_id = p.id
    LEFT JOIN zones z ON p.zone_id = z.id
    WHERE g.date_fin >= CURDATE()
    ORDER BY g.date_debut, p.nom
");
$pharmacies_garde = $stmt->fetchAll();

//require_once '/includes/header.php';
?>

<h2>Pharmacies de Garde</h2>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th>Zone</th>
                <th>Date de garde</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pharmacies_garde)): ?>
                <tr>
                    <td colspan="5" class="text-center">Aucune pharmacie de garde actuellement</td>
                </tr>
            <?php else: ?>
                <?php foreach ($pharmacies_garde as $pharmacie): ?>
                    <tr>
                        <td><?= htmlspecialchars($pharmacie['nom']) ?></td>
                        <td><?= htmlspecialchars($pharmacie['adresse']) ?></td>
                        <td><?= htmlspecialchars($pharmacie['telephone']) ?></td>
                        <td><?= htmlspecialchars($pharmacie['zone_nom'] ?? 'Non spécifiée') ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($pharmacie['date_debut'])) ?>
                            <?= $pharmacie['date_debut'] != $pharmacie['date_fin'] ? ' au ' . date('d/m/Y', strtotime($pharmacie['date_fin'])) : '' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>