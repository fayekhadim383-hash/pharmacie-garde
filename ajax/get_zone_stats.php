<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
requireAdmin();

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT z.nom as zone, COUNT(p.id) as count
        FROM zones z
        LEFT JOIN pharmacies p ON z.id = p.zone_id
        GROUP BY z.id
        ORDER BY count DESC
    ");
    $results = $stmt->fetchAll();

    $labels = [];
    $values = [];

    foreach ($results as $row) {
        $labels[] = $row['zone'];
        $values[] = (int)$row['count'];
    }

    echo json_encode([
        'labels' => $labels,
        'values' => $values
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'labels' => [],
        'values' => []
    ]);
}